<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsManager;

use Exception;
use InvalidArgumentException;
use neon\core\helpers\Arr;
use neon\core\helpers\Hash;
use neon\daedalus\interfaces\IDdsBase;
use neon\daedalus\services\ddsManager\models\DdsClass;
use neon\daedalus\services\ddsManager\models\DdsDataType;
use neon\daedalus\services\ddsManager\models\DdsMember;
use neon\daedalus\services\ddsManager\models\DdsObject;
use neon\daedalus\services\ddsManager\models\DdsStorage;
use yii\base\Component;


class DdsCore extends Component implements IDdsBase
{
	public const MAX_LENGTH = 1000;
	public const MYSQL_DEFAULT_COLLATION = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

	/** ---------- IDdsBase Methods ---------- **/

	/** ---------- Utility Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function canonicalise($reference)
	{
		return $this->canonicaliseRef($reference);
	}

	/**
	 * @inheritdoc
	 */
	public function listStorageTypes()
	{
		return DdsStorage::find()->asArray()->all();
	}

	/**
	 * @inheritdoc
	 */
	public function now()
	{
		return date('Y-m-d H:i:s');
	}

	/** -------------------------------------- **/
	/** ---------- Protected Methods --------- **/
	/** -------------------------------------- **/

	/**
	 * store a set of migrations
	 * @param string $up
	 * @param string $down
	 * @return string  the migration id
	 */
	protected function storeMigration($up, $down)
	{
		return neon()->dds->IDdsAppMigrator->storeMigration($up, $down);
	}

	/**
	 * Remove a migration entry
	 *
	 * @param string $id  the id for the migration to be removed
	 */
	protected function removeMigration($id)
	{
		neon()->dds->IDdsAppMigrator->removeMigration($id);
	}

	/**
	 * create a table for a particular class type
	 * @param type $classType
	 */
	protected function createClassTable($classType)
	{
		$tableName = $this->getTableFromClassType($classType);
		// create the up and down sql migration code
		$upSql = $this->getCreateTableSql($tableName);
		$downSql = $this->getDropTableSql($classType);
		neon()->db->createCommand($upSql)->execute();
		$this->storeMigration($upSql, $downSql);
	}

	/**
	 * drops a table for a particular class type
	 * @param type $classType
	 */
	protected function dropClassTable($classType)
	{
		$tableName = $this->getTableFromClassType($classType);
		$upSql = $this->getDropTableSql($classType);
		$downSql = $this->getCreateTableSql($tableName);
		foreach ($upSql as $up)
			neon()->db->createCommand($up)->execute();
		$this->storeMigration($upSql, $downSql);
	}

