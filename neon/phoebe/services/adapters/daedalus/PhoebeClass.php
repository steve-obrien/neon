<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\daedalus;

use neon\phoebe\services\adapters\common\PhoebeClassBase;
use neon\phoebe\interfaces\forms\IPhoebeFormClass;


class PhoebeClass extends PhoebeClassBase
implements IPhoebeFormClass
{
	/** ---------------------------------- **/
	/** -------- IPhoebeFormClass -------- **/

	/**
	 * @inheritdoc
	 */
	public function getRequiredDataSources()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function findPhoebeKeysForClassTypesInDefinition($ddsClassType)
	{
		return null;
	}

	/** ---------------------------------- **/
	/** ---------- IPhoebeClass ---------- **/

	/**
	 * @inheritdoc
	 */
	public function getClassFormDefinition(array $fields = [])
	{
		return $this->getDefinitionAsForm(true, $fields);
	}

	public function getClassBuilderDefinition()
	{
		return $this->getDefinitionAsForm(false);
	}

	/**
	 * @inheritdoc
	 */
	public function editClass($changes)
	{
		if (!is_array($changes))
			throw new \RuntimeException('You should pass an array into editClass. You passed in '.print_r($changes,true));

		// make the appropriate changes to dds. Any unknown fields are ignored
		$this->dds()->editClass($this->classType, $changes);

		// extract the members
		$fields = [];
		if (isset($changes['definition']['fields'])) {
			$fields = $changes['definition']['fields'];
		}

		// get hold of the existing fields
		$members = $this->dds()->listMembers($this->classType, true);
		// and now go through each field either editing, adding or deleting

		foreach ($fields as $k => $f) {
			// map some form to Phoebe differences
			$f['link_class'] = (!empty($f['linkClass']) ? $f['linkClass'] : null);  // named differently
			// extract the definition for saving in phoebe
			$definition = $f['definition'];

			$memberRef = $this->canonicaliseRef($f['memberRef']);
			$memberExists = array_key_exists($memberRef, $members);
			$currentData = [];
			if ($memberExists) {
				// get the data before the changes
				$currentData = $this->dds()->getMember($this->classType, $memberRef);
				$this->dds()->editMember($this->classType, $memberRef, $f);
			} else {
				// don't add deleted / destroyed members that don't already exist
				if ($definition['deleted'] != 0) {
					// remove definition from phoebe
					unset($changes['definition']['fields'][$k]);
					continue;
				}
				$this->dds()->addMember(
					$this->classType,
					$memberRef,
					$f['dataType'],
					$f['label'],
					$f['description'],
					[
						'link_class'=>$f['link_class'],
						'choices'=>$f['choices'],
					]
				);
				// get the data after creation
				$currentData = $this->dds()->getMember($this->classType, $memberRef);
			}

			if ($f['mapField'] == 1 && $currentData['map_field'] == 0) {
				$this->dds()->setMemberAsMapField($this->classType, $memberRef);
			}
			if ($definition['deleted'] == 1 && $currentData['deleted'] == 0)
				$this->dds()->deleteMember($this->classType, $memberRef);
			else if ($definition['deleted'] == 0 && $currentData['deleted'] == 1)
				$this->dds()->undeleteMember($this->classType, $memberRef);
			if ($definition['deleted'] == -1) {
				// remove definition from phoebe
				unset($changes['definition']['fields'][$k]);
				$this->dds()->destroyMember($this->classType, $memberRef);
			}
		}

		// and save to phoebe - remove unallowed phoebe values for Daedalus tables
		$changes = array_diff_key($changes, array_flip(['version', 'volatile', 'object_history', 'version_locked']));
		if (count($changes)==0)
			return;
		$hasChanges = parent::editClass($changes);
		return $hasChanges;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteClass()
	{
		if (($result = parent::deleteClass()))
			$this->dds()->deleteClass($this->classType);
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteClass()
	{
		if (($result = parent::undeleteClass()))
			$this->dds()->undeleteClass($this->classType);
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function destroyClass()
	{
		if (($result = parent::destroyClass()))
			$this->dds()->destroyClass($this->classType);
		return $result;
	}


	/** --------------------------------------- **/
	/** -------- Class Override Methods ------- **/

	public function addOverride($label, $selector='', $activeFrom=null, $activeTo=null, $isActive=true)
	{
		throw new \RuntimeException("Overrides are not available for Daedalus tables");
	}


	/** --------------------------------------- **/
	/** ---------- Additional Public ---------- **/

	/**
	 * Edit the classes parent only - useful in migrations
	 * @param array $changes
	 * @return int  the number of changes
	 */
	public function editClassParent($changes)
	{
		return parent::editClass($changes);
	}

	/** ---------------------------------------- **/
	/** -------- Implementation Methods -------- **/

	/**
	 * Get the form definition either for a class form or a class builder
	 * definition. The key difference here is whether or not to show the
	 * deleted fields.
	 *
	 * @param boolean $excludeDeletedFields
	 * @param array $restrictToFields  restrict the definition to these fields
	 *   reordering the form in the process
	 * @return array
	 */
	protected function getDefinitionAsForm($excludeDeletedFields=true, array $restrictToFields=[])
	{
		$fields = [];
		$definition = $this->definition;
		$order = 0;
		$restrictFields = count($restrictToFields) > 0;
		if ($restrictFields)
			$restrictFieldPostion = array_flip($restrictToFields);
		if (isset($definition['fields'])) {
			$fields = $definition['fields'];
			foreach ($fields as $key=>$field) {
				if ($restrictFields && !in_array($key, $restrictToFields)) {
					unset($fields[$key]);
					continue;
				}
				if (!empty($field['deleted']) && $excludeDeletedFields) {
					unset($fields[$key]);
					continue;
				}
				if (isset($field['definition'])) {
					$fields[$key] = $field['definition'];
					$fields[$key]['mapField'] = $field['mapField'];
					// add validation?

					if (isset($field['deleted']))
						$fields[$key]['deleted'] = $field['deleted'];
					// TODO - 20180522 NJ
					// THIS WILL GO WRONG WITH DELETED FIELDS WHERE FINAL SUBMIT BUTTON MAY BE IN WRONG PLACE
					$fields[$key]['order'] = ($restrictFields ? ($restrictFieldPostion[$key]) : $order++);
				} else {
					$fields[$key] = [];
				}
			}
		}
		$definition['fields'] = $fields;
		return $definition;
	}

	protected $_dds = null;
	protected function dds()
	{
		if ($this->_dds == null)
			$this->_dds = neon('dds')->iDdsClassManagement;
		return $this->_dds;
	}

}