<?php

namespace neon\tests\unit\daedalus;

use neon\tests\unit\daedalus\DdsBaseTestUnit;
use neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use neon\daedalus\services\ddsManager\DdsObjectManager;
use neon\core\helpers\Hash;
use neon\core\helpers\Iterator;
use Yii;
/**
 * Class ChangeLogManagementTest
 * @package neon\tests\unit\daedalus
 *
 * @property DdsDataDefinitionManager $dds
 * @property DdsObjectManager $ddo
 */
class ChangeLogManagementDdsIntegratedTest extends DdsBaseTestUnit
{
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
	}
	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();
	}

	public function _setUp()
	{
		$this->dds = new DdsDataDefinitionManager();
		$this->ddo = new DdsObjectManager;

		// create a basic class type
		$this->dataTypeRef = $this->randomString(10);
		$this->dds->addDataType($this->dataTypeRef, "Short Text", "Short Text", '', "textshort");
		$this->classType = $this->randomString();
		$this->dds->addClass($this->classType, $this->randomString());
		$this->dds->editClass($this->classType, ['label'=>'Test Class']);
		$this->memberRef1 = $this->randomString(10);
		$this->memberRef2 = $this->randomString(10);
		$this->memberId1 = $this->dds->addMember($this->classType, $this->memberRef1, $this->dataTypeRef, $this->randomString(), '');
		$this->memberId2 = $this->dds->addMember($this->classType, $this->memberRef2, $this->dataTypeRef, $this->randomString(), '');

		// get hold of the change log
		$this->changeLog = neon()->dds->IDdsChangeLogManagement;
		$this->changeLog->clearChangeLog(date('Y-m-d H:i:s'));

	}

	public function _tearDown()
	{
		parent::tearDown();
		$sql = "DROP TABLE IF EXISTS ddt_{$this->classType}; ";
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function testHasChangeLog()
	{
		// check it is false when can't find class type
		$hasChangeLog = $this->changeLog->hasChangeLog($this->randomString());
		$this->assertFalse($hasChangeLog);

		// check it is false when set false
		$this->setChangeLog(false);
		$hasChangeLog = $this->changeLog->hasChangeLog($this->classType);
		$this->assertFalse($hasChangeLog);

		// check it is true when set true
		$this->setChangeLog(true);
		$hasChangeLog = $this->changeLog->hasChangeLog($this->classType);
		$this->assertTrue($hasChangeLog);
	}


	public function testNoChangeLog()
	{
		$this->setChangeLog(false);
		$log = $this->changeLog->listChangeLog();
		$logCount = count($log);
		$edits = [];
		$this->makeObjectEdits(null, $edits, 10, false);
		$log = $this->changeLog->listChangeLog();
		$this->assertCount($logCount, $log);
	}

	public function testWithChangeLog()
	{
		$this->setChangeLog(true);
		$log = $this->changeLog->listChangeLog();
		$logCount = count($log);
		$numberOfEdits = $this->makeObjectEdits();
		$iterator = new Iterator;
		$iterator->length = ($logCount+$numberOfEdits);
		$log = $this->changeLog->listChangeLog(null, $iterator);
		$this->assertCount($logCount+$numberOfEdits, $log);
		$this->checkLogEntriesValidity($log);
	}

	private function checkLogEntriesValidity($logList)
	{
		foreach ($logList as $log) {
			switch ($log['change_key']) {
				case 'ADD':
					$this->assertEmpty($log['before']);
					$this->assertNotEmpty($log['after']);
				break;
				case 'EDIT':
					$this->assertNotEmpty($log['before']);
					$this->assertNotEmpty($log['after']);
				break;
				case 'DELETE':
					$this->assertEmpty($log['before']);
					$this->assertEmpty($log['after']);
				break;
				case 'UNDELETE':
					$this->assertEmpty($log['before']);
					$this->assertEmpty($log['after']);
				break;
				case 'DESTROY':
					$this->assertNotEmpty($log['before']);
					$this->assertEmpty($log['after']);
				break;
			}
		}
	}

	/**
	 * Test that the correct entries are made when using
	 * addObjects, editObjects, and deleteObjects
	 */
	public function testBulkObjectChanges()
	{
		$this->setChangeLog(true);
		$log = $this->changeLog->listChangeLog();
		$logCount = count($log);

		// create some objects using addObjects
		$numberOfObjects = 10;
		$objects = [];
		$objectUuids = [];
		for ($i=0; $i<$numberOfObjects; $i++) {
			$uuid = $this->uuid64();
			$objectUuids[] = $uuid;
			$objects[] = [
				'_uuid'=>$uuid,
				$this->memberRef1=>$this->randomString(),
				$this->memberRef2=>$this->randomString()
			];
		}
		$logCount += $numberOfObjects;
		$this->ddo->addObjects($this->classType, $objects);
		$this->assertCount($logCount, $this->changeLog->listChangeLog());

		// now bulk edit the objects
		$this->ddo->editObjects($this->classType, $objectUuids, [$this->memberRef2=>$this->randomString()]);
		$logCount += $numberOfObjects;
		$this->assertCount($logCount, $this->changeLog->listChangeLog());

		// now bulk delete the objects
		$this->ddo->deleteObjects($objectUuids);
		$logCount += $numberOfObjects;
		$this->assertCount($logCount, $this->changeLog->listChangeLog());
	}

	public function testObjectHistory()
	{
		$this->setChangeLog(true);
		$uuid = Hash::uuid64();
		$changes = [];
		$logCount = $this->makeObjectEdits($uuid, $changes);
		$originalEdits = array_reverse($changes);
		$iterator = new Iterator;
		$iterator->length = $logCount;
		$history = $this->changeLog->listObjectHistory($uuid, null, $iterator);
		$this->assertCount($logCount, $history);
		foreach ($history as $pos => $entry) {
			$data = $entry['object'];
			switch($entry['change_key']) {
				case 'ADD':
				case 'EDIT':
				case 'UNDELETE':
				case 'RESTORE':
				case 'DELETE':
					$this->assertArrayHasKey('_uuid', $data);
					$this->assertArrayHasKey($this->memberRef1, $data);
					$this->assertArrayHasKey($this->memberRef1, $data);
					$this->assertEquals($data['_uuid'], $originalEdits[$pos]['_uuid']);
					$this->assertEquals($data[$this->memberRef1], $originalEdits[$pos][$this->memberRef1]);
					$this->assertEquals($data[$this->memberRef2], $originalEdits[$pos][$this->memberRef2]);
				break;
				case 'COMMENT':
					if (!empty($originalEdits[$pos])) {
						$this->assertArrayHasKey('_uuid', $data);
						$this->assertArrayHasKey($this->memberRef1, $data);
						$this->assertArrayHasKey($this->memberRef1, $data);
						$this->assertEquals($data['_uuid'], $originalEdits[$pos]['_uuid']);
						$this->assertEquals($data[$this->memberRef1], $originalEdits[$pos][$this->memberRef1]);
						$this->assertEquals($data[$this->memberRef2], $originalEdits[$pos][$this->memberRef2]);
					} else {
						$this->assertEmpty($data);
					}
				break;
				case 'DESTROY':
					$this->assertEmpty($data);
				break;
			}
		}
	}

	public function testMultipleObjectDestroy()
	{
		$this->setChangeLog(true);
		$log = $this->changeLog->listChangeLog();
		$logCount = count($log);

		$numberOfObjects = 10;
		$uuids = [];
		for ($i = 0; $i<$numberOfObjects; $i++) {
			$uuid = Hash::uuid64();
			$uuids[] = $uuid;
			// create an object
			$this->ddo->addObject($this->classType, [
				'_uuid' => $uuid,
				$this->memberRef1=>$this->randomString(),
				$this->memberRef2=>$this->randomString()
			]);
		}
		// delete one of the objects
		$this->ddo->deleteObject($uuids[0]);
		$this->ddo->destroyObjects($uuids);
		$log = $this->changeLog->listChangeLog();
		$this->assertCount($logCount+2*$numberOfObjects+1, $log);
	}

	public function testRevertingChangeLog()
	{
		$this->setChangeLog(true);
		$uuid = Hash::uuid64();
		$changes = [];
		$logCount = $this->makeObjectEdits($uuid, $changes);
		$originalEdits = array_reverse($changes);
		$iterator = new Iterator;
		$iterator->length = $logCount;
		$history = $this->changeLog->listObjectChangeLog($uuid, null, $iterator);
		$this->assertCount($logCount, $history);
		foreach ($history as $pos => $entry) {
			$logUuid = $entry['log_uuid'];
			$objectAt = $this->changeLog->getObjectAtLogPoint($uuid, $logUuid);
			$this->changeLog->restoreObjectToLogPoint($uuid, $logUuid);
			$restored = $this->ddo->getObject($uuid);
			switch($entry['change_key']) {
				case 'ADD':
				case 'EDIT':
				case 'UNDELETE':
				case 'RESTORE':
				case 'DELETE':
				case 'COMMENT':
					// check the object at matches
					$this->assertEquals($objectAt['_uuid'], $originalEdits[$pos]['_uuid']);
					$this->assertEquals($objectAt[$this->memberRef1], $originalEdits[$pos][$this->memberRef1]);
					$this->assertEquals($objectAt[$this->memberRef2], $originalEdits[$pos][$this->memberRef2]);

					// check the restored matches
					$this->assertEquals($restored['_uuid'], $originalEdits[$pos]['_uuid']);
					$this->assertEquals($restored[$this->memberRef1], $originalEdits[$pos][$this->memberRef1]);
					$this->assertEquals($restored[$this->memberRef2], $originalEdits[$pos][$this->memberRef2]);
				break;
				case 'DESTROY':
					$this->assertEmpty($objectAt);
					$this->assertEmpty($restored);
				break;
			}
		}
	}

	protected function setChangeLog($hasChangeLog)
	{
		$this->changeLog->setChangeLog($this->classType, $hasChangeLog);
	}


	/**
	 * Make a predefined number of object edits. These should result in
	 * at least one change in the change log once everything has been implemented.
	 *
	 * @param string $uuid  if provided then use this for the object creation
	 * @param array $edits  the set of edits obtained from the DB after each change
	 * @param int $noOfRepeatedEdits  the number of repeated edits here. When testing
	 *   the change log you need to ensure the iterator will return the required number
	 *   of edits otherwise it will bomb out at 100.
	 * @return int  the number of expected new entries in the log
	 */
	protected function makeObjectEdits($uuid=null, &$edits=[], $noOfRepeatedEdits=10, $hasChangeLog=true)
	{
		$uuid = ($uuid === null ? Hash::uuid64() : $uuid);

		// create an object
		$this->ddo->addObject($this->classType, [
			'_uuid' => $uuid,
			$this->memberRef1=>$this->randomString(),
			$this->memberRef2=>$this->randomString()
		], $changeAddUuid);
		if ($hasChangeLog) {
			$this->assertNotEmpty($changeAddUuid);
			$edits[] = $this->ddo->getObject($uuid);
		} else {
			$this->assertEmpty($changeAddUuid);
		}

		// edit it
		$this->ddo->editObject($uuid, [$this->memberRef1=>$this->randomString()], $changeEditUuid);
		if ($hasChangeLog) {
			$this->assertNotEmpty($changeEditUuid);
			$this->assertNotEquals($changeAddUuid, $changeEditUuid);
		} else {
			$this->assertEmpty($changeEditUuid);
		}
		$edits[] = $this->ddo->getObject($uuid);

		// add a comment
		$this->changeLog->addComment($uuid, 'The object was edited');
		$edits[] = $this->ddo->getObject($uuid);

		// edit again
		for ($i=0; $i<$noOfRepeatedEdits; $i++) {
			$this->ddo->editObject($uuid, [
				$this->memberRef1=>$this->randomString(),
				$this->memberRef2=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->editObject($uuid, [
				$this->memberRef2=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->editObject($uuid, [
				$this->memberRef1=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
		}

		// add a comment
		$this->changeLog->addComment($uuid, 'The object was edited again several times');
		$edits[] = $this->ddo->getObject($uuid);

		// delete, undelete and edit several times
		for ($i=0; $i<$noOfRepeatedEdits; $i++) {
			$this->ddo->editObject($uuid, [
				$this->memberRef1=>$this->randomString(),
				$this->memberRef2=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->deleteObject($uuid, $changeDeleteUuid);
			if ($hasChangeLog) {
				$this->assertNotEmpty($changeDeleteUuid);
			} else {
				$this->assertEmpty($changeDeleteUuid);
			}
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->undeleteObject($uuid, $changeUndeleteUuid);
			if ($hasChangeLog) {
				$this->assertNotEmpty($changeUndeleteUuid);
				$this->assertNotEquals($changeDeleteUuid, $changeUndeleteUuid);
			} else {
				$this->assertEmpty($changeUndeleteUuid);
			}
			$edits[] = $this->ddo->getObject($uuid);
		}

		// add a comment
		$this->changeLog->addComment($uuid, 'The object was soft deleted, undeleted and edited several times');
		$edits[] = $this->ddo->getObject($uuid);

		// edit again
		for ($i=0; $i<$noOfRepeatedEdits; $i++) {
			$this->ddo->editObject($uuid, [
				$this->memberRef1=>$this->randomString(),
				$this->memberRef2=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->editObject($uuid, [
				$this->memberRef2=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
			$this->ddo->editObject($uuid, [
				$this->memberRef1=>$this->randomString(),
			]);
			$edits[] = $this->ddo->getObject($uuid);
		}

		// add a comment
		$this->changeLog->addComment($uuid, 'The object was edited again several times');
		$edits[] = $this->ddo->getObject($uuid);

		// destroy it
		$this->ddo->destroyObject($uuid, $changeDestroyUuid);
		if ($hasChangeLog) {
			$this->assertNotEmpty($changeDestroyUuid);
			$this->assertNotEquals($changeDeleteUuid, $changeDestroyUuid);
		} else {
			$this->assertEmpty($changeDestroyUuid);
		}
		$edits[] = [];

		// return the number of change log entries made.
		return count($edits);
	}

}