	protected function addClassMemberColumn($classType, $memberRef)
	{
		$storage = $this->getMemberStorage($classType, $memberRef);
		$table = $this->getTableFromClassType($classType);
		$member = $storage[$memberRef];
		// handle some storage differences
		$columnCheck = substr($member['column'], 0, 4);
		if ($columnCheck == 'CHAR' || $columnCheck == 'UUID') {
			$size = (isset($member['definition']['size'])) ? $member['definition']['size'] : 150;
			$member['column'] = str_replace($columnCheck, "CHAR($size)", $member['column']);
		}
		try {
			$upMember = "ALTER TABLE `$table` ADD `$memberRef` $member[column];";
			$downMember = "ALTER TABLE `$table` DROP `$memberRef`;";
			neon()->db->createCommand($upMember)->execute();
			$this->storeMigration($upMember, $downMember);
			if ($member['index']) {
				$upIndex = "ALTER TABLE `$table` ADD " . $member['index'] . "(`$memberRef`);";
				$downIndex = "ALTER TABLE `$table` DROP INDEX `$memberRef`;";
				neon()->db->createCommand($upIndex)->execute();
				$this->storeMigration($upIndex, $downIndex);
			}
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	protected function dropClassMemberColumn($classType, $memberRef)
	{
		$table = $this->getTableFromClassType($classType);
		$storage = $this->getMemberStorage($classType, $memberRef);
		$member = $storage[$memberRef];
		try {
			if ($storage[$memberRef]['index']) {
				$upIndex = "ALTER TABLE `$table` DROP INDEX `$memberRef`;";
				$downIndex = "ALTER TABLE `$table` ADD " . $member['index'] . "(`$memberRef`);";
				neon()->db->createCommand($upIndex)->execute();
				$this->storeMigration($upIndex, $downIndex);
			}
			$upMember = "ALTER TABLE `$table` DROP `$memberRef`;";
			$downMember = "ALTER TABLE `$table` ADD `$memberRef` $member[column];";
			neon()->db->createCommand($upMember)->execute();
			$this->storeMigration($upMember, $downMember);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * get the storage table for each member ref defined in the class type
	 * @param string $classType
	 * @param string $memberRef  if set then just get that value
	 * @return []  array of ['member_ref']=>['data_type_ref', 'column', 'index']
	 *  where column is the db column type
	 */
	protected function getMemberStorage($classType, $memberRef = null)
	{
		$boundValues = [];
		$query = <<<EOQ
SELECT `m`.`member_ref`, `s`.`type`, d.`definition` FROM dds_member m
JOIN `dds_data_type` d ON `m`.`data_type_ref`=d.`data_type_ref`
JOIN `dds_storage` s ON `d`.`storage_ref`=s.`storage_ref`
WHERE m.`class_type`=:classType
EOQ;
		$boundValues[':classType'] = $classType;
		if ($memberRef) {
			$query .= ' AND `m`.`member_ref`=:memberRef';
			$boundValues[':memberRef'] = $memberRef;
		}

		$cmd = neon()->db->createCommand($query, $boundValues);
		$rows = $cmd->queryAll();
		$memberStorage = [];
		foreach ($rows as $r) {
			$memberStorage[$r['member_ref']] = [
				'column' => $this->getColumnType($r['type']),
				'index' => $this->getIndexType($r['type']),
				'definition' => empty($r['definition']) ? null : json_decode($r['definition'], true)
			];
		}
		return $memberStorage;
	}

	protected function getColumnType($storageType)
	{
		$type = '';
		switch (strtoupper($storageType)) {
			case 'INTEGER_TINY':
				$type = 'TINYINT';
				break;
			case 'INTEGER_SHORT':
				$type = 'SMALLINT';
				break;
			case 'INTEGER':
				$type = 'INT';
				break;
			case 'INTEGER_LONG':
				$type = 'BIGINT';
				break;
			case 'FLOAT':
				$type = 'FLOAT';
				break;
			case 'DOUBLE':
				$type = 'DOUBLE';
				break;
			case 'DATE':
				$type = 'DATE';
				break;
			case 'DATETIME':
				$type = 'DATETIME';
				break;
			case 'TIME':
				$type = 'TIME';
				break;
			case 'TEXT_SHORT':
				$type = 'VARCHAR(150)';
				break;
			case 'TEXT':
				$type = 'TEXT';
				break;
			case 'TEXT_LONG':
				$type = 'MEDIUMTEXT';
				break;
			case 'BINARY_SHORT':
				$type = 'BLOB';
				break;
			case 'BINARY':
				$type = 'MEDIUMBLOB';
				break;
			case 'BINARY_LONG':
				$type = 'LONGBLOB';
				break;
			case 'CHAR':
				$type = 'CHAR';
				break;
			case 'UUID':
				$type = 'UUID';
				break;
			default:
				$type = "UNKNOWN STORAGE TYPE $storageType";
				break;
		}
		$collation = $this->getCollation($storageType);
		if ($collation)
			return "$type $collation DEFAULT NULL ";
		return "$type DEFAULT NULL ";
	}

	protected function getIndexType($storageType)
	{
		$index = '';
		switch (strtoupper($storageType)) {
			case 'INTEGER_TINY':
			case 'INTEGER_SHORT':
			case 'INTEGER':
			case 'INTEGER_LONG':
			case 'FLOAT':
			case 'DOUBLE':
			case 'DATE':
			case 'DATETIME':
			case 'TIME':
			case 'TEXT_SHORT':
			case 'CHAR':
			case 'UUID':
				$index = 'INDEX';
				break;
			case 'TEXT':
			case 'TEXT_LONG':
			case 'BINARY_SHORT':
			case 'BINARY':
			case 'BINARY_LONG':
			default:
				$index = null;
				break;
		}
		return $index;
	}

	protected function getCollation($storageType)
	{
		$collation = null;
		switch ($storageType) {
			case 'UUID':
				$collation = 'CHARACTER SET latin1 COLLATE latin1_general_cs';
				break;
		}
		return $collation;
	}

	/**
	 * A cache of class metadata
	 * @var array
	 */
	private static $_classCache;

	/**
	 * A cache of class member metadata
	 * @var array
	 */
	protected static $_classMembersCache;

	/**
	 * A cache of map members for classes
	 * @var array
	 */
	protected static $_classMemberMapCache;

	/**
	 * get hold of a class
	 * @param string $classType
	 * @param DdsClass &$class
	 * @param bool $throwException [false] Whether we should throw an exception if the class is not found
	 * @throws InvalidArgumentException if class not found and $throwException is true
	 * @return bool  whether or not found
	 */
	protected function findClass($classType, &$class = null, $throwException = false)
	{
		$ct = $this->canonicaliseRef($classType);
		if (empty(self::$_classCache[$ct])) {
			self::$_classCache[$ct] = DdsClass::findOne(['class_type' => $ct]);
		}
		$class = self::$_classCache[$ct];
		if (!$class && $throwException)
			throw new InvalidArgumentException('Unknown class type "' . $ct . '"');
		return ($class !== null);
	}

	/**
	 * Clear the class database cache
	 * @param string $classType
	 */
	protected function clearClassCache($classType)
	{
		unset(self::$_classCache[$classType]);
		$this->clearClassMemberCache($classType);
	}

	/**
	 * Clear the class member cache
	 *
	 * @param string $classType
	 */
	protected function clearClassMemberCache($classType)
	{
		unset(static::$_classMembersCache[$classType]);
		unset(static::$_classMemberMapCache[$classType]);
	}

	/**
	 * get hold of a class member object
	 * @param string $classType the class type the member belongs to
	 * @param string $memberRef the member ref identifying this member in the class
	 * @param DdsMember &$member
	 * @return bool  whether or not found
	 */
	protected function findMember($classType, $memberRef, &$member)
	{
		$member = DdsMember::findOne(['class_type' => $classType, 'member_ref' => $memberRef]);
		return ($member != null);
	}

	/**
	 * @see IDdsClassManagement::listMembers
	 */
	protected function listMembersForClass($classType, $includeDeleted = false, $keyBy = 'member_ref')
	{
		if (!is_string($classType))
			throw new InvalidArgumentException('The class type $classType parameter should be a string');
		$select = ['member_ref', 'label', 'data_type_ref', 'description', 'choices', 'map_field', 'link_class'];
		if (!empty($keyBy) && !in_array($keyBy, $select))
			throw new InvalidArgumentException('Parameter keyBy must be empty or one of ' . print_r($select, true));

		// see if we have a cached version or getting from the database
		if (empty(static::$_classMembersCache[$classType][$includeDeleted])) {
			$query = DdsMember::find()->where(['class_type' => $classType]);
			if ($includeDeleted)
				$select[] = 'deleted';
			else
				$query->andWhere(['deleted' => 0]);
			$rows = $query->select($select)->orderBy('created')->asArray()->all();
			foreach ($rows as $k => $r)
				$rows[$k]['choices'] = json_decode($r['choices'] ?? '', true);
			static::$_classMembersCache[$classType][$includeDeleted] = $rows;
		}

		if (empty($keyBy))
			return static::$_classMembersCache[$classType][$includeDeleted];

		// key by a particular ref
		$results = [];
		foreach (static::$_classMembersCache[$classType][$includeDeleted] as $r)
			$results[$r[$keyBy]] = $r;

		return $results;
	}


	/**
	 * Get the map field for a class
	 * @param string $class
	 */
	protected function getMapMemberForClass($classType)
	{
		if (empty(static::$_classMemberMapCache[$classType])) {
			if ($this->findClass($classType, $class)) {
				$member = DdsMember::find()
					->where(['class_type' => $classType, 'map_field' => 1, 'deleted' => 0])
					->asArray()->limit(1)->one();
				$this->setClassMapMemberCache($classType, $member);
			}
		}
		return static::$_classMemberMapCache[$classType];
	}

	/**
	 * Set the map member for a class
	 * @param string $classType  the class
	 * @param string $member  its map member
	 */
	protected function setClassMapMemberCache($classType, $member)
	{
		static::$_classMemberMapCache[$classType] = $member;
	}

	/**
	 * get hold of a data type by its ref
	 * @param string $dataTypeRef
	 * @param DdsDataType &$dataType
	 * @return bool  whether or not found
	 */
	protected function findDataType($dataTypeRef, &$dataType = null)
	{
		$dataType = DdsDataType::findOne(['data_type_ref' => $dataTypeRef]);
		return ($dataType !== null);
	}

	/**
	 * see if there are any objects for a particular class type
	 * @param string $classType
	 * @return bool
	 */
	protected function hasObjects($classType)
	{
		$obj = DdsObject::findOne(['_class_type' => $classType]);
		return !empty($obj);
	}

	/**
	 * canonicalise a reference so that it follows a set pattern
	 *
	 * This is to prevent problems with queries where the field name may be
	 * illegitimate and also to help prevent SQL injection in raw queries
	 *
	 * @param string $ref  the uncanonicalised reference
	 * @return string  the canonicalised one
	 */
	protected function canonicaliseRef($ref)
	{
		return preg_replace('/[^a-z0-9_]/', '', strtolower(preg_replace('/ +/', '_', trim($ref))));
	}

	/**
	 * canonicalise an array of refs. These are assumed to be of the
	 * form [key]=>$ref
	 * @param array $refs  an array of uncanonicalised refs
	 * @return array  the array of canonicalised ones
	 */
	protected function canonicaliseRefs(array $refs)
	{
		$canon = [];
		foreach ($refs as $key => $ref) {
			$canon[$key] = $this->canonicaliseRef($ref);
		}
		return $canon;
	}

	/**
	 * Canonicalise a reference by parts where each part is canonicalised separately
	 * e.g. abc.def can be canonicalised separated by the '.' character
	 *
	 * @param string $ref the reference to canonicalise
	 * @param char $separator  the single character separator to split the string into its parts
	 * @return string  the canonicalised result
	 */
	protected function canonicaliseRefByParts($ref, $separator = '.')
	{
		$parts = explode($separator, $ref);
		$canons = [];
		foreach ($parts as $p)
			$canons[] = $this->canonicaliseRef($p);
		return implode($separator, $canons);
	}

	/**
	 * Adds ` around column names as well as correctly prefixing dds_object columns
	 * @param $ref
	 * @return string
	 */
	protected function quoteField($ref, $ddsObjectAlias = 'o')
	{
		if (in_array($ref, ['_uuid', '_created', '_updated', '_class_ref'])) {
			return "`$ddsObjectAlias`.`$ref`";
		}
		return neon()->db->quoteColumnName($this->canonicaliseRefByParts($ref));
	}

	/**
	 * canonicalise the filters for a query
	 * @param array $filters  the uncanonicalised ones
	 * @return array  the canonicalised ones
	 */
	protected function canonicaliseFilters($filters)
	{
		if (!is_array($filters))
			return [];
		try {
			$this->canonicaliseFiltersRecursive($filters);
		} catch (InvalidArgumentException $ex) {
			throw new InvalidArgumentException($ex->getMessage() . ' Filters passed in: ' . print_r($filters, true));
		}
		return $filters;
	}

	/**
	 * Filter recursively down through a set of filters until you find
	 * a filter clause and then canonicalise it
	 *
	 * @param array $filters
	 * @return null
	 */
	protected function canonicaliseFiltersRecursive(&$filters)
	{
		// Check if $filters is an associative array (key-value pair)
		if ($this->isAssociativeArray($filters)) {
			// Convert associative array into a set of filter clauses
			$convertedFilters = [];
			foreach ($filters as $key => $value) {
				$convertedFilters[] = [$key, '=', $value];
			}
			$filters = $convertedFilters;
		}

		// is this a filter clause or set of filter clauses??
		if (!is_array($filters) || count($filters) == 0)
			return;

		// recursively descend until one finds a filter clause
		if (is_array($filters[0])) {
			foreach ($filters as &$f)
				$this->canonicaliseFiltersRecursive($f);
			return;
		}
		// so canonicalise a filter clause
		if (array_key_exists(0, $filters))
			$this->canonicaliseFilter($filters[0], 0);
		if (array_key_exists(1, $filters))
			$this->canonicaliseFilter($filters[1], 1);

		// Handle nulls and adjust the query accordingly
		$this->handleNullValuesInFilters($filters);
	}

	/**
	 * Checks if an array is associative (has string keys).
	 *
	 * @param array $arr The array to check.
	 * @return bool True if associative, false otherwise.
	 */
	protected function isAssociativeArray(array $arr)
	{
		return count(array_filter(array_keys($arr), 'is_string')) > 0;
	}

	/**
	 * Handles null values in filters, adjusting the filter clause as necessary.
	 *
	 * @param array &$filters The filters to check and adjust.
	 */
	protected function handleNullValuesInFilters(&$filters)
	{
		if (array_key_exists(2, $filters) && $filters[2] === null) {
			if ($filters[1] === '=') $filters[1] = 'is null';
			else if ($filters[1] === '!=') $filters[1] = 'is not null';
			else throw new InvalidArgumentException('Incorrect filter - a null value was passed. The given filter was: ' . print_r([$filters[0], $filters[1], null], true));
		}
	}

	/**
	 * Canonicalise a part of the filters
	 * @param mixed $item
	 * @param int $key
	 * @throws InvalidArgumentException
	 */
	protected function canonicaliseFilter(&$item, $key)
	{
		if ($key === 0) {
			$item = $this->canonicaliseRefByParts($item);
		}
		if ($key === 1) {
			// accept only these operators
			switch (strtolower($item)) {
				case '=':
				case '!=':
				case '<':
				case '<=':
				case '>':
				case '>=':
					break;
				case 'in':
				case 'not in':
				case 'like':
				case 'not like':
				case 'is null':
				case 'is not null':
					$item = strtoupper($item);
					break;
				case 'null':
				case 'not null':
					// fix missing operator IS
					$item = 'IS ' . strtoupper($item);
					break;
				default:
					throw new InvalidArgumentException("Invalid comparison operator '$item' passed in filters");
			}
		}

		// $key == 2: values are handled through the use of PDO
		// $key == 3: keys are handled separately
	}

	/**
	 * Canonicalise the logic clause. This checks to see if all the keys
	 * are defined in the logic clause and that the logic clause doesn't contain
	 * any extraneous characters. Allowed additionals are AND, NOT, OR and ()
	 * @param string[] $keys
	 * @param string $logic
	 * @return string
	 */
	protected function checkLogic($keys, $logic)
	{
		// check there are no integer keys as this means not all keys are in the logic
		foreach ($keys as $k) {
			if ((int)$k === $k) {
				throw new InvalidArgumentException(
					'Daedalus: You have provided a logic string to the query, but it looks like not all filter clauses have a logic name added to them. All filter clauses need to represented in the logic statement.'
				);
			}
		}
		// test is to remove all keys and allowed characters and see if anything is
		// left over. If so then there must be bad characters ... one assumes
		$subLogic = str_replace($keys, '', $logic);
		$subLogic = str_replace(
			['AND', 'and', 'NOT', 'not', 'OR', 'or', ' ', ')', '('],
			'',
			$subLogic
		);
		if (strlen($subLogic) > 0)
			throw new InvalidArgumentException("Daedalus: Invalid logic operator provided. Maybe you haven't defined all keys or have other logic than 'AND', 'OR', 'NOT' and '(',')' characters in your logic? You have defined the keys as " . print_r($keys, true) . ' for a logic statement of ' . print_r($logic, true) . ' The remaining characters are ' . print_r($subLogic, true));
		return $logic;
	}

	/**
	 * Determine if an SQL operator takes an object or not
	 * e.g. >,= etc do whereas NOT NULL doesn't
	 * @param string $operator
	 * @return bool
	 */
	protected function operatorTakesObject($operator)
	{
		switch (strtolower($operator)) {
			case 'is not null':
			case 'is null':
				return false;
				break;
			default:
				return true;
				break;
		}
	}

	/**
	 * canonicalise the order clause
	 * @param array $order an array of [key]=>'ASC|DESC'
	 * @return type
	 */
	protected function canonicaliseOrder($order)
	{
		$canon = [];
		if (is_array($order)) {
			foreach ($order as $k => $d) {
				$drn = strtoupper($d);
				switch ($drn) {
					case 'ASC':
					case 'DESC':
						// allow -ve key starts for nulls last in MySql
						if (strpos($k, '-') === 0)
							$canon['-' . $this->quoteField($k)] = $drn;
						else
							$canon[$this->quoteField($k)] = $drn;
						break;
					case 'ASC_L':
						$canon[$k] = 'ASC';
						break;
					case 'DESC_L':
						$canon[$k] = 'DESC';
						break;
					case 'RAND()':
					case 'RAND':
						$canon['RAND'] = 'RAND';
						break;
				}
			}
		}
		return $canon;
	}

	/**
	 * canonicalise the requested limit
	 * @param array $limit  the uncanonicalised limit
	 * @param int &$total  the total value extracted from the limit
	 * @param bool &$calculateTotal  whether or not to calculate the total
	 *   This is true if $total is set to true.
	 * @return array  the canonicalised limit
	 */
	protected function canonicaliseLimit($limit, &$total, &$calculateTotal)
	{
		$canon = [];
		$total = null;
		$calculateTotal = false;
		if (is_array($limit)) {
			$canon = ['start' => 0, 'length' => self::MAX_LENGTH];
			foreach ($limit as $k => $v) {
				$key = strtolower($k);
				switch ($key) {
					case 'start':
						$canon[$key] = (int) $v;
						break;
					case 'length':
						$canon[$key] = min((int) $v, self::MAX_LENGTH);
						break;
					case 'total':
						// $v can be truthy or the previous integer
						$total = is_numeric($v) ? (int) $v : null;
						$calculateTotal = ($v === true || $v === 'true');
						break;
				}
			}
		}
		return $canon;
	}

	/**
	 * Convert row from the database to the formats required by PHP
	 * e.g. booleans from 1/0 to true/false
	 * @param [] $row  the row of data to be converted
	 * @param string $classTypeKey the key in the data that will give the class type
	 */
	protected function convertFromDBToPHP(&$row, $links = [], $classTypeKey = '_class_type')
	{
		$classType = $row[$classTypeKey];
		$members = $this->getClassMembers($classType);
		// now process only the member defined fields:
		foreach ($row as $key => &$value) {
			if (isset($members[$key])) {
				$memberLinks = isset($links[$key]) ? $links[$key] : [];
				$this->doConversionFromDBToPHP($members[$key], $value, $memberLinks);
			}
		}
	}

	/**
	 * The actual converter from DB to PHP. Override this if the data type is not
	 * handled here, and call this if not handled in the overridden method
	 * @param string $dataType the data type ref of the value
	 * @param mixed $value  the value returned by the database
	 */
	protected function doConversionFromDBToPHP($member, &$value, $memberLinks = [])
	{
		switch ($member['data_type_ref']) {
			case 'choice':
				// silently ignore deleted old choice as no longer valid
				if (is_array($member['choices']) && isset($member['choices'][$value])) {
					$value = ['key' => $value, 'value' => $member['choices'][$value], 'type' => 'choice'];
				}
				break;
			case 'choice_multiple':
				$choices = json_decode($value, true);
				$value = [];

				// protect against non array values
				if (!empty($choices) && is_array($choices)) {
					foreach ($choices as $choice) {
						// silently ignore deleted old choice as no longer valid
						if (isset($member['choices'][$choice]))
							$value[] = ['key' => $choice, 'value' => $member['choices'][$choice]];
					}
				}
				break;
			case 'boolean':
				if ($value === NULL)
					return;
				$value = !!$value;
				break;
			case 'json':
				$value = json_decode($value ?? '', true);
				break;
			case 'link_multi':
			case 'file_ref_multi':
				$value = $memberLinks;
				break;
		}
	}

	/**
	 * convert data from PHP format to the database format
	 * @param string $classType
	 * @param array $data  the object data
	 * @param array &$links  on return, any links are extracted into this
	 */
	protected function convertFromPHPToDB($classType, &$data, &$links)
	{
		$links = [];
		$members = $this->getClassMembers($classType);
		foreach ($data as $key => &$value) {
			$itemLinks = null;
			if (isset($members[$key])) {
				$this->doConversionFromPHPToDB($members[$key], $value, $itemLinks);
				if ($itemLinks !== null)
					$links[$key] = $itemLinks;
			}
		}
	}

	/**
	 * The actual converter from PHP to DB. Override this if the data type is not
	 * handled here, and call this if not handled in the overridden method
	 *
	 * @param array $member
	 * @param mixed $value
	 */
	protected function doConversionFromPHPToDB($member, &$value, &$links)
	{
		$links = null;
		switch ($member['data_type_ref']) {
			case 'choice':
				// convert from the value array to the key if the array
				// the array was returned
				if (is_array($value) && isset($value['key']))
					$value = $value['key'];
				break;
			case 'choice_multiple':
				$value = json_encode($value);
				break;
			case 'boolean':
				// check for null values
				if ($value === null)
					return;
				// convert from truthy to database 1 or 0
				$value = $value ? 1 : 0;
				break;
			case 'json';
				// json encode the PHP object / array
				$value = json_encode($value);
				break;
			case 'link_multi':
			case 'file_ref_multi':
				// extract out the links so they can be saved separately
				$links = empty($value) ? [] : $value;
				$value = null;
				break;
		}
	}

	/**
	 * get hold of all of the class members given a classType
	 * @param string $classType
	 * @param array $dataTypes  add to restrict members to certain types
	 * @return array the members
	 */
	protected function getClassMembers($classType, array $dataTypes = [])
	{
		static $_classMembers = [];
		if (!array_key_exists($classType, $_classMembers)) {
			try {
				// get the members and convert to array below to
				// make sure model afterFind has been run
				$members = DdsMember::find()->where(['class_type' => $classType])->all();
				$membersByRef = [];
				foreach ($members as $member)
					$membersByRef[$member['member_ref']] = $member->attributes;
				$_classMembers[$classType] = $membersByRef;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Error attempting to get members for $classType.");
			}
		}
		if (empty($dataTypes))
			return $_classMembers[$classType];
		$dataMembers = [];
		foreach ($_classMembers[$classType] as $k => $m) {
			if (in_array($m['data_type_ref'], $dataTypes))
				$dataMembers[$k] = $m;
		}
		return $dataMembers;
	}

	/**
	 * get the object table name from the class type
	 * @param string $classType
	 * @return string
	 */
	protected function getTableFromClassType($classType)
	{
		return 'ddt_' . $this->canonicaliseRef($classType);
	}

	/**
	 * Get the basic create sql for a table
	 * @param string $tableName
	 * @return string
	 */
	protected function getCreateTableSql($tableName)
	{
		$tableOptions = null;
		if (neon()->db->driverName === 'mysql') {
			$tableOptions = self::MYSQL_DEFAULT_COLLATION . ' ENGINE=MYISAM';
		}
		$uuidCollation = $this->getCollation('UUID');
		return "CREATE TABLE IF NOT EXISTS `$tableName` (`_uuid` CHAR(22) $uuidCollation NOT NULL COMMENT 'object _uuid from the dds_object table', PRIMARY KEY (`_uuid`)) $tableOptions;";
	}

	/**
	 * Get the basic drop table sql
	 * @param string $classType
	 * @return array
	 */
	protected function getDropTableSql($classType)
	{
		$classType = $this->canonicaliseRef($classType);
		$tableName = $this->getTableFromClassType($classType);
		return [
			"DELETE FROM `dds_object` WHERE `_class_type`='$classType';",
			"DROP TABLE IF EXISTS `$tableName`;"
		];
	}

	/**
	 * Converts an array of field=>values from a database row into a REPLACE SQL statement
	 * @param string $table
	 * @param object|array $row  the table row
	 * @return string
	 */
	protected function getTableRowReplaceSql($table, $row)
	{
		if (!is_array($row))
			$row = $row->toArray();
		$fields = [];
		$values = [];
		foreach ($row as $f => $v) {
			$fields[] = $f;
			$values[] = $this->pdoQuote($v);
		}
		if (count($fields))
			return "REPLACE INTO `$table` (`" . (implode('`,`', $fields)) . '`) VALUES (' . (implode(',', $values)) . ');';
		return null;
	}

	/**
	 * protect the values for PDO insertion
	 * @param mixed $value
	 * @return string
	 */
	private function pdoQuote($value)
	{
		if (is_array($value))
			$value = json_encode($value);
		if (is_null($value))
			return 'null';
		return neon()->db->pdo->quote($value);
	}


	/**
	 * generate a UUID
	 * @return string[22]  the uuid in base 64
	 */
	protected function generateUUID()
	{
		return Hash::uuid64();
	}

	/**
	 * test where or not an item is a UUID (in base 64)
	 *
	 * @param string $candidate
	 * @return bool
	 */
	protected function isUUID($candidate)
	{
		return Hash::isUuid64($candidate);
	}

	/**
	 * test whether or not an array of items are all UUIDs
	 * (in base 64)
	 *
	 * @param array $candidates
	 * @return bool
	 */
	protected function areUUIDs(array $candidates)
	{
		foreach ($candidates as $candidate) {
			if (!$this->isUUID($candidate))
				return false;
		}
		return true;
	}
}
