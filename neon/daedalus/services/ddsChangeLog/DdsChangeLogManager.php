<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsChangeLog;

use neon\daedalus\interfaces\IDdsChangeLogManagement;
use neon\daedalus\services\ddsManager\DdsCore;
use neon\daedalus\services\ddsManager\models\DdsClass;
use neon\core\helpers\Hash;
use neon\core\helpers\Iterator;


class DdsChangeLogManager extends DdsCore
implements IDdsChangeLogManagement
{
	/**
	 * The allowed change log actions. This is a subset of allowed
	 * change log entries, being those ones that resulted in a change in
	 * an object. So this doesn't include comments for example
	 * @var array
	 */
	private $changeLogEntryActions = [
		'ADD', 'EDIT', 'DELETE', 'UNDELETE', 'DESTROY', 'RESTORE'
	];

	// ---------- IDdsChangeLogManagement methods ---------- //

	/**
	 * @inheritdoc
	 */
	public function listClassesWithChangeLog()
	{
		return DdsClass::find()
			->where(['change_log'=>1, 'deleted'=>0])
			->asArray()
			->all();
	}

	/**
	 * @inheritdoc
	 */
	public function hasChangeLog($classType)
	{
		if ($this->findClass($classType, $class)) {
			return (boolean)$class->hasChangeLog();
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function setChangeLog($classType, $to)
	{
		$to = (boolean)$to;
		if ($this->findClass($classType, $class)) {
			$hasChangeLog = (boolean)$class->hasChangeLog();
			// only change if different to current
			if ($hasChangeLog !== $to) {
				neon('dds')->IDdsClassManagement->editClass($classType, ['change_log'=>$to]);

				// create a comment to note the change log setting
				$whoUid = $name = null;
				$this->whoDunnit($whoUid, $name);
				$date = date('D, jS M Y \a\t H:i:s');
				$description = "Change log for $classType was turned %s by $name on $date";
				neon()->db->createCommand()->insert('{{dds_change_log}}', [
					'log_uuid' => Hash::uuid64(),
					'class_type' => $class['class_type'],
					'change_key' => 'COMMENT',
					'description' => sprintf($description, ($to?'ON':'OFF')),
					'who' => $whoUid,
					'when' => date('Y-m-d H:i:s'),
				])->execute();
			}
			$this->clearClassCache($classType);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function listChangeLog($fromDate=null, Iterator $iterator=null)
	{
		return $this->getChangeLog($fromDate, $iterator, null);
	}

	/**
	 * @inheritdoc
	 */
	public function listObjectChangeLog($uuid, $fromDate=null, Iterator $iterator=null)
	{
		return $this->getChangeLog($fromDate, $iterator, $uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function listObjectHistory($uuid, $fromDate=null, Iterator $iterator=null)
	{
		$changeLog = $this->getChangeLog($fromDate, $iterator, $uuid);
		return $this->convertChangeLogToHistory($uuid, $changeLog);
	}

	/**
	 * @inheritdoc
	 */
	public function clearChangeLog($toDate, $classList=[], $clearClassList=true)
	{
		$deleteConditions = '[[when]] <= :toDate';
		$bindValues = [ ':toDate'=>$toDate ];
		if (!empty($classList)) {
			$clearClause = ($clearClassList ? '' : '!');
			$deleteConditions .= " AND $clearClause([[class_type]]=:".implode(" OR [[class_type]]=:", $classList).")";
			foreach ($classList as $class) {
				$bindValues[":$class"] = $class;
			}
		}
		neon()->db->createCommand()
			->delete('{{dds_change_log}}', $deleteConditions)
			->bindValues($bindValues)
			->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function clearObjectChangeLog($uuid, $toDate)
	{
		$deleteConditions = '[[when]] <= :toDate AND [[object_uuid]] = :uuid';
		$bindValues = [ ':toDate'=>$toDate, ':uuid' => $uuid ];
		neon()->db->createCommand()
			->delete('{{dds_change_log}}', $deleteConditions)
			->bindValues($bindValues)
			->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function addLogEntry($objectUuid, $class, $changeKey, $before=[], $after=[])
	{
		if (!in_array($changeKey, $this->changeLogEntryActions))
			throw new \InvalidArgumentException('Invalid change key in addLogEntry. Allowed values are ['.implode(', ', $this->changeLogEntryActions));

		$class = $this->getClass($class);

		// check the class does have a change log
		if (!$class['change_log'])
			return;

		// get who this was
		$whoUid = $name = null;
		$this->whoDunnit($whoUid, $name);

		// create a generic user friendly message for the changes made
		$description = '';
		$date = date('D, jS M Y \a\t H:i:s');
		$baseDescription = "$name %s item of type '$class[label] ($class[class_type])' on $date. The item id is '$objectUuid'.";
		switch ($changeKey) {
			case 'ADD':
				$description = sprintf($baseDescription, 'added a new');
			break;
			case 'EDIT':
				$changeCount = $this->calculateMinimumFieldsChangedDuringEdit($before, $after);
				if ($changeCount == 0)
					return;
				$description = sprintf($baseDescription, 'edited an')
					." The fields changed were ['".implode("', '", array_keys($after))."'].";
			break;
			case 'DELETE':
				$description = sprintf($baseDescription, 'soft deleted an')
					." The item can be restored using an undelete within Daedalus.";
			break;
			case 'UNDELETE':
				$description = sprintf($baseDescription, 'undeleted an')
					." The item has been undeleted.";
			break;
			case 'DESTROY':
				$description = sprintf($baseDescription, 'destroyed an')
					." The item can only be recovered from the change log or database backups.";
			break;
			case 'RESTORE':
				$description = sprintf($baseDescription, 'restored an')
					." The item was restored to its value at ".$this->restorePoint;
			break;
		}

		$logUuid = Hash::uuid64();

		neon()->db->createCommand()->insert('{{dds_change_log}}', [
			'log_uuid' => $logUuid,
			'object_uuid' => $objectUuid,
			'class_type' => $class['class_type'],
			'change_key' => $changeKey,
			'description' => $description,
			'who' => $whoUid,
			'when' => date('Y-m-d H:i:s'),
			'before' => json_encode($before),
			'after' => json_encode($after)
		])->execute();

		return $logUuid;
	}

	public function getLogEntry($logUuid)
	{
		if ($logUuid) {
			$entry = neon()->db->createCommand("SELECT * FROM {{dds_change_log}} WHERE [[log_uuid]] = :logUuid")
				->bindParam(":logUuid", $logUuid)
				->queryOne();
			if ($entry)
				$this->convertFromDb($entry);
			return $entry;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function addComment($objectUuid, $comment)
	{
		// check this has a change log
		$class = $this->getClassFromObject($objectUuid);
		if (empty($class) || !$class['change_log'])
			return;

		// find out who did this
		$whoUid = $name = null;
		$this->whoDunnit($whoUid, $name);
		$logUuid = Hash::uuid64();
		neon()->db->createCommand()->insert('{{dds_change_log}}', [
			'log_uuid' => $logUuid,
			'object_uuid' => $objectUuid,
			'class_type'=>$class['class_type'],
			'change_key' => 'COMMENT',
			'description' => $comment,
			'who' => $whoUid,
			'when' => date('Y-m-d H:i:s')
		])->execute();
		return $logUuid;
	}

	/**
	 * @inheritdoc
	 */
	public function addGeneralComment($module, $classType, $comment, $objectUuid=null, array $associateObjectUuids=[])
	{
		// check we can make a comment
		if (empty($comment))
			return;

		// find out who did this
		$whoUid = $name = null;
		$this->whoDunnit($whoUid, $name);
		$logUuid = Hash::uuid64();
		neon()->db->createCommand()->insert('{{dds_change_log}}', [
			'log_uuid' => $logUuid,
			'module' => $module,
			'class_type'=>$classType,
			'object_uuid' => ($objectUuid ? $objectUuid : ''),
			'associated_objects' => json_encode($associateObjectUuids),
			'change_key' => 'COMMENT',
			'description' => $comment,
			'who' => $whoUid,
			'when' => date('Y-m-d H:i:s')
		])->execute();
		return $logUuid;
	}

	/**
	 * @inheritdoc
	 */
	public function getObjectAtLogPoint($objectUuid, $logEntryUuid)
	{
		$initialObject = $restorePoint = null;
		$this->calculateObjectAtRestorePoint($objectUuid, $logEntryUuid, $initialObject, $restorePoint);
		if ($restorePoint)
			return $restorePoint['object'];
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function restoreObjectToLogPoint($objectUuid, $logEntryUuid)
	{
		$this->calculateObjectAtRestorePoint($objectUuid, $logEntryUuid, $initialObject, $restorePoint);
		$restoreObject = $restorePoint['object'];

		$dds = neon('dds')->IDdsObjectManagement;

		// so how to restore
		$isInitialEmpty = empty($initialObject);
		$isRestoreEmpty = empty($restoreObject);

		if ($isInitialEmpty && $isRestoreEmpty) {
			// here there is nothing to restore to and it's already gone
			return;
		} else if ($isInitialEmpty && !$isRestoreEmpty) {
			// here object was destroyed and needs re-adding
			$dds->addObject($restorePoint['class_type'], $restoreObject);
		} else if (!$isInitialEmpty && $isRestoreEmpty) {
			// here object exists but needs to become empty so is destroyed
			$dds->destroyObject($objectUuid);
		} else {
			// here we can return the object to the restore point and then
			// make sure it is undeleted (assuming this is what restoring requires)
			$dds->undeleteObject($objectUuid);
			$dds->editObject($objectUuid, $restoreObject);
		}
		// and make a log entry
		if (!empty($restorePoint))
			$this->addLogEntry($objectUuid, $restorePoint['class_type'], 'RESTORE', $initialObject, $restorePoint);
	}


	// ---------- Private Parameters and Methods ---------- //

	private $restorePoint = null;

	private function whoDunnit(&$who, &$name)
	{
		$who = null;
		$name = 'The system';
		// if we're a web instance we can have users
		if (neon() instanceof \yii\web\Application) {
			$user = neon()->user;
			$who = $user->uuid;
			$name = $user->name;
		}
	}

	/**
	 * Get the change log given a set of criteria
	 * @param date $fromDate  when to start the change log from
	 * @param Iterator $iterator  the iterator over the change log
	 *   This ignored if you are getting from a particular log id
	 * @param uuid $objectUuid  the object you want the change log for
	 * @param uuid $logId  if set then get all entries from this point up
	 *   This overrides any restrictions on the iterator
	 * @return array  the change log
	 * @throws \RuntimeException  if the restore point could not be reached
	 *   using a sensible number of change log points (<=1000).
	 */
	private function getChangeLog($fromDate, $iterator, $objectUuid, $logId=null)
	{
		$fromDate = ($fromDate === null ? date('Y-m-d 00:00:00') : $fromDate);
		$iterator = ($iterator === null ? new Iterator : $iterator);

		// set up the basic query
		$query = (new \yii\db\Query())
			->from('dds_change_log')
			->where(['>=', 'when', $fromDate])
			->orderBy(['log_id'=>SORT_DESC]);

		// check for a particular object if required
		if ($objectUuid)
			$query->andWhere(['object_uuid'=>$objectUuid]);

		// start from a particular log id point
		if ($logId) {
			$query->andWhere(['>=', 'log_id', $logId]);
			$iterator->start = 0;
			$iterator->length = Iterator::MAX_LENGTH;
		}

		// and check sensible limits
		$query->limit($iterator->length)
			->offset($iterator->start);
		if ($iterator->shouldReturnTotal())
			$iterator->total = $query->count();

		$changeLog = $query->all();

		// ensure we got the logId if we were looking for one
		if ($logId) {
			$last = end($changeLog);
			if ($last['log_id'] != $logId) {
				throw new \RuntimeException("Log restore point could not be reached from the change log.");
			}
		}

		foreach ($changeLog as &$entry) {
			$this->convertFromDb($entry);
		}
		return $changeLog;
	}

	/**
	 *
	 * @param type $entry
	 */
	private function convertFromDb(&$entry)
	{
		$entry['before'] = json_decode($entry['before'],true);
		$entry['after'] = json_decode($entry['after'],true);
		$entry['associated_objects'] = json_decode($entry['associated_objects'] ?? '',true);
	}

	/**
	 * Convert a change log into a history of the state of the object at
	 * various points in time after each action was performed on it.
	 *
	 * @param string $uuid
	 * @param array $log
	 * @param array &$currentObject  the value of the current object
	 * @return array  a history log
	 */
	private function convertChangeLogToHistory($uuid, $log, &$currentObject=[])
	{
		// get the current object and work backwards from there to get history
		// first see if we can the object and if not check that the last action
		// was a destroy. If not then there is something wrong with the logs
		$currentObject = neon('dds')->IDdsObjectManagement->getObject($uuid);
		$lastAction = $this->getLastActionFromLog($log);
		if ($currentObject == null) {
			$currentObject = [];
			if (!($lastAction == 'DESTROY' || $lastAction == 'RESTORE'))
				return [];
		}
		$object = $currentObject;
		$objectHistory = [];

		// ok so it should be possible to restore backwards from here
		foreach ($log as $l) {
			// set the object into an appropriate state.
			$objectHistory[] = [
				'when' => $l['when'],
				'change_key' => $l['change_key'],
				'description' => $l['description'],
				'class_type' => $l['class_type'],
				'object' => $object
			];
			if (is_array($l['before'])) {
				switch ($l['change_key']) {
					case 'RESTORE': $object = $l['before']; break;
					case 'ADD': $object = []; break;
					default: $object = array_merge($object, $l['before']); break;
				}
			}
		}
		return $objectHistory;
	}

	/**
	 * Go back through a log and find the last action. An action is one of
	 * ADD, EDIT, DELETE, UNDELETE, DESTROY, RESTORE
	 * @return string  the last valid action
	 */
	private function getLastActionFromLog($log)
	{
		foreach ($log as $l) {
			if (!in_array($l['change_key'], $this->changeLogEntryActions))
				continue;
			return $l['change_key'];
		}
		return null;
	}

	/**
	 * Get the class of an object
	 *
	 * @param uuid $objectUuid  the uuid of the object you want the class for
	 * @return string  the class type
	 *
	 * @staticvar array $objectClasses  stores previously found results
	 */
	private function getClassFromObject($objectUuid)
	{
		static $objectClasses = [];
		if (!isset($objectClasses[$objectUuid])) {
			$object = neon()->db->createCommand(
				"SELECT [[_class_type]] FROM {{dds_object}} WHERE [[_uuid]] = :objUuid"
			)->bindValue(':objUuid', $objectUuid)->queryOne();
			$objectClasses[$objectUuid] = $object ? $object['_class_type'] : '';
		}
		return $this->getClass($objectClasses[$objectUuid]);
	}

	/**
	 * Get hold of the class for a given class type
	 *
	 * @param array|string $class  if an array this is passed back. If string, class is
	 *   looked up and then returned.
	 * @return array
	 * @throws \InvalidArgumentException  if the class is neither an array or a string
	 *
	 * @staticvar array $classes  stores previously found classes
	 * @staticvar neon\daedalus\interfaces\IDdsClassManagement $ddc  stores reference to IDdsClassManagement
	 */
	private function getClass($class)
	{
		static $classes = [];
		static $ddc = null;

		// see if this is already an array in which case no need to look up
		if (is_array($class))
			return $class;

		// otherwise check this is a string for a class type
		if (!is_string($class))
			throw new \InvalidArgumentException("Invalid type of class passed in. Should be either a string or an array. Type passed in was ".gettype($class));

		// and get hold of the class
		if (!isset($classes[$class])) {
			if (empty($ddc))
				$ddc = neon('dds')->IDdsClassManagement;
			$classes[$class] = $ddc->getClass($class);
		}
		return $classes[$class];
	}

	/**
	 * Calculate the minimum number of changed fields made during an edit
	 */

	/**
	 * Calculate the minimum number of fields actually changed during an edit
	 *
	 * @param array &$before  the fields passed in as before the edit. Only actually changed fields
	 *   remain after the call. Unchanged fields are removed.
	 * @param array $after  the fields passed in as after the edit. Only actually changed fields
	 *   remain after the call. Unchanged fields are removed.
	 * @return boolean  true if there are any actual changes
	 */
	private function calculateMinimumFieldsChangedDuringEdit(&$before, &$after)
	{
		$beforeChangedFields = [];
		$afterChangedFields = [];
		foreach ($after as $k=>$v) {
			if (array_key_exists($k, $before)) {
				$b = $before[$k];
				// check for a few expected changes that aren't actual changes
				if ($this->isEquivalentNull($b, $v) || $this->isEquivalentBoolean($b, $v) || $this->isEquivalentChoice($b, $v))
					continue;
				if ($v !== $before[$k]) {
					$beforeChangedFields[$k] = $b;
					$afterChangedFields[$k] = $v;
				}
			} else {
				$afterChangedFields = $v;
			}
		}
		$before = $beforeChangedFields;
		$after = $afterChangedFields;
		return count($after)>0;
	}

	/**
	 * Calculate the object value at both the current and restore points
	 * @param string $objectUuid  the object you want
	 * @param string $logEntryUuid  the log point you want to check to
	 * @param array $initialObject  the current object data
	 * @param array $restorePoint  the final object data
	 * @throws \InvalidArgumentException
	 */
	private function calculateObjectAtRestorePoint($objectUuid, $logEntryUuid, &$initialObject, &$restorePoint)
	{
		// find the log entry point and then all entries up to and equal that one
		$restorePoint = neon()->db->createCommand("SELECT * FROM dds_change_log WHERE log_uuid = :log_uuid")
			->bindValue(':log_uuid', $logEntryUuid)
			->queryOne();
		if (!$restorePoint)
			throw new \InvalidArgumentException("The requested log point ($logEntryUuid) doesn't exist.");
		if ($objectUuid != $restorePoint['object_uuid'])
			throw new \InvalidArgumentException("The requested object ($objectUuid) and the object at the log point ($logEntryUuid) don't match");

		$initialObject = null;
		$changeLog = $this->getChangeLog($restorePoint['when'], null, $objectUuid, $restorePoint['log_id']);
		$objectHistory = $this->convertChangeLogToHistory($objectUuid, $changeLog, $initialObject);
		$restorePoint = empty($objectHistory) ? [] : end($objectHistory);
	}

	/**
	 * Check if the before and after are actually the same choice. A
	 * choice is the same if the keys match.
	 *
	 * @param mixed $before
	 * @param mixed $after
	 * @return boolean  returns true if the before is an array of key, value, type,
	 *   the after is a string, the type is choice and the keys are equal
	 */
	private function isEquivalentChoice($before, $after)
	{
		return (
			is_array($before)
			&& array_key_exists('key', $before)
			&& array_key_exists('value', $before)
			&& array_key_exists('type', $before)
			&& $before['type'] == 'choice'
			&& $before['key'] == $after
		);
	}

	/**
	 * Check if the before and after values are equivalent booleans
	 *
	 * @param mixed $before
	 * @param mixed $after
	 * @return boolean  returns true if the before and after are both
	 *   boolean equivalents - e.g. before is true and after is 1
	 */
	private function isEquivalentBoolean($before, $after)
	{
		return (is_bool($before) && (bool)($after) == $before);
	}

	/**
	 * Check if the before and after values are equivalent nulls
	 *
	 * @param mixed $before
	 * @param mixed $after
	 * @return boolean  returns true if the before and after are both
	 *   null equivalents - i.e. null, empty string or empty array.
	 */
	private function isEquivalentNull($before, $after)
	{
		if ($before === null || $before === '' || (is_array($before) && count($before)==0))
			return ($after === null || $after === '' || (is_array($after) && count($after)==0));
		return false;
	}

}
