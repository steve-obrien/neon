<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsManager;

use neon\daedalus\interfaces\IDdsDataTypeManagement;
use neon\daedalus\interfaces\IDdsClassManagement;
use neon\daedalus\services\ddsManager\DdsCore;
use neon\daedalus\services\ddsManager\models\DdsClass;
use neon\daedalus\services\ddsManager\models\DdsDataType;
use neon\daedalus\services\ddsManager\models\DdsMember;

class DdsDataDefinitionManager extends DdsCore
implements IDdsDataTypeManagement, IDdsClassManagement
{

	/** --------------------------------------------------- **/
	/** ---------- interface IDdsClassManagement ---------- **/
	/** --------------------------------------------------- **/

	/** ---------- Utility Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function isClassTypeUnique($candidate)
	{
		$ct = $this->canonicaliseRef($candidate);
		$class = DdsClass::find()->where(['class_type'=>$ct])->one();
		return ($class == null);
	}

	/**
	 * @inheritdoc
	 */
	public function isMemberRefUnique($classType, $candidate)
	{
		if ($this->isClassTypeUnique($classType))
			throw new \InvalidArgumentException("Class type ".$this->canonicaliseRef($classType)." unknown");
		$mr = $this->canonicaliseRef($candidate);
		$member = DdsMember::find()->where(['class_type'=>$classType, 'member_ref'=>$mr])->one();
		return ($member == null);
	}

	/** ---------- Class Manipulations ---------- **/

	/**
	 * @inheritdoc
	 */
	public function countClasses($module=null, $includeDeleted=false)
	{
		$query = $this->_getClassesQuery($module, $includeDeleted);
		return $query->count();
	}

	/**
	 * @inheritdoc
	 */
	public function listClasses($module, &$total, $includeDeleted=false, $start=0, $length=100, $orderBy='label')
	{
		$query = $this->_getClassesQuery($module, $includeDeleted);
		if ($start == 0)
			$total = (int) $query->count();
		$results = $query->asArray()
			->offset($start)
			->limit($length)
			->orderBy($orderBy)
			->all();
		foreach ($results as &$r)
			$r['count_current'] = $r['count_total'] - $r['count_deleted'];
		return $results;
	}

	/**
	 * @inheritdoc
	 */
	public function getMappableClasses()
	{
		$ddsMemberTbl = DdsMember::tableName();
		$ddsClassTbl = DdsClass::tableName();
		$mappable = DdsClass::find()
			->innerJoin($ddsMemberTbl, "$ddsClassTbl.class_type = $ddsMemberTbl.class_type")
			->select("$ddsClassTbl.class_type, $ddsClassTbl.label")
			->where([
				"$ddsMemberTbl.map_field" => 1,
				"$ddsClassTbl.deleted" => 0,
				"$ddsMemberTbl.deleted" => 0
			])
			->asArray()
			->all();
		$map = [];
		foreach ($mappable as $m)
			$map[$m['class_type']] = $m['label'];
		return $map;
	}

	/**
	 * Get the map field for a class
	 * @param string $class
	 */
	public function getClassMapField($class)
	{
		$member = $this->getMapMemberForClass($class);
		return $member ? $member['member_ref'] : null;
	}

	/**
	 * @inheritdoc
	 */
	public function addClass(&$classType, $module)
	{
		$classType = $this->canonicaliseRef($classType);
		if (DdsClass::findOne(['class_type'=>$classType]) !== null)
			throw new \InvalidArgumentException("The class $classType already exists");
		$c = new DdsClass();
		$c->class_type = $classType;
		$c->module = (empty($module) ? null : $module);
		if (!$c->save())
			throw new \RuntimeException("Couldn't save the class: ".print_r($c->errors,true));
		$upSql = $this->getClassTypeMigrationSql($classType);
		$downSql = "DELETE FROM `dds_class` WHERE `class_type`='$classType';";
		$this->storeMigration($upSql, $downSql);
		$this->createClassTable($classType);
		return $c->class_type;
	}

	/**
	 * @inheritdoc
	 */
	public function getClass($classType, $includeMembers=false)
	{
		if ($this->findClass($classType, $class)) {
			$data = $class->toArray();
			if ($includeMembers)
				$data['members'] = $this->listMembers($class['class_type']);
			return $data;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function editClass($classType, $changes)
	{
		$allowed = array_intersect_key($changes, array_flip(['label', 'description', 'module', 'change_log']));
		if (count($allowed)==0)
			return;
		$this->clearClassCache($classType);
		$downSql = $this->getClassTypeMigrationSql($classType);
		if ($this->findClass($classType, $class)) {
			// find the minimum set of actual changes and only update if there are any
			$updates = array_diff_assoc($allowed, $class->attributes);
			if (count($updates)) {
				$class->attributes = $updates;
				if (!$class->save())
					throw new \RuntimeException("Dds: Couldn't update the class '$classType': ".print_r($class->errors));
				$upSql = $this->getClassTypeMigrationSql($classType);
				$this->storeMigration($upSql, $downSql);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function deleteClass($classType)
	{
		// delete a class if it hasn't already been deleted
		if ($this->findClass($classType, $class) && $class->deleted == 0) {
			$upSql = "UPDATE `dds_class` SET `deleted`=1 WHERE `class_type`='{$class->class_type}';";
			neon()->db->createCommand($upSql)->execute();
			$downSql = "UPDATE `dds_class` SET `deleted`=0 WHERE `class_type`='{$class->class_type}';";
			$this->storeMigration($upSql, $downSql);
			$this->clearClassCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteClass($classType)
	{
		// undelete a class if it is already deleted
		if ($this->findClass($classType, $class) && $class->deleted == 1) {
			$upSql = "UPDATE `dds_class` SET `deleted`=0 WHERE `class_type`='{$class->class_type}';";
			neon()->db->createCommand($upSql)->execute();
			$downSql = "UPDATE `dds_class` SET `deleted`=1 WHERE `class_type`='{$class->class_type}';";
			$this->storeMigration($upSql, $downSql);
			$this->clearClassCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyClass($classType)
	{
		if ($this->findClass($classType, $class)) {
			if ($this->hasObjects($classType))
				throw new \RunTimeException("You need to delete all objects before you can destroy a class");

			// now delete the rows
			$members = DdsMember::find()->where(['class_type'=>$class->class_type])->asArray()->all();
			$errors = [];
			foreach ($members as $member) {
				try {
					$this->destroyMember($member['class_type'], $member['member_ref']);
				} catch (\Exception $e) {
					$errors[] = $e->getMessage();
				}
			}
			if (count($errors))
				throw new \RuntimeException("Couldn't destroy all of the members: ".print_r($errors, true));
			// now delete the class
			// can also return 0 - which is valid - false means there has been an error
			if ($class->delete() === false)
				throw new \RuntimeException("Couldn't destroy the class: ".print_r($class->errors, true));
			// and clear away the table
			$this->dropClassTable($class->class_type);
			$this->clearClassCache($classType);
		}
	}


	/** ---------- Member Manipulations(!!) ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listMembers($classType, $includeDeleted=false, $keyBy='member_ref')
	{
		return $this->listMembersForClass($classType, $includeDeleted, $keyBy);
	}

	/**
	 * @inheritdoc
	 */
	public function addMember($classType, &$memberRef, $dataTypeRef, $label, $description, $additional=null)
	{
		$classType = $this->canonicaliseRef($classType);
		$memberRef = $this->canonicaliseRef($memberRef);
		$class = null;
		if ($this->findClass($classType, $class)) {
			// extract out any additional information
			$choices = ((!empty($additional['choices']) && is_array($additional['choices'])) ? $additional['choices'] : []);
			$linkClass = (!empty($additional['link_class']) ? $additional['link_class'] : null);
			if (DdsMember::findOne(['class_type' => $classType, 'member_ref' => $memberRef])!==null)
				throw new \InvalidArgumentException("The member $memberRef already exists");
			if (in_array($dataTypeRef, ['link_uni', 'link_multi']) && !$linkClass) {
				throw new \InvalidArgumentException("Link_uni, link_multi data types require a link_class to be passed for member '$classType::$memberRef'. You passed in ".print_r($additional, true));
			}
			$member = new DdsMember();
			$member->class_type = $classType;
			$member->member_ref = $memberRef;
			$member->data_type_ref = $dataTypeRef;
			$member->label = $label;
			$member->description = $description;
			$member->choices = ($this->memberAllowedChoices($dataTypeRef) ? $choices : null);
			$member->link_class = ($this->memberAllowedLinks($dataTypeRef) ? $linkClass : null);
			$member->created = $this->now();
			if (!$member->save())
				throw new \RuntimeException("Couldn't save the member: ".print_r($member->errors, true));

			// create the migrations
			$savedMember = $this->getMember($classType, $memberRef);
			$upMember = $this->getTableRowReplaceSql('dds_member', $savedMember);
			$downMember = "DELETE FROM `dds_member` WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			$mId = $this->storeMigration($upMember, $downMember);

			// and create the member column
			if (($error=$this->addClassMemberColumn($classType, $member->member_ref)) !== true) {
				$member->delete();
				$this->removeMigration($mId);
				throw new \RuntimeException("Couldn't create the member column. Error=".$error);
			}
			$this->clearClassMemberCache($classType);
			return $member->member_ref;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getMember($classType, $memberRef, $fields=[])
	{
		// !! keep member as an object so it runs all conversion code
		$query = DdsMember::find();
		if (count($fields)) {
			$selection = array_intersect($fields, [
				'class_type', 'member_ref', 'data_type_ref', 'label', 'description', 'definition', 'choices', 'link_class', 'deleted'
			]);
			// ensure only allowed fields are passed through
			$query = $query->select($selection);
		}
		$member = $query->where(['member_ref' => $memberRef, 'class_type' => $classType])->one();
		return $member->toArray();
	}

	/**
	 * @inheritdoc
	 */
	public function editMember($classType, $memberRef, $changes)
	{
		// check that only allowed updates are made
		$allowed = array_intersect_key($changes, array_flip(['label', 'description', 'definition', 'choices', 'link_class']));
		if (count($allowed)==0)
			return;

		// find the member and remove any illegitamate updates
		if (!$this->findMember($classType, $memberRef, $member))
			throw new \InvalidArgumentException("No member found");
		if (isset($allowed['choices'])) {
			$allowed['choices'] = empty($allowed['choices']) ? [] : $allowed['choices'];
			if (!(is_array($allowed['choices']) && $this->memberAllowedChoices($member->data_type_ref))) {
				unset($allowed['choices']);
			}
		}

		if (isset($allowed['link_class']) && !$this->memberAllowedLinks($member->data_type_ref))
			unset($allowed['link_class']);

		// finally check to see if there are any actual updates and if so save the provided updates
		$updates = $this->getMemberUpdateDifferences($allowed, $member->attributes);
		if (count($updates)) {
			$downSql = $this->getTableRowReplaceSql('dds_member', $member);
			$member->attributes = $updates;
			if (!$member->save())
				throw new \RuntimeException("Couldn't update the member: ".print_r ($member->errors, true));
			$upSql = $this->getTableRowReplaceSql('dds_member', $member);
			$this->storeMigration($upSql, $downSql);
			$this->clearClassMemberCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setMemberAsMapField($classType, $memberRef)
	{
		if (!$this->findMember($classType, $memberRef, $member))
			throw new \InvalidArgumentException("No member found");

		// find out which member had the map field set for migrations
		$oldMember = DdsMember::findOne(['class_type'=>$classType, 'map_field'=>1]);
		if ($oldMember && $oldMember->member_ref == $memberRef)
			return;
		$upSql = "UPDATE `dds_member` SET `map_field`=(IF (`member_ref`='$memberRef', 1, 0)) WHERE `class_type`='$classType';";
		$downSql = ($oldMember ? "UPDATE `dds_member` SET `map_field`=(IF (`member_ref`='{$oldMember->member_ref}', 1, 0)) WHERE `class_type`='$classType';" : 
			"UPDATE `dds_member` SET `map_field`=0 WHERE `class_type`='$classType';");
		neon()->db->createCommand($upSql)->execute();
		$this->storeMigration($upSql, $downSql);
		$this->clearClassMemberCache($classType);
	}


	/**
	 * @inheritdoc
	 */
	public function deleteMember($classType, $memberRef)
	{
		// delete a member if it isn't already deleted
		if ($this->findMember($classType, $memberRef, $member) && $member->deleted==0) {
			$upSql = "UPDATE `dds_member` SET `deleted`=1 WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			$downSql = "UPDATE `dds_member` SET `deleted`=0 WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
			$this->clearClassMemberCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteMember($classType, $memberRef)
	{
		// undelete a member if it is already deleted
		if ($this->findMember($classType, $memberRef, $member) && $member->deleted==1) {
			// create migrations
			$upSql = "UPDATE `dds_member` SET `deleted`=0 WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			$downSql = "UPDATE `dds_member` SET `deleted`=1 WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
			$this->clearClassMemberCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyMember($classType, $memberRef)
	{
		if ($this->findMember($classType, $memberRef, $member)) {
			// try and drop the column first and then the member definition
			$this->dropClassMemberColumn($classType, $memberRef);
			$upSql = "DELETE FROM `dds_member` WHERE `class_type`='$classType' AND `member_ref`='$memberRef';";
			$downSql = $this->getTableRowReplaceSql('dds_member', $member);
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
			$this->clearClassMemberCache($classType);
		}
	}

	/** ------------------------------------------------------ **/
	/** ---------- interface IDdsDataTypeManagement ---------- **/
	/** ------------------------------------------------------ **/

	/** ---------- Utility Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function isDataTypeUnique($candidate)
	{
		$dt = $this->canonicaliseRef($candidate);
		$dataType = DdsDataType::find()->where(['data_type_ref'=>$dt])->one();
		return ($dataType == null);
	}

	/** ---------- Data Type Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listDataTypes($includeDeleted=false)
	{
		$query = DdsDataType::find();
		$select = ['data_type_ref', 'label', 'description', 'definition', 'storage_ref'];
		if ($includeDeleted) {
			$select[] = 'deleted';
		} else {
			$query->where(['deleted'=>0]);
		}
		return $query->select($select)->asArray()->all();
	}

	/**
	 * @inheritdoc
	 */
	public function addDataType(&$dataTypeRef, $label, $description, $definition, $storageRef)
	{
		$dataTypeRef = $this->canonicaliseRef($dataTypeRef);
		if ($this->findDataType($dataTypeRef))
			throw new \InvalidArgumentException("The dataType $dataTypeRef already exists");
		$dt = new DdsDataType();
		$dt->data_type_ref = $dataTypeRef;
		$dt->label = $label;
		$dt->description = $description;
		$dt->definition = $definition;
		$dt->storage_ref = $storageRef;
		if (!$dt->save())
			throw new \RuntimeException("Couldn't create the datatype $dataTypeRef: ".print_r($dt->errors, true));

		// store the migration
		$this->findDataType($dataTypeRef, $dt);
		$upSql = $this->getTableRowReplaceSql('dds_data_type', $dt);
		$downSql = "DELETE FROM `dds_data_type` WHERE `data_type_ref`='$dataTypeRef';";
		$this->storeMigration($upSql, $downSql);

		return $dt->data_type_ref;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataType($dataTypeRef)
	{
		return DdsDataType::find()->where(['data_type_ref'=>$dataTypeRef])->asArray()->one();
	}

	/**
	 * @inheritdoc
	 */
	public function editDataType($dataTypeRef, $changes)
	{
		$allowed = array_intersect_key($changes, array_flip(['label', 'description', 'definition']));
		if (count($allowed) == 0)
			return;
		if ($this->findDataType($dataTypeRef, $dataType)) {
			// make the updates if there is anything to change
			$updates = array_diff($allowed, $dataType->attributes);
			if (count($updates)) {
				$downSql = $this->getTableRowReplaceSql('dds_data_type', $dataType);
				$dataType->attributes = $updates;
				if (!$dataType->save())
					throw new \RuntimeException("Couldn't save the datatype: ".print_r($dataType->errors(),true));
				$upSql = $this->getTableRowReplaceSql('dds_data_type', $dataType);
				$this->storeMigration($upSql, $downSql);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function deleteDataType($dataTypeRef)
	{
		// delete the datatype if not already deleted
		if ($this->findDataType($dataTypeRef, $dataType) && $dataType->deleted == 0) {
			$upSql = "UPDATE `dds_data_type` SET `deleted`=1 WHERE `data_type_ref`='$dataTypeRef';";
			$downSql = "UPDATE `dds_data_type` SET `deleted`=0 WHERE `data_type_ref`='$dataTypeRef';";
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteDataType($dataTypeRef)
	{
		// undelete the datatype if not already undeleleted
		if ($this->findDataType($dataTypeRef, $dataType) && $dataType->deleted = 1) {
			$upSql = "UPDATE `dds_data_type` SET `deleted`=0 WHERE `data_type_ref`='$dataTypeRef';";
			$downSql = "UPDATE `dds_data_type` SET `deleted`=1 WHERE `data_type_ref`='$dataTypeRef';";
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyDataType($dataTypeRef)
	{
		// destroy the datatype if it exists
		if ($this->findDataType($dataTypeRef, $dataType)) {
			$upSql = "DELETE FROM `dds_data_type` WHERE `data_type_ref`='$dataTypeRef';";
			$downSql = $this->getTableRowReplaceSql('dds_data_type', $dataType);
			neon()->db->createCommand($upSql)->execute();
			$this->storeMigration($upSql, $downSql);
		}
	}



	/** ------------------------------------ **/
	/** ---------- Private Methods --------- **/
	/** ------------------------------------ **/


	/**
	 * Convert a dds_class entry into an insert statement for migrations
	 * @param string $classType
	 * @return null|string
	 */
	private function getClassTypeMigrationSql($classType)
	{
		$ddsClass = DdsClass::findOne(['class_type'=>$classType]);
		if ($ddsClass == null)
			return null;
		return $this->getTableRowReplaceSql('dds_class', $ddsClass);
	}

	/**
	 * Check if a particular member type is allowed to store choices.
	 *
	 * @param string $dataTypeRef
	 * @return bool
	 */
	private function memberAllowedChoices($dataTypeRef)
	{
		// if additional datatypes are allowed then update appropriately
		$allowedDataTypes = ['choice', 'choice_multiple'];
		return in_array($dataTypeRef, $allowedDataTypes);
	}

	/**
	 * Check if a particular member type is allowed to store links in the links table
	 *
	 * @param string $dataTypeRef
	 * @return bool
	 */
	private function memberAllowedLinks($dataTypeRef)
	{
		// if additional datatypes can be links then update appropriately
		$allowedDataTypes = [
			'link_multi',
			'link_uni',
			'file_ref_multi'
		];
		return in_array($dataTypeRef, $allowedDataTypes);
	}

	/**
	 * Get the difference between a set of changes and the member fields
	 *
	 * @param array $changes  set of requested changes
	 * @param array $member  current member values
	 */
	protected function getMemberUpdateDifferences($changes, $member)
	{
		$a = $changes;
		$b = $member;
		$diffs = [];
		foreach ($a as $k=>$v) {
			if (array_key_exists($k, $b)) {
				if (is_array($v)) {
					$diff = $this->getMemberUpdateDifferences($v, $b[$k]);
					// we want all of the array if there were any differences
					if (count($diff))
						$diffs[$k] = $v;
				} else {
					if ((string)$v !== (string)$b[$k])
						$diffs[$k] = $v;
				}
			} else {
				$diffs[$k] = $v;
			}
		}
		return $diffs;
	}


	/**
	 * static cache array of class data to reduce database calls
	 * @var array
	 */
	private static $_classCache = [];

	private function _getClassesQuery($module=null, $includeDeleted=false)
	{
		$query = DdsClass::find();
		$select = ['class_type', 'label', 'description', 'module', 'count_total', 'count_deleted', 'change_log'];
		if ($includeDeleted) {
			$select[] = 'deleted';
		} else {
			$query->andWhere(['deleted' => 0]);
		}
		$query->select($select);
		if ($module != null)
			$query->andWhere(['or', ['module' => $module], ['module' => NULL]]);
		return $query;
	}

}
