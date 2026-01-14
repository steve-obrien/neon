<?php
namespace neon\core\helpers;

use neon\core\db\Query;

class Dds
{
	CONST TEXTSHORT_LENGTH = 150;

	/**
	 * wrapper for dds getObject which also confirms it's the expected class
	 * @param string $ddsClass
	 * @param string $uuid
	 * @return array|null results or null if not found
	 * @throws \Exception
	 */
	public static function getObject(string $ddsClass, string $uuid): ?array
	{
		if (!self::confirmDdsClass($uuid, $ddsClass)) {
			throw new \Exception("Unexpected dds class for uuid='".$uuid."' was expecting '".$ddsClass."'");
		}
		$rows = [ neon()->dds->IDdsObjectManagement->getObject($uuid) ];
		self::normaliseRows($ddsClass, $rows);
		return reset($rows);
	}

	/**
	 * wrapper for dds editObject which also confirms it's the expected class
	 * @param string $ddsClass
	 * @param string $uuid
	 * @param array $data
	 * @return array|bool true or an array of errors
	 * @throws \Exception
	 */
	public static function editObject(string $ddsClass, string $uuid, array $data)
	{
		$check = self::confirmDdsClass($uuid, $ddsClass);
		if ($check===null) {
			throw new \Exception("Object with uuid='".$uuid."' was not found");
		}
		elseif (!$check) {
			throw new \Exception("Unexpected dds class for uuid='".$uuid."' was expecting '".$ddsClass."'");
		}
		return neon()->dds->IDdsObjectManagement->editObject($uuid, $data);
	}

	/**
	 * wrapper for dds addObject (brings nothing to the table but being a little more terse!)
	 * @param string $ddsClass
	 * @param array $data
	 * @return string uuid of the added object
	 */
	public static function addObject(string $ddsClass, array $data): string
	{
		return neon()->dds->IDdsObjectManagement->addObject($ddsClass, $data);
	}

	/**
	 * helper for getting a single dds result.
	 * @param string $classType
	 * @param array $filter @see \neon\daedalus\interfaces\IDdsObjectManagement
	 * @param bool $failIfMultiple optionally fail if there are multiple results for the given filter, otherwise just return the first
	 * @return null|array
	 * @throws \Exception
	 */
	public static function getOne(string $classType, array $filter, bool $failIfMultiple=true): ?array
	{
		$results = neon()->dds->IDdsObjectManagement->runSingleObjectRequest($classType, self::normaliseFilter($filter));
		if (count($results['rows']) === 0) {
			return null;
		}
		if (count($results['rows']) > 1 && $failIfMultiple) {
			throw new \Exception("Unexpected multiple results for dds class '" . $classType . "' using filter " . print_r($filter, true));
		}
		$rows = [$results['rows'][0]];
		self::normaliseRows($classType, $rows);
		$row = reset($rows);
		return $row===false ? null : $row;
	}

	/**
	 * helper for getting multiple dds results
	 * @param string $classType
	 * @param array $filter
	 * @param array $order
	 * @param array $limit
	 * @return array
	 */
	public static function getMany(string $classType, array $filter=[], array $order=[], array $limit=[])
	{
		$normalisedFilter =  self::normaliseFilter($filter);
		$results = neon()->dds->IDdsObjectManagement->runSingleObjectRequest($classType, $normalisedFilter, $order, $limit);
		$rows = $results['rows'];
		self::normaliseRows($classType, $rows);
		return $rows;
	}

	/**
	 * hard delete the object
	 * @param string $classType
	 * @param string $objectUuid
	 * @throws \Exception
	 */
	public static function destroyObject(string $classType, string $objectUuid)
	{
		if (!self::confirmDdsClass($objectUuid, $classType)) {
			throw new \Exception("Object uuid='".$objectUuid."' does not refer to the expected class '".$classType."'");
		}
		neon()->dds->IDdsObjectManagement->destroyObject($objectUuid);
	}

