<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsManager;

use neon\daedalus\events\ObjectEditEvent;
use neon\daedalus\events\ObjectAddEvent;
use neon\daedalus\interfaces\IDdsObjectManagement;
use neon\daedalus\services\ddsManager\models\DdsClass;
use neon\daedalus\services\ddsManager\models\DdsObject;
use neon\daedalus\services\ddsManager\models\DdsLink;

use function PHPSTORM_META\argumentsSet;

class DdsObjectManager
extends DdsCore
implements IDdsObjectManagement
{

	/** ---------------------------------------------------- */
	/** ---------- interface IDdsObjectManagement ---------- */
	/** ---------------------------------------------------- */

	/**
	 * Caches for results from queries so can protect against over-zealous
	 * requests to the database within one call
	 * @var array
	 */
	private static $_requestResultsCache = [];
	private static $_linkResultsCache = [];
	private static $_mapResultsCache = [];

	public $sql = [];

	/**
	 * @inheritdoc
	 */
	public function addObjectRequest($classType, array $filters = [], array $order = [], array $limit = [], $resultKey = null, $includeDeleted = false)
	{
		// protect ourselves against SQL injection by making sure all
		// items are canonicalised as required (and use of PDO later)
		$classType = $this->canonicaliseRef($classType);
		$filters = $this->canonicaliseFilters($filters);
		$order = $this->canonicaliseOrder($order);
		$limit = $this->canonicaliseLimit($limit, $total, $calculateTotal);

		$request = [
			'classType' => $classType,
			'filters' => $filters,
			'order' => $order,
			'limit' => $limit,
			'total' => $total,
			'calculateTotal' => $calculateTotal,
			'deleted' => $includeDeleted
		];
		// add a request key so we can cache request results more easily
		$request['requestKey'] = md5(serialize($request));
		$result = $this->createSqlAndStoreRequest($request, $resultKey);
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function get($classType, array $filters = [], array $order = [], array $limit = [], $includeDeleted = false)
	{
		// cache
		$args = func_get_args();
		return neon()->cache->getOrSet(neon()->dds->cacheKey($args), function() use($args) {
			return $this->runSingleObjectRequest(...$args);
		});
	}

	/**
	 * @inheritdoc
	 */
	public function getObjectRequests()
	{
		return [
			'requests' => $this->objectRequests,
			'boundValues' => $this->boundValues
		];
	}

	/**
	 * @inheritdoc
	 */
	public function hasObjectRequests()
	{
		return !empty($this->objectRequests);
	}

	/**
	 * @inheritdoc
	 */
	public function commitRequests()
	{
		// return an empty array if there is nothing to do.
		if (!$this->hasObjectRequests()) {
			profile_end('DDS::COMMIT_REQUESTS', 'dds');
			return [];
		}

		profile_begin('DDS::COMMIT_REQUESTS', 'dds');

		// map resultkeys to requestKeys for caching
		$resultKey2RequestKey = [];

		// get already cached results or create the query
		$query = '';
		$sql = [];
		$data = [];
		$newData = [];
		foreach ($this->objectRequests as $requests) {
			foreach ($requests as $k => $r) {
				if (array_key_exists($r['requestKey'], self::$_requestResultsCache)) {
					$data[$k] = self::$_requestResultsCache[$r['requestKey']];
				} else {
					// set null result information about the request
					$newData[$k] = ['start' => $r['limit']['start'], 'length' => 0, 'total' => $r['total'], 'rows' => []];
					$sql[] = $r['sql'];
					if ($r['totalSql'])
						$sql[] = $r['totalSql'];
					$resultKey2RequestKey[$r['resultKey']] = $r['requestKey'];
				}
			}
		}

		// are there any new requests to make?
		if (count($sql)) {
			$query = implode('; ', $sql);
			// execute the query
			$yiiQuery = neon()->db->createCommand($query, $this->boundValues);
			$this->sql[] = $yiiQuery->getRawSql();
			$reader = $yiiQuery->query();

			// store object ids ready to get multilinks afterwards
			$objectIds = [];
			// extract the results
			do {
				$results = $reader->readAll();
				foreach ($results as $r) {
					$resultKey = $r[$this->resultKeyField];
					$resultKey2RequestKey[$resultKey] = $r[$this->requestKeyField];
					unset($r[$this->resultKeyField]);
					if (strpos($resultKey, '_total') !== false) {
						$newData[substr($resultKey, 0, -6)]['total'] = $r['total'];
					} else {
						$objectIds[$r['_uuid']] = $r['_uuid'];
						$newData[$resultKey]['rows'][] = $r;
						$newData[$resultKey]['length']++;
					}
				}
			} while ($reader->nextResult());

			// get all links across the entire resultset
			$membersLinks = $this->getMembersLinks($objectIds);

			// convert any data to PHP format
			foreach ($newData as $k => &$d) {
				$this->convertResultsToPHP($d['rows'], $membersLinks);
				self::$_requestResultsCache[$resultKey2RequestKey[$k]] = $d;
				$data[$k] = $d;
			}
		}
		$this->lastCommitResults = $data;
		$this->clearRequests();

		profile_end('DDS::COMMIT_REQUESTS', 'dds');

		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function getLastCommitData($requestKey)
	{
		return isset($this->lastCommitResults[$requestKey]) ? $this->lastCommitResults[$requestKey] : [];
	}

	/**
	 * @inheritdoc
	 */
	public function runSingleObjectRequest($classType, array $filters = [], array $order = [], array $limit = [], $includeDeleted = false)
	{
		if (empty($classType))
			throw new \InvalidArgumentException('ClassType must be specified');

		// make sure that any currently queued requests from elsewhere are saved off the queue
		$currentRequests = $this->popRequests();

		// make the object request
		$requestId = $this->addObjectRequest($classType, $filters, $order, $limit, null, $includeDeleted);
		$data = $this->commitRequests();

		// and store them back again
		$this->pushRequests($currentRequests);

		return $data[$requestId];
	}


	/** ---------- Object Map Management ---------- **

	/**
	 * @inheritdoc
	 */
	public function getObjectMap($classType, $filters = [], $fields = [], $start = 0, $length = 1000, $includeDeleted = false)
	{
		return $this->getObjectMapManager()->getObjectMap($classType, $filters, $fields, $start, $length, $includeDeleted);
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($objectUuids, $mapFields = [], $classType = null)
	{
		return $this->getObjectMapManager()->makeMapLookupRequest($objectUuids, $mapFields, $classType);
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapChainLookupRequest($objectUuids, $chainMembers = [], $chainMapFields = [], $inReverse = false)
	{
		return $this->getObjectMapManager()->makeMapChainLookupRequest($objectUuids, $chainMembers, $chainMapFields, $inReverse);
	}


	/**
	 * @inheritdoc
	 */
	public function commitMapRequests()
	{
		return $this->getObjectMapManager()->commitMapRequests();
	}

	/**
	 * @inheritdoc
	 */
	public function getMapLookupResults($requestKey)
	{
		return $this->getObjectMapManager()->getMapLookupResults($requestKey);
	}

	/**
	 * @inheritdoc
	 */
	public function getMapChainLookupResults($requestKey)
	{
		return $this->getObjectMapManager()->getMapChainLookupResults($requestKey);
	}

	/**
	 * @inheritdoc
	 */
	public function clearMapRequests()
	{
		return $this->getObjectMapManager()->clearMapRequests();
	}

	/** ---------- Basic Object Management ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listObjects($classType, &$total = null, $includeDeleted = false, $inFull = true, $start = 0, $length = 100, $order = null)
	{
		$classType = $this->canonicaliseRef($classType);
		if (empty($order))
			$order = ['_created' => 'DESC'];
		$order = $this->canonicaliseOrder($order);

		// get the total first
		$class = null;
		$this->findClass($classType, $class, true);
		$total = $class->count_total;
		if (!$includeDeleted)
			$total -= $class->count_deleted;

		// now get the objects
		$query = (new \neon\core\db\Query)
			->from('{{dds_object}} o')
			->select($inFull ? "[[t]].*, [[o]].*" : "[[o]].*")
			->where(['[[o]].[[_class_type]]' => $classType])
			->offset($start)
			->limit($length);

		if (!$includeDeleted)
			$query->andWhere('[[o]].[[_deleted]] = 0');
		if ($inFull) {
			$tableName = $this->getTableFromClassType($classType);
			$query->innerJoin('{{' . $tableName . '}} t', '[[o]].[[_uuid]]=[[t]].[[_uuid]]');
		}

		if (is_array($order)) {
			$orderBits = [];
			foreach ($order as $k => $d) {
				if ($k[1] == 'o') {
					$orderBits[] = "$k $d";
				} else if ($inFull) {
					$orderBits[] = "[[t]].$k $d";
				}
			}
			if ($orderBits)
				$orderClause = implode(', ', $orderBits);
			$query->orderBy($orderClause);
		}

		$data = $query->all();
		$this->convertResultsToPHP($data);
		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function addObject($classType, array $data, &$changeLogUuid = null)
	{
		$class = null;
		$classType = $this->canonicaliseRef($classType);
		$this->findClass($classType, $class, true);
		if ($class->hasChangeLog())
			$newValues = $data;
		$table = $this->getTableFromClassType($classType);

		// create the new object
		$object = new DdsObject;
		$now = date('Y-m-d H:i:s');
		$uuid = (!empty($data['_uuid']) && $this->isUUID($data['_uuid'])) ? $data['_uuid'] : $this->generateUUID();
		$object->_uuid = $uuid;
		$object->_class_type = $classType;
		$object->_created = $now;
		$object->_updated = $now;
		if (!$object->save())
			throw new \RuntimeException("Couldn't create object: " . print_r($object->errors, true));

		// increment the class's object count
		$class->count_total += 1;
		$class->save();

		// then insert all of the associated data
		try {
			$this->convertFromPHPToDB($classType, $data, $links);
			$memberStorage = $this->getMemberStorage($classType);
			$count = 0;
			$fields = [];
			$inserts = [];
			$values = [];
			foreach (array_keys($memberStorage) as $memRef) {
				// save all non-empty fields.
				if (array_key_exists($memRef, $data) && $data[$memRef] !== null && $data[$memRef] !== '') {
					$fields[] = "`$memRef`";
					$inserts[] = ":v$count";
					$values[":v$count"] = $data[$memRef];
					$count++;
				}
			}

			if (count($fields))
				$query = "INSERT INTO `$table` (`_uuid`, " . implode(', ', $fields) . ") VALUES ('{$object->_uuid}', " . implode(', ', $inserts) . ")";
			else
				$query = "INSERT INTO `$table` (`_uuid`) VALUES ('{$object->_uuid}')";
			$command = neon()->db->createCommand($query);
			$command->bindValues($values);
			$command->execute();

			// if there are any links then add these to the link table
			$this->setMemberLinks($object->_uuid, $links);

			if ($class->hasChangeLog())
				$changeLogUuid = $this->createChangeLogEntry($class, 'ADD', $object->_uuid, [], $newValues);
		} catch (\Exception $e) {
			// execution failed so clean up the object
			$object->delete();
			$this->clearCaches();
			throw $e;
		}
		$this->clearCaches();

		$this->trigger("dds.afterAdd.$classType", new ObjectAddEvent([
			'classType' => $classType,
			'object' => array_merge($data, $object->toArray())
		]));
		return $object->_uuid;
	}

	/**
	 * @inheritdoc
	 */
	public function addObjects($classType, array $data, $chunkSize = 1000)
	{
		$classType = $this->canonicaliseRef($classType);
		$class = null;
		$this->findClass($classType, $class, true);
		$table = $this->getTableFromClassType($classType);
		$hasChangeLog = $class->hasChangeLog();

		// get a list of the columns
		$columns = [];
		$nullRow = [];
		$memberStorage = $this->getMemberStorage($classType);
		foreach (array_keys($memberStorage) as $memRef) {
			$columns[] = $memRef;
			$nullRow[$memRef] = null;
		}
		$columns[] = '_uuid';
		$nullRow['_uuid'] = null;

		foreach (array_chunk($data, $chunkSize) as $chunkData) {
			$rows = [];
			$objectDbRows = [];
			$objectsLinks = [];
			$now = date('Y-m-d H:i:s');
			foreach ($chunkData as $row) {
				if ($hasChangeLog)
					$newValues = $row;
				// ddt table value - format row data and append the uuid
				$this->convertFromPHPToDB($classType, $row, $links);
				$uuid = (!empty($row['_uuid']) && $this->isUUID($row['_uuid'])) ? $row['_uuid'] : $this->generateUUID();
				$row['_uuid'] = $uuid;
				// only include data that belongs in the table and null value remainder
				$rows[] = array_replace($nullRow, array_intersect_key($row, $nullRow));
				// object table row value
				$objectDbRows[] = [$row['_uuid'], $classType, $now, $now];
				$objectsLinks[$row['_uuid']] = $links;
				if ($hasChangeLog)
					$this->createChangeLogEntry($class, 'ADD', $uuid, [], $newValues);
			}
			neon()->db->createCommand()->batchInsert(DdsObject::tableName(), ['_uuid', '_class_type', '_created', '_updated'], $objectDbRows)->execute();
			neon()->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
			$this->setMembersLinks($objectsLinks, $chunkSize);
			$class->count_total += count($chunkData);
		}
		$class->save();
		$this->clearCaches();
	}

	/**
	 * @inheritdoc
	 */
	public function getObject($uuid)
	{
		$object = null;
		if ($this->getObjectFromId($uuid, $object)) {
			$table = $this->getTableFromClassType($object->_class_type);
			$query = "SELECT `o`.*, `t`.* FROM `dds_object` `o` LEFT JOIN  `$table` `t` ON `o`.`_uuid`=`t`.`_uuid` WHERE `o`.`_uuid`=:uuid LIMIT 1";
			$rows = neon()->db->createCommand($query)->bindValue(':uuid', $uuid)->queryAll();
			$row =  count($rows) > 0 ? $rows[0] : null;
			$this->convertFromDBToPHP($row, $this->getMemberLinks($uuid));
			return $row;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function editObject($uuid, array $changes, &$changeLogUuid = null)
	{
		// check there are some changes to make
		if (count($changes) == 0)
			return false;

		// check that the object exists and find its class type
		if (!$this->getObjectFromId($uuid, $object))
			throw new \InvalidArgumentException("Couldn't edit object with id $uuid as not found");

		// see if we need to store the change log
		$class = null;
		$this->findClass($object['_class_type'], $class);

		$object->_updated = date('Y-m-d H:i:s');
		if (!$object->save())
			return $object->errors;

		// now update any member changes
		try {
			// note before and after values if has a change log
			$currentObject = $this->getObject($uuid);
			$memberStorage = array_diff_key(array_keys($currentObject), array_keys($object->attributes));
			$originalValues = array_intersect_key($currentObject, $changes);
			$newValues = array_intersect_key($changes, array_flip($memberStorage));

			$this->convertFromPHPToDB($object['_class_type'], $changes, $links);
			$table = $this->getTableFromClassType($object['_class_type']);

			$count = 0;
			$updates = [];
			$values = [];
			foreach ($memberStorage as $memRef) {
				if (array_key_exists($memRef, $changes)) {
					// when saving changes, need to manage empty fields by setting to null
					// otherwise saving what has been provided
					if ($changes[$memRef] === null || $changes[$memRef] === '') {
						$updates[] = "`$memRef`=NULL";
					} else {
						$updates[] = "`$memRef`=:v$count";
						$values[":v$count"] = $changes[$memRef];
						$count++;
					}
				}
			}
			if (count($updates) == 0)
				return false;

			$query = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `_uuid`='$uuid' LIMIT 1";
			neon()->db->createCommand($query)->bindValues($values)->execute();

			// add or remove any links from the linking table
			$this->setMemberLinks($object->_uuid, $links);

			// finally note the changes in the change log
			if ($class && $class->hasChangeLog())
				$changeLogUuid = $this->createChangeLogEntry($class, 'EDIT', $uuid, $originalValues, $newValues);
		} catch (\yii\db\exception $e) {
			$this->clearCaches();
			return ['error' => $e->getMessage()];
		}
		$this->clearCaches();

		$this->trigger("dds.afterEdit.{$object['_class_type']}", new ObjectEditEvent([
			'classType' => $object['_class_type'],
			'object' => array_merge($changes, $object->toArray()),
			'originalValues' => $originalValues
		]));
		return true;
	}

	/**
	 * NOTE - this method makes the same set of changes across a series of objects
	 * and is not a way of bulk updating objects differently
	 * @inheritdoc
	 */
	public function editObjects($classType, array $uuids, array $changes)
	{
		if (count($uuids) == 0 || count($changes) == 0)
			return false;
		try {
			$uuids = $this->checkUuidsAgainstClassType($uuids, $classType);
			if (count($uuids) == 0)
				return false;
			$table = $this->getTableFromClassType($classType);
			$memberStorage = $this->getMemberStorage($classType);
			if (count($memberStorage) == 0) {
				throw new \InvalidArgumentException('Unknown or empty class type ' . $classType);
			}

			// see if we are recording changes into the change log
			$class = null;
			$this->findClass($classType, $class);
			if ($class && $class->hasChangeLog()) {
				foreach ($uuids as $uuid)
					$beforeValues[$uuid] = array_intersect_key($this->getObject($uuid), $changes);
				$newValues = $changes;
			}

			// make the changes
			$this->convertFromPHPToDB($classType, $changes, $links);
			$count = 0;
			$updates = [];
			$values = [];
			foreach (array_keys($memberStorage) as $memRef) {
				if (array_key_exists($memRef, $changes)) {
					// when saving changes, need to manage empty fields by setting to null
					// otherwise saving what has been provided
					if ($changes[$memRef] === null || $changes[$memRef] === '') {
						$updates[] = "`$memRef`=NULL";
					} else {
						$updates[] = "`$memRef`=:v$count";
						$values[":v$count"] = $changes[$memRef];
						$count++;
					}
				}
			}
			if (count($updates) == 0)
				return false;

			// sort out the uuid clause for both the update on the table and in the object table
			$uuidClause = [];
			$objectValues = [];
			foreach ($uuids as $id) {
				$placeHolder = ":v$count";
				$uuidClause[] = $placeHolder;
				$values[$placeHolder] = $id;
				$objectValues[$placeHolder] = $id;
				$count++;
			}
			$uuidClause = '(' . implode(',', $uuidClause) . ')';

			// update the latest changed
			$db = neon()->db;
			$query = "UPDATE `dds_object` SET `_updated`=NOW() WHERE `_uuid` IN $uuidClause LIMIT " . count($uuids);
			$db->createCommand($query)->bindValues($objectValues)->execute();
			// make the changes
			$query = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `_uuid` IN $uuidClause LIMIT " . count($uuids);
			$db->createCommand($query)->bindValues($values)->execute();

			//
			// do NOT update any member links - that would likely be a programmatic error
			//

			// and save entries into a change log
			if ($class && $class->hasChangeLog()) {
				foreach ($beforeValues as $uuid => $before)
					$this->createChangeLogEntry($class, 'EDIT', $uuid, $before, $newValues);
			}
		} catch (\yii\db\exception $e) {
			$this->clearCaches();
			return ['error' => $e->getMessage()];
		}
		$this->clearCaches();
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteObject($uuid, &$changeLogUuid = null)
	{
		$object = null;
		// find the object and delete if it hasn't already been
		if ($this->getObjectFromId($uuid, $object) && $object->_deleted == 0) {
			$object->_deleted = 1;
			$object->_updated = date('Y-m-d H:i:s');
			if (!$object->save())
				throw new \RuntimeException("Couldn't delete the object: " . print_r($object->errors, true));
			// and update the class object deleted count
			$class = null;
			$this->findClass($object['_class_type'], $class);
			$class->count_deleted += 1;
			$class->save();

			// see if we need to store the change log
			if ($class && $class->hasChangeLog())
				$changeLogUuid = $this->createChangeLogEntry($class, 'DELETE', $uuid);

			/** ---- KEEP ME ----
			 * don't delete the links so any that haven't been deleted between
			 * deletion and undeletion can be recreated. The most likely usecase
			 * here is an accidental delete followed by an immediate undelete.
			 * As far as I can see there is no definitive solution here that covers
			 * all possible usecases, so this is a behavioural choice.
			 * See setMembersLinks for more on this choice.
			 *
			 * Links are deleted when objects are destroyed
			 * ---- KEEP ME ---- */

			$this->clearCaches();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function deleteObjects(array $uuids)
	{
		$objects = $this->getObjectRowsFromIds($uuids);
		if (count($objects) == 0)
			return;

		$foundUuids = [];
		$foundClasses = [];
		$deletedUuids = [];
		foreach ($objects as $obj) {
			if ($obj['_deleted'] == 0) {
				$foundUuids[$obj['_uuid']] = $obj['_uuid'];
				$classType = $obj['_class_type'];
				if (!isset($foundClasses[$classType])) {
					$foundClasses[$classType] = 1;
				} else {
					$foundClasses[$classType] += 1;
				}
				$deletedUuids[$classType][$obj['_uuid']] = $obj['_uuid'];
			}
		}
		if (count($foundUuids))
			DdsObject::updateAll(['_deleted' => 1, '_updated' => date('Y-m-d H:i:s')], ['_uuid' => $foundUuids]);
		foreach ($foundClasses as $type => $count) {
			$class = null;
			if ($this->findClass($type, $class)) {
				$class->count_deleted += $count;
				$class->save();
				// TODO 20200107 Make a bulk call for change log entries
				if ($class->hasChangeLog()) {
					foreach ($deletedUuids[$class->class_type] as $objUuid)
						$this->createChangeLogEntry($class, 'DELETE', $objUuid);
				}
			}
		}
		/** ---- KEEP ME ----
		 * don't delete the links so any that haven't been deleted between
		 * deletion and undeletion can be recreated. The most likely usecase
		 * here is an accidental delete followed by an immediate undelete.
		 * As far as I can see there is no definitive solution here that covers
		 * all possible usecases, so this is a behavioural choice.
		 * See setMembersLinks for more on this choice.
		 *
		 * Links are deleted when objects are destroyed
		 * ---- KEEP ME ---- */
		$this->clearCaches();
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteObject($uuid, &$changeLogUuid = null)
	{
		$object = null;
		// find the object and delete if it hasn't already been
		if ($this->getObjectFromId($uuid, $object) && $object->_deleted == 1) {
			$object->_deleted = 0;
			$object->_updated = date('Y-m-d H:i:s');
			if (!$object->save())
				throw new \RuntimeException("Couldn't undelete the object: " . print_r($object->errors, true));

			// and update the class object deleted count
			$class = null;
			if ($this->findClass($object['_class_type'], $class)) {
				$class->count_deleted = max(0, $class->count_deleted - 1);
				$class->save();

				// see if we need to store the change log
				if ($class->hasChangeLog())
					$changeLogUuid = $this->createChangeLogEntry($class, 'UNDELETE', $uuid);
			}
			$this->clearCaches();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyObject($uuid, &$changeLogUuid = null)
	{
		$object = null;
		if ($this->getObjectFromId($uuid, $object)) {
			$class = null;
			$this->findClass($object['_class_type'], $class);
			if ($class && $class->hasChangeLog())
				$originalValues = $this->getObject($uuid);

			// delete any object table data
			$table = $this->getTableFromClassType($object['_class_type']);
			neon()->db->createCommand()
				->delete($table, ['_uuid' => $uuid])
				->execute();

			// and the object row itself
			neon()->db->createCommand()
				->delete('dds_object', ['_uuid' => $uuid])
				->execute();

			// then if ok, decrement the class's object counts
			if ($class) {
				$class->count_total = max(0, $class->count_total - 1);
				if ($object['_deleted'] == 1)
					$class->count_deleted = max(0, $class->count_deleted - 1);
				$class->save();
			}

			// finally, delete any links from or to the object
			DdsLink::deleteAll(['or', ['from_id' => $uuid], ['to_id' => $uuid]]);

			// and note this is in the change log
			if ($class && $class->hasChangeLog())
				$changeLogUuid = $this->createChangeLogEntry($class, 'DESTROY', $uuid, $originalValues);

			$this->clearCaches();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyObjects(array $uuids)
	{
		$objects = $this->getObjectRowsFromIds($uuids);
		if (count($objects) == 0)
			return;

		$objectUuids = [];
		$objectsDeleted = [];
		$classUuids = [];
		foreach ($objects as $obj) {
			$objectUuids[$obj['_uuid']] = $obj['_uuid'];
			if ($obj['_deleted'] == 1)
				$objectsDeleted[$obj['_class_type']][$obj['_uuid']] = $obj['_uuid'];
			$classUuids[$obj['_class_type']][] = $obj['_uuid'];
		}

		// delete all of the objects from their appropriate class table
		foreach ($classUuids as $classType => $uuids) {
			$class = null;
			$this->findClass($classType, $class);
			$table = $this->getTableFromClassType($classType);

			// note the destroys in the change log
			// TODO 20200107 Make a bulk call for change log entries
			if ($class && $class->hasChangeLog()) {
				foreach ($uuids as $uuid) {
					$data = $this->getObject($uuid);
					$this->createChangeLogEntry($class, 'DESTROY', $uuid, $data);
				}
			}

			// now delete all of the objects
			neon()->db->createCommand()
				->delete($table, ['_uuid' => $uuids])
				->execute();

			// decrement the class's object counts
			if ($class) {
				$class->count_total = max(0, $class->count_total - count($uuids));
				if (isset($objectsDeleted[$classType]))
					$class->count_deleted = max(0, $class->count_deleted - count($objectsDeleted[$classType]));
				$class->save();
			}
		}

		// delete all of the objects from the DdsObject table
		DdsObject::deleteAll(['_uuid' => $objectUuids]);

		// finally, delete any links from or to the objects
		DdsLink::deleteAll(['or', ['from_id' => $objectUuids], ['to_id' => $objectUuids]]);

		$this->clearCaches();
	}

	/**
	 * @inheritdoc
	 */
	public function getObjectCounts($classType)
	{
		$row = DdsClass::find()->where(['class_type'=>$classType])->one()->toArray();
		if ($row) {
			return ['total'=>$row['count_total'], 'active'=>($row['count_total']-$row['count_deleted']), 'deleted'=>$row['count_deleted']];
		}
		return null;
	}


	/**
	 * @inheritdoc
	 */
	public function recalculateObjectCount($classType)
	{
		$query = <<<EOQ
		UPDATE dds_class SET count_total = (SELECT COUNT(*) FROM dds_object WHERE _class_type=:CLASSTYPE1),
		count_deleted = (SELECT COUNT(*) FROM dds_object WHERE _class_type=:CLASSTYPE2 AND _deleted=1) LIMIT 1;
EOQ;
		neon()->db->createCommand($query, ['CLASSTYPE1'=>$classType, 'CLASSTYPE2'=>$classType])->execute();
	}

	/** -------------------------------------- **/
	/** ---------- Internal Methods ---------- **/

	/**
	 * @var \neon\daedalus\services\ddsManager\DdsObjectMapManager|null
	 */
	private $_objectMapManager = null;

	/**
	 * @return \neon\daedalus\services\ddsManager\DdsObjectMapManager|null
	 */
	private function getObjectMapManager()
	{
		if (!$this->_objectMapManager)
			$this->_objectMapManager = new DdsObjectMapManager;
		return $this->_objectMapManager;
	}

	/**
	 * convert a row of data from the database to php
	 * @param [] &$dataSet  [n]=>$rows  rows of requested data
	 * @param string $classTypeKey the key in the data that will give the class type
	 */
	private function convertResultsToPHP(&$dataSet, $links = null, $classTypeKey = '_class_type')
	{
		// if we haven't been provided any multilinks (even if empty array)
		// then see if there are any multilinks to extract
		if ($links === null) {
			$objectIds = [];
			foreach ($dataSet as $row)
				$objectIds[] = $row['_uuid'];
			$links = $this->getMembersLinks($objectIds);
		}
		foreach ($dataSet as &$data) {
			// convert full rows to database. Totals are left untouched
			if (isset($data[$classTypeKey]))
				$this->convertFromDBToPHP($data, $links[$data['_uuid']], $classTypeKey);
		}
	}

	/**
	 * Get the model object from its id
	 * @param string $uuid  the object id
	 * @param DdsObject &$object  the returned object
	 * @return boolean  whether or not object found
	 */
	private function getObjectFromId($uuid, &$object = null)
	{
		$object = DdsObject::find()->noCache()->where(['_uuid' => $uuid])->one();
		return ($object !== null);
	}

	/**
	 * Get the array data for a row from its id
	 * @param array $uuids  an array of object uuids
	 * @return array  the objects found
	 */
	private function getObjectRowsFromIds(array $uuids)
	{
		$uuids = array_unique($uuids);
		$rows = DdsObject::find()->noCache()->where(['_uuid' => $uuids])->asArray()->all();
		return $rows;
	}

	/**
	 * @var array  values bound for the next commit
	 */
	private $boundValues = [];

	/**
	 * @var string  internal name for request keys
	 */
	private $requestKeyField = "___request_key";

	/**
	 * @var string  internal name for result keys
	 */
	private $resultKeyField = "___result_key";

	/**
	 * @var array  store of the last commit
	 */
	private $lastCommitResults = [];

	/**
	 * @var [] the set of object requests
	 */
	private $objectRequests = [];

	/**
	 * Convert and store an object request
	 * @param array $request  the request
	 * @param string $resultKey  if provided use this else create one
	 * @return string  return the resultKey
	 */
	private function createSqlAndStoreRequest(array $request, $resultKey = null)
	{
		// track number of calls to function to generate unique wthin one php request per call and per request result keys
		static $counter = 0;
		$counter++;
		// a randomly generated resultKey prevents db query chaching.
		$resultKey = $resultKey ? $resultKey : $request['requestKey'] . '_' . $counter;
		$request['resultKey'] = $resultKey;
		if (!array_key_exists($request['requestKey'], self::$_requestResultsCache))
			$this->convertRequestToSql($request);
		// store the request
		$this->objectRequests[$request['classType']][$resultKey] = $request;
		return $resultKey;
	}

	/**
	 * clear any object requests
	 */
	private function clearRequests()
	{
		$this->objectRequests = [];
		$this->boundValues = [];
	}

	/**
	 * clear all DdsObjectManager caches
	 */
	private function clearCaches()
	{
		self::$_requestResultsCache = [];
		self::$_linkResultsCache = [];
		self::$_mapResultsCache = [];
		neon()->dds->clearCacheKey();
	}

	/**
	 * Extract all current requests off the queue and pass back
	 * @return array ['requests', 'boundValues']
	 */
	private function popRequests()
	{
		$currentRequests = $this->getObjectRequests();
		$this->clearRequests();
		return $currentRequests;
	}

	/**
	 * Replace all requests passed on the queue with those provided
	 * - use after popObjectRequests
	 * @param array $requests  an array of 'requests' and their
	 *   corresponding 'boundValues'
	 */
	private function pushRequests($requests)
	{
		$this->objectRequests = $requests['requests'];
		$this->boundValues = $requests['boundValues'];
	}

	/**
	 * convert a request into its SQL query for use on commit and store
	 * @param array $request
	 * @return string
	 */
	private function convertRequestToSql(&$request)
	{
		$includeDeleted = $request['deleted'];
		$onlyDeleted = $this->onlyWantDeleted($includeDeleted, $request['filters']);
		$classType = $request['classType'];
		$table = $this->getTableFromClassType($classType);
		$links = array_keys($this->getClassMembers($classType, ['link_multi', 'file_ref_multi']));
		$where = $this->extractWhereClause($request['filters'], $includeDeleted, $links, $filterKeys);
		$join  = $this->extractJoinClause($links, $filterKeys);
		$order = $this->extractOrderClause($request['order']);
		$limit = $this->extractLimitClause($request['limit']);
		$request['sql'] = "SELECT o.*, t.*, '$request[requestKey]' as `{$this->requestKeyField}`, '$request[resultKey]' as `{$this->resultKeyField}` FROM $table t $join $where $order $limit";

		// try and find an efficient way of doing the really expensive count. This is a problem when
		// we are trying to count a very large number of items and not filtering at all.
		// Basically, if there are no filters, no links or we are only after deleted numbers
		// then we can use the full class counts rather than counting on the queries directly.
		$request['totalSql'] = null;
		$useSimpleCount = ((empty($request['filters']) || $onlyDeleted) && empty($links));
		if ($request['calculateTotal']) {
			if ($useSimpleCount) {
				if ($onlyDeleted) {
					$request['totalSql'] = "SELECT `count_deleted` as `total`";
				} else if ($includeDeleted) {
					$request['totalSql'] = "SELECT `count_total` as `total`";
				} else {
					$request['totalSql'] = "SELECT (`count_total`-`count_deleted`) as `total`";
				}
				$request['totalSql'] .= ", '$request[requestKey]' as `{$this->requestKeyField}`, '$request[resultKey]_total' as `{$this->resultKeyField}` FROM `dds_class` WHERE `class_type` = '$classType'";
			} else {
				$request['totalSql'] =  "SELECT COUNT(*) as `total`, '$request[requestKey]' as `{$this->requestKeyField}`, '$request[resultKey]_total' as `{$this->resultKeyField}` FROM $table t $join $where";
			}
		}
	}

	/**
	 * See if only deleted objects are required
	 * @return boolean
	 */
	private function onlyWantDeleted($includeDeleted, $filters)
	{
		return $includeDeleted && (count($filters) == 1) && isset($filters[0][0]) && $filters[0][0] = '_deleted' && isset($filters[0][2]) && $filters[0][2] == 1;
	}

	/**
	 * extract the join clause from the filters provided
	 * @param array $links  the set of link fields in the table
	 * @param array $filterKeys  the set of fields used in the filtering
	 * @return string  the join clause
	 */
	private function extractJoinClause($links, $filterKeys)
	{
		// we're always joining the object table to the main table
		$join = ['INNER JOIN dds_object `o` on `t`.`_uuid` = `o`.`_uuid`'];
		// now see what else needs to be added
		foreach ($links as $l) {
			if (in_array($l, $filterKeys))
				$join[] = "LEFT JOIN dds_link `link_$l` ON (`link_$l`.`from_id`=`o`.`_uuid` AND `link_$l`.`from_member`='$l')";
		}
		return implode(' ', $join);
	}

	/**
	 * extract the where clause from the filters provided
	 * @param array $filters
	 * @param boolean $includeDeleted  true if including deleted
	 * @param array $links  any filter keys that are links
	 * @param array &$filterKeys  the set of keys used as filters for use elsewhere
	 * @return string
	 */
	private function extractWhereClause($filters, $includeDeleted, $links, &$filterKeys)
	{
		$filterKeys = [];
		$notDeleted = '`o`.`_deleted`=0';
		if (count($filters) == 0)
			return $includeDeleted ? '' : "WHERE $notDeleted";
		$this->createFilterClause($filters, $links, $filterKeys);
		if (!$includeDeleted)
			return "WHERE $notDeleted AND ($filters[sql])";
		return "WHERE $filters[sql]";
	}

	/**
	 * recursively go through the filters and generate the sql filter
	 * clause that's used for the where clause
	 * @param array &$filters   the set of filters that are to be
	 *   traversed to generate the overall where clause
	 * @param array $links  the set of link members that aren't stored in this
	 *   table
	 * @param array &$filterKeys  the set of members that are being filtered on
	 */
	private function createFilterClause(&$filters, $links, &$filterKeys)
	{
		// filter down until we hit the actual filters
		if (is_array($filters[0])) {
			foreach ($filters as $k => &$filter) {
				if ($k === 'logic')
					continue;
				$this->createFilterClause($filter, $links, $filterKeys);
			}

			//
			// to get here we're bubbling back up the recursion again
			//
			if (isset($filters[0]['itemSql'])) {
				// ok, so now we should have itemSql's defined
				// in which case we combine those with to get the filterSql
				$filterSql = [];
				foreach ($filters as $k => &$filter) {
					if ($k === 'logic')
						continue;
					// use the logic keys if any for the items - these can be in position 3
					// for most operators and position 2 for operators that don't take a key
					if ($this->operatorTakesObject($filter[1])) {
						if (isset($filter[3]))
							$filterSql[$filter[3]] = $filter['itemSql'];
						else
							$filterSql[] = $filter['itemSql'];
					} else {
						if (isset($filter[2]))
							$filterSql[$filter[2]] = $filter['itemSql'];
						else
							$filterSql[] = $filter['itemSql'];
					}
				}
				if (empty($filters['logic']))
					$filters['filterSql'] = $filters['sql'] = implode(' AND ', $filterSql);
				else {
					// make sure we test logic filters in order from longest key to shortest key
					// otherwise we can end up with subkeys screwing things up
					$orderedKeys = array_map('strlen', array_keys($filterSql));
					array_multisort($orderedKeys, SORT_DESC, $filterSql);
					$this->checkLogic(array_keys($filterSql), $filters['logic']);

					// make sure that keys that are subsets of variables or other keys
					// don't interfere by converting all to their md5 hash and doing a
					// double conversion,
					$keys = array_keys($filterSql);
					$keys2hashed = array();
					foreach ($keys as $k)
						$keys2hashed[$k] = md5($k);
					$logicPass1 = str_replace($keys, $keys2hashed, $filters['logic']);
					$filters['filterSql'] = $filters['sql'] = str_replace($keys2hashed, array_values($filterSql), $logicPass1);
				}
			} else {
				// or we have filterSql's defined in which case combine these
				// to get the complete set of filters
				$clauseSql = [];
				foreach ($filters as &$filter) {
					$clauseSql[] = $filter['filterSql'];
					unset($filter['sql']);
				}
				$filters['sql'] = '(' . implode(') OR (', $clauseSql) . ')';
			}
			return;
		}
		//
		// To get here we're at the leaf of the recursion tree
		// Start creating the sql. $filters[0,1] will have been canonicalised, $filters[2] is protected using PDO
		// Not all queries have a [2] though so should be just the operator (e.g. IS NULL)
		//
		$filter = (in_array($filters[0], $links) ? "`link_{$filters[0]}`.`to_id`" : $this->quoteField($filters[0]));
		$filterKeys[$filters[0]] = $filters[0];
		if ($this->operatorTakesObject($filters[1]) && isset($filters[2]))
			$filters['itemSql'] = $filters['sql'] = "$filter " . $this->prepareForPDO($filters[1], $filters[2]);
		else {
			$filters['itemSql'] = $filters['sql'] = "$filter $filters[1]";
		}
	}

	private function prepareForPDO($operator, $value)
	{
		if (is_array($value)) {
			$pdoValues = [];
			if (!in_array($operator, ['=', '!=', 'IN', 'NOT IN']))
				throw new \InvalidArgumentException("Daedalus: Cannot pass an *array* of values with an operator of $operator");
			foreach ($value as $v) {
				if (is_array($v))
					throw new \InvalidArgumentException("Daedalus: Cannot pass an *array* of *array* of values as filters (silly billy). You passed " . print_r($value, true));
				$count = count($this->boundValues);
				$variable = ":var{$count}end";
				$this->boundValues[$variable] = $v;
				$pdoValues[] = $variable;
			}
			// converts equals to ins and outs
			if ($operator == '=')
				$operator = 'IN';
			if ($operator == '!=')
				$operator = 'NOT IN';
			return "$operator (" . implode(',', $pdoValues) . ')';
		} else {
			$count = count($this->boundValues);
			$variable = ":var{$count}end";
			if (strpos(strtolower($operator), 'like') !== false) {
				$this->boundValues[$variable] = "%$value%";
				return "$operator $variable";
			} else if (strpos(strtolower($operator), 'null') !== false) {
				return "$operator";
			} else {
				$this->boundValues[$variable] = $value;
				return "$operator $variable";
			}
		}
	}

	/**
	 * Extract an order clause from the canonicalised order array
	 * @param array $order
	 * @return string
	 */
	private function extractOrderClause($order)
	{
		if (count($order) == 0)
			return '';
		$clause = [];
		foreach ($order as $o => $d) {
			// if any of the order clauses are RAND then replace whole clause
			if ($o === 'RAND') {
				$clause = ["RAND()"];
				break;
			}
			// allow negative ordering in mysql for nulls last
			if (strpos($o, '-') === 0)
				$clause[] = '-' . substr($o, 1) . " $d";
			else
				$clause[] = "$o $d";
		}
		return 'ORDER BY ' . implode(', ', $clause);
	}

	/**
	 * Extract a limit clause from the canonicalised limit array
	 * @param array $limit
	 * @return string
	 */
	private function extractLimitClause($limit)
	{
		if (count($limit) == 0)
			return '';
		return "LIMIT $limit[start], $limit[length]";
	}

	/**
	 * Set member links in bulk into the links table
	 * @param array $objectsLinks  an array of $fromUuid => $toMemberLinks
	 *   where uuid is the from uuid and $toMemberLinks is an array of
	 */
	private function setMembersLinks($objectsLinks, $chunkSize)
	{
		if (count($objectsLinks) === 0)
			return 0;
		$totalCount = 0;
		$db = neon()->db;
		$ddsLink = DdsLink::tableName();
		foreach (array_chunk($objectsLinks, $chunkSize, true) as $chunkData) {
			$count = 0;
			$batchInsert = [];
			$deleteLinkClauses = [];
			foreach ($chunkData as $fromUuid => $toMemberLinks) {
				// check you've been supplied with rinky dinky data
				if (!$this->isUUID($fromUuid))
					throw new \InvalidArgumentException("The fromUuid should be a UUID64. You passed in $fromUuid");
				foreach ($toMemberLinks as $member => $toLinks) {
					if (!is_array($toLinks))
						$toLinks = [$toLinks];
					// remove any empty toLinks that might have snuck through
					$toLinks = array_filter($toLinks);
					if (!$this->areUUIDs($toLinks))
						throw new \InvalidArgumentException("The toMemberLinks should be UUID64s. You passed in " . print_r($toLinks, true));
					$toLinks = array_unique($toLinks);

					//
					// set up any existing links from from_id:member to be deleted
					//
					// There is an ambiguity here with what to do about soft-deleted objects that
					// are linked. You could leave these here, but then if the other object
					// was undeleted, then would you expect or be surprised if those links reappeared?
					// I don't think there is any correct policy here so choosing to delete the links
					//
					$deleteLinkClauses[] = "(`from_id`='$fromUuid' AND `from_member`='$member')";

					// prepare the new links ready for bulk insert
					$count += count($toLinks);
					foreach ($toLinks as $toLink)
						$batchInsert[] = ['from_id' => $fromUuid, 'from_member' => $member, 'to_id' => $toLink];
				}
			}
			if (count($deleteLinkClauses)) {
				$deleteClause = implode(' OR ', $deleteLinkClauses);
				$db->createCommand()->delete($ddsLink, $deleteClause)->execute();
			}

			$insertedCount = 0;
			if (count($batchInsert))
				$insertedCount = $db->createCommand()->batchInsert($ddsLink, ['from_id', 'from_member', 'to_id'], $batchInsert)->execute();
			if (YII_DEBUG && $insertedCount !== $count)
				throw new \Exception("The link insertion failed - $count items should have been inserted vs $insertedCount actual");
			$totalCount += $count;
		}
		return $totalCount;
	}

	/**
	 * Insert a series of links from object a to set of objects b
	 *
	 * @param string $fromUuid - a UUID64
	 * @param [member_ref][string] $toMemberLinks - an array of UUID64s for each
	 *   member_ref keys are set for (so we can distinguish between not set for
	 *   updates (=> do nothing) and set but blank (=> delete)
	 * @return integer - the number of inserted links
	 */
	private function setMemberLinks($fromUuid, array $toMemberLinks)
	{
		return $this->setMembersLinks([$fromUuid=>$toMemberLinks], 1);
	}

	/**
	 * Get all member links associated with this object
	 *
	 * @param uuid $uuid
	 * @return array  an array of all object ids linked against
	 *   a member of this object e.g. ['author_id']=>[ blogIds ]
	 */
	private function getMemberLinks($uuid)
	{
		$membersLinks = $this->getMembersLinks([$uuid]);
		return $membersLinks[$uuid];
	}

	/**
	 * Get the membersLinks associated with particular fields on objects
	 * @param array $objectIds  - the set of objects you want the fields for
	 * @return array  an array of [objectId]=>[member_ref=>links]. All objects
	 *   passed in have an array returned even if that is an empty array
	 */
	protected function getMembersLinks(array $objectIds)
	{
		if (empty($objectIds))
			return [];
		$objectIds = array_unique($objectIds);
		sort($objectIds, SORT_STRING);
		$cacheKey = md5(serialize($objectIds));
		if (!array_key_exists($cacheKey, self::$_linkResultsCache)) {
			$rows = DdsLink::find()->noCache()->select(['from_id', 'from_member', 'to_id', '_deleted'])
				->join('LEFT JOIN', '{{%dds_object}}', 'to_id=_uuid')
				->where('`_deleted` IS NULL OR `_deleted` = 0')
				->andWhere(['from_id' => $objectIds])
				->asArray()
				->all();
			$multilinks = array_fill_keys($objectIds, []);
			foreach ($rows as $row)
				$multilinks[$row['from_id']][$row['from_member']][] = $row['to_id'];
			self::$_linkResultsCache[$cacheKey] = $multilinks;
		}
		return self::$_linkResultsCache[$cacheKey];
	}

	/**
	 * Send a change log entry to the change log manager
	 * @param DdsClass $class  the DdsClass Object
	 * @param string $changeKey  the change key for the action performed e.g. EDIT
	 * @param string $objectUuid  the object uuid
	 * @param array $changes  what the changes are
	 * @return string  the uuid of the log entry
	 */
	private function createChangeLogEntry($class, $changeKey, $objectUuid, $originalValues = [], $newValues = [])
	{
		static $changeLog = null;
		if (!$changeLog)
			$changeLog = neon('dds')->IDdsChangeLogManagement;
		return $changeLog->addLogEntry($objectUuid, $class->toArray(), $changeKey, $originalValues, $newValues);
	}

	/**
	 * Check that the uuids provided are all of the given class type.
	 * Remove any that aren't.
	 * @param array $uuids  the set of uuids
	 * @param string $classType  the class type they should all belong to
	 * @return array  the set of uuids that all belong to this class type
	 */
	private function checkUuidsAgainstClassType($uuids, $classType)
	{
		// remove any objects that aren't of the same type as the class type
		$db = neon()->db;
		$uuidClause = [];
		$objectValues = [];
		$count = 0;
		foreach ($uuids as $id) {
			$placeHolder = ":v$count";
			$uuidClause[] = $placeHolder;
			$objectValues[$placeHolder] = $id;
			$count++;
		}
		$uuidClause = '(' . implode(',', $uuidClause) . ')';
		$query = "SELECT [[_uuid]] FROM [[dds_object]] WHERE [[_class_type]]='$classType' AND [[_uuid]] IN $uuidClause";
		$checkedUuidResults = $db->createCommand($query)->bindValues($objectValues)->queryAll();
		return array_column($checkedUuidResults, '_uuid');
	}
}