	/**
	 * soft delete the object
	 * @param string $classType
	 * @param string $objectUuid
	 * @throws \Exception
	 */
	public static function deleteObject(string $classType, string $objectUuid)
	{
		if (!self::confirmDdsClass($objectUuid, $classType)) {
			throw new \Exception("Object uuid='".$objectUuid."' does not refer to the expected class '".$classType."'");
		}
		neon()->dds->IDdsObjectManagement->deleteObject($objectUuid);
	}

	/**
	 * destroyObjects sometimes fails to remove all the specified ones for some reason
	 * @param string $classType
	 * @return bool
	 * @throws \Exception
	 */
	public static function truncateTable(string $classType): bool
	{
		$tableExists = neon()->db->createCommand("SHOW TABLES LIKE 'ddt_".$classType."'")->queryOne();
		if (!$tableExists) throw new \Exception("Failed to find table 'ddt_".$classType."'");

		$chunkSize = 10000;
		$rowCount = neon()->db->createCommand("SELECT COUNT(_uuid) FROM ddt_".$classType)->queryScalar();
		$chunks = (int)ceil($rowCount/$chunkSize);
		if ($chunks>0) {
			// remove any dds links
			for ($chunkIdx=0; $chunkIdx<$chunks; $chunkIdx++) {
				$offset = $chunkIdx * $chunkSize;
				$chunkUuids = neon()->db->createCommand("SELECT _uuid FROM ddt_".$classType." LIMIT ".$chunkSize." OFFSET ".$offset)->queryColumn();
				$inClause = implode(",", array_fill(0, count($chunkUuids), '?'));
				neon()->db->createCommand(
					'DELETE FROM dds_link WHERE from_id IN (' . $inClause . ')',
					self::reindexArray($chunkUuids, 1)
				)->execute();
				neon()->db->createCommand(
					'DELETE FROM dds_link WHERE to_id IN (' . $inClause . ')',
					self::reindexArray($chunkUuids, 1)
				)->execute();
			}
		}

		// remove dds objects
		neon()->db->createCommand('DELETE FROM dds_object WHERE _class_type=:classType',['classType'=>$classType])->execute();

		// truncate the actual table
		neon()->db->createCommand('TRUNCATE TABLE ddt_' . $classType)->execute();

		// reset count total
		neon()->db->createCommand('UPDATE dds_class SET count_total=0 WHERE class_type=:classType',['classType'=>$classType])->execute();

		return true;
	}

	/**
	 * get a simple query for a dds table
	 * @param $classType
	 * @return Query
	 */
	public static function newQuery($classType): Query
	{
		$query = new \neon\core\db\Query();
		$query
			->select('*')
			->from('ddt_'.$classType.' '.$classType)
			->join('INNER JOIN', 'dds_object o', 'o._uuid='.$classType.'._uuid AND o._deleted=0');
		return $query;
	}

	/**
	 * adds a join from one dds table to another to an existing Query to facilitate pagination (and other non-trivial
	 * queries)
	 * Note : this makes no attempt to select or group anything for you
	 * @param Query $query
	 * @param string $fromClassType
	 * @param string $toClassType
	 * @param string $type
	 * @throws \Exception
	 */
	public static function addQueryJoin(Query $query, string $fromClassType, string $toClassType, string $type='INNER JOIN')
	{
		if (!self::queryHasClass($query, $fromClassType)) {
			throw new \Exception("Query can't join from '".$fromClassType."' since it's not currently in the from clause!");
		}
		$joinMember = self::identifyLinkMember($fromClassType, $toClassType);
		if ($joinMember) {
			// owning side
			if ($joinMember['data_type_ref']=='link_uni') {
				$query->join($type, "ddt_" . $toClassType . " " . $toClassType, $joinMember["class_type"] . "." . $joinMember['member_ref'] . "=" . $toClassType . "._uuid");
			}
			elseif ($joinMember['data_type_ref']=='link_multi') {
				$linkAlias = $toClassType."_link";
				$query->join($type, "dds_link ".$linkAlias, $joinMember["class_type"]."._uuid=".$linkAlias.".from_id AND ".$linkAlias.".from_member='".$joinMember['member_ref']."'");
				$query->join($type, "ddt_".$toClassType." ".$toClassType, $toClassType."._uuid=".$linkAlias.".to_id");
			}
			else {
				throw new \Exception("As yet unsupported link type ".$joinMember['data_type_ref']);
			}
			// maybe the following should always be an inner join?
			$query->join($type, "dds_object o_".$toClassType, "o_".$toClassType."._uuid=".$toClassType."._uuid AND o_".$toClassType."._deleted=0");
			return;
		}

		$joinMember = self::identifyLinkMember($toClassType, $fromClassType);
		if ($joinMember) {
			// owned side
			if ($joinMember['data_type_ref']=='link_uni') {
				$query->join($type, "ddt_" . $toClassType . " " . $toClassType, $joinMember['class_type'] . "." . $joinMember['member_ref'] . "=" . $fromClassType . "._uuid");
			}
			elseif ($joinMember['data_type_ref']=='link_multi') {
				$linkAlias = $toClassType."_link";
				$query->join($type, "dds_link ".$linkAlias, $joinMember["link_class"]."._uuid=".$linkAlias.".to_id AND ".$linkAlias.".from_member='".$joinMember['member_ref']."'");
				$query->join($type, "ddt_".$toClassType." ".$toClassType, $toClassType."._uuid=".$linkAlias.".from_id");
			}
			else {
				throw new \Exception("As yet unsupported link type ".$joinMember['data_type_ref']);
			}
			// maybe the following should always be an inner join?
			$query->join($type, "dds_object o_".$toClassType, "o_".$toClassType."._uuid=".$toClassType."._uuid AND o_".$toClassType."._deleted=0");
			return;
		}
		throw new \Exception("The classes '".$fromClassType."' and '".$toClassType."' are not joined");
	}

	/******************************************************************************************************************/

	/**
	 * confirms uuid refers to the expected dds class
	 * @param string $uuid
	 * @param string $expectedClassType
	 * @return bool|null true if it matches, false if it doesn't, and null if the object was not found
	 */
	private static function confirmDdsClass(string $uuid, string $expectedClassType): ?bool
	{
		$query = neon()->db->query()
			->select('_class_type')
			->from('dds_object')
			->where("_uuid=:uuid",['uuid'=>$uuid]);
		$ddsClass = $query->scalar();
		return $ddsClass
			? $ddsClass===$expectedClassType
			: null;
	}

	/**
	 * allows for associative array style ANDed filters
	 * e.g. ['_uuid' => $uuid] becomes
	 *   WHERE _uuid=$uuid
	 * or
	 *   WHERE _uuid IN ($uuid) if $uuid contains an array
	 * @param array $filter
	 * @return array
	 */
	private static function normaliseFilter(array $filter): array
	{
		if (count($filter)===0) return [];
		if (array_keys($filter) !== range(0, count($filter)-1)) {
			// associative so assume it's simple ANDed key=>value pairs
			$newFilter = [];
			foreach ($filter as $key=>$value) {
				if (is_array($value)) {
					$newFilter[] = [$key, 'IN', $value];
				}
				else {
					$newFilter[] = [$key, '=', $value];
				}
			}
			return $newFilter;
		}
		else {
			return $filter;
		}
	}

	/**
	 * normalise dds result rows for various types to make life a little more bearable:
	 *  'choice' type just returns the key rather than array of key, value & type
	 *  'choice_multiple' an array of keys rather than a json string
	 * @param string $classType
	 * @param array $rows
	 */
	private static function normaliseRows(string $classType, array &$rows)
	{
		$metadata = self::getMetadata($classType);
		array_walk($rows, function(&$row) use ($metadata) {
			array_walk($row,function(&$value, $key) use ($metadata) {
				if (isset($metadata[$key])) {
					$fieldMetadata = $metadata[$key];
					if ($fieldMetadata['data_type_ref'] === 'choice') {
						$value = $value['key'];
					} elseif ($fieldMetadata['data_type_ref'] === 'choice_multiple') {
						$data = json_decode($value, JSON_OBJECT_AS_ARRAY);
						$value = (is_array($data) && count($data)>0)
							? array_values($data)
							: [];
					}
				}
			});
		});
	}

	/**
	 * mainly since PDO expects 1 based arrays when not naming parameters
	 * @param array $arr
	 * @param int $startIdx
	 * @return array|false
	 */
	private static function reindexArray(array $arr, int $startIdx=0)
	{
		if (count($arr)==0) return [];
		$arr = array_values($arr);
		if ($startIdx==0) return $arr;
		$newIndex = range($startIdx, $startIdx+count($arr)-1);
		return array_combine($newIndex,$arr);
	}

	/**
	 * gets metadata info on the specified dds class
	 * @param string $classType
	 * @param string|null $field optionally specify a particular field just to get its info
	 * @return array|mixed|null
	 */
	private static function getMetadata(string $classType, string $field=null)
	{
		static $metadata = [];
		if (!isset($metadata[$classType])) {
			$fieldsMetadata = neon()->db->query()
				->select('*')
				->from('dds_member')
				->where(['class_type'=>$classType])
				->andWhere('deleted=0')
				->all();
			$metadata[$classType] = array_column($fieldsMetadata,  null, 'member_ref');
		}
		return $field
			? $metadata[$classType][$field] ?? null
			: $metadata[$classType];
	}

	/**
	 * checks to see if a class has already been included via the FROM clause or via a JOIN
	 *
	 * weird formatting from getTablesUseInFrom for no good or explained reason:
	 *   https://www.yiiframework.com/doc/api/2.0/yii-db-query#getTablesUsedInFrom()-detail
	 * @param Query $query
	 * @param string $classType
	 * @return bool
	 * @throws \Exception
	 */
	public static function queryHasClass(Query $query, string $classType): bool
	{
		$ddtTableName = 'ddt_'.$classType;
		$checkFor = '{{'.$ddtTableName.'}}';
		foreach ($query->getTablesUsedInFrom() as $tableAlias=>$table) {
			if ($checkFor === $table) {
				return true;
			}
		}
		if ($query->join===null) return false;
		foreach($query->join as $checkJoin) {
			list($discardJoinType, $tableAndAlias, $discardJoinCondition) = $checkJoin;
			list($table) = explode(' ',$tableAndAlias);
			if ($ddtTableName === $table) {
				return true;
			}
		}
		return false;
	}

	/**
	 * attempts to retrieve the dds link member between two tables
	 * Note this assumes there will only be one (not quite sure if this is correct or not!)
	 *
	 * @param string $fromClassType
	 * @param string $toClassType
	 * @param string|false $linkType false => just bring back the first match regardless of type
	 * @return false|array
	 * @throws \Exception
	 */
	public static function identifyLinkMember(string $fromClassType, string $toClassType, $linkType=false)
	{
		$tableMembers = self::getMetadata($fromClassType);
		foreach ($tableMembers as $member) {
			if ((!$linkType || $member['data_type_ref']===$linkType) && $member['link_class']===$toClassType) {
				return $member;
			}
		}
		return false;
	}

	/**
	 * identifies the main mapping member for a given classType
	 * Note this assumes there will be only one per table
	 *
	 * @param string $classType
	 * @return false|array
	 * @throws \Exception
	 */
	public static function identifyMappingMember(string $classType)
	{
		$tableMembers = self::getMetadata($classType);
		foreach ($tableMembers as $member) {
			if ($member['map_field']==1) {
				return $member;
			}
		}
		return false;
	}
}
