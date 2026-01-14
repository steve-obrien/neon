<?php

namespace neon\tests\unit\daedalus;

use neon\tests\unit\daedalus\DdsBaseTestUnit;
use neon\core\helpers\Hash;
use Yii;

/**
 * Class ChangeLogManagementTest
 * @package neon\tests\unit\daedalus
 */
class ChangeLogManagementTest extends DdsBaseTestUnit
{
	public static function setUpBeforeClass(): void
	{
		neon('dds')->IDdsChangeLogManagement->clearChangeLog(date('Y-m-d H:i:s'));
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass():void
	{
		parent::tearDownAfterClass();
	}

	public function setUp(): void
	{
		parent::setUp();
		// create a basic class type
		$this->classType = $this->randomString();

		// get hold of the change log
		$this->changeLog = neon('dds')->IDdsChangeLogManagement;
		$now = date('Y-m-d H:i:s');
		$this->changeLog->clearChangeLog($now);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		$sql = "DROP TABLE IF EXISTS ddt_{$this->classType}; ";
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function testAddLogEntry()
	{
		$logCount = count($this->changeLog->listChangeLog());
		$numberOfEdits = $this->addLogEntries();
		$newEntries = $this->changeLog->listChangeLog();
		$this->assertCount($logCount+$numberOfEdits, $newEntries);
		foreach ($newEntries as $e) {
			$le = $this->changeLog->getLogEntry($e['log_uuid']);
			$this->assertArraySubset($le, $e);
		}
		$now = date('Y-m-d H:i:s');
		$this->changeLog->clearChangeLog($now);
	}

	public function testBadLogEntryKey()
	{
		$this->expectException(\InvalidArgumentException::class);
		$class = $this->createMockClass();
		$this->changeLog->addLogEntry($this->uuid64(), $class, 'NOTALLOWED', 'Object was created', ['abc']);
	}

	public function testGeneralComment()
	{
		// add a partial comment
		$comment = [
			'module' => $this->randomString(),
			'class_type' => $this->randomString(),
			'description' => $this->randomString(),
		];
		$id = $this->changeLog->addGeneralComment($comment['module'], $comment['class_type'], $comment['description']);
		$entry = $this->changeLog->getLogEntry($id);
		$this->assertArraySubset($comment, $entry);

		// add a full comment
		$comment['object_uuid'] = Hash::uuid64();
		$comment['associated_objects'] = [Hash::uuid64(), Hash::uuid64(), Hash::uuid64()];
		$id = $this->changeLog->addGeneralComment($comment['module'], $comment['class_type'], $comment['description'], $comment['object_uuid'], $comment['associated_objects']);
		$entry = $this->changeLog->getLogEntry($id);
		$this->assertArraySubset($comment, $entry);
	}

	public function testObjectChangeLogAndClearObjectChangeLog()
	{
		$log = $this->changeLog->listChangeLog();
		$logCount = count($log);
		$object = [];
		$objectEdit = [];
		$numberOfObjects = 10;

		// create some log entries
		$numberOfEdits = $logCount;
		for ($i=0; $i<$numberOfObjects; $i++) {
			// make some edits
			$object[$i] = Hash::uuid64();
			$objectEdit[$i] = $this->addLogEntries($object[$i]);
			$numberOfEdits += $objectEdit[$i];

			// check the full change log matches expected
			$log = $this->changeLog->listChangeLog();
			$this->assertCount($numberOfEdits, $log);

			// check an individual change log count matches expected
			$objectLog = $this->changeLog->listObjectChangeLog($object[$i]);
			$this->assertCount($objectEdit[$i], $objectLog);
		}

		// clear some log entries
		$yesterday = date('Y-m-d 00:00:00');
		$now = date('Y-m-d H:i:s');
		for ($i=0; $i<$numberOfObjects; $i++) {
			// clear a log up until yesterday - should have no affect
			$this->changeLog->clearObjectChangeLog($object[$i], $yesterday);
			$objectLog = $this->changeLog->listObjectChangeLog($object[$i]);
			$this->assertCount($objectEdit[$i], $objectLog);

			// clear a log up until now - should clear it
			$this->changeLog->clearObjectChangeLog($object[$i], $now);
			$objectLog = $this->changeLog->listObjectChangeLog($object[$i]);
			$this->assertCount(0, $objectLog);
		}
	}

	public function testClearChangeLog()
	{
		// check we start with a blank change log
		$yesterday = date('Y-m-d 00:00:00');
		$now = date('Y-m-d H:i:s');
		$this->changeLog->clearChangeLog($now);
		$log = $this->changeLog->listChangeLog();
		$this->assertCount(0, $log);

		$numberOfEdits = 0;
		for ($i=0; $i<1; $i++) {
			$numberOfEdits += $this->addLogEntries();
		}
		//$this->assertCount($numberOfEdits, $this->changeLog->listChangeLog());

		// clear up until yesterday - no change
		$this->changeLog->clearChangeLog($yesterday);
		//$this->assertCount($numberOfEdits, $this->changeLog->listChangeLog());

		// clear all up until now - but preserve this class
		$this->changeLog->clearChangeLog($now, [$this->classType], false);
		$this->assertCount($numberOfEdits, $this->changeLog->listChangeLog());

		// clear all up until now - clearing some other random class
		$this->changeLog->clearChangeLog($now, [$this->randomString()], true);
		$this->assertCount($numberOfEdits, $this->changeLog->listChangeLog());

		// clear all up until now - clearing this class
		$this->changeLog->clearChangeLog($now, [$this->classType], true);
		$this->assertCount(0, $this->changeLog->listChangeLog());
	}

	/**
	 * Add a set of log entries faking daedalus edits
	 *
	 * @param string $uuid  if provided then use this for the object creation
	 * @return int  the number of expected new entries in the log
	 */
	protected function addLogEntries($uuid=null)
	{
		$uuid = ($uuid === null ? Hash::uuid64() : $uuid);
		$class = $this->createMockClass();

		// create an object
		$logUuid = $this->changeLog->addLogEntry($uuid, $class, 'ADD', ['Object was created'], ['field1']);
		$this->assertNotEmpty($logUuid);
		$this->assertTrue((strlen($logUuid)==22));

		// edit it
		$this->changeLog->addLogEntry($uuid, $class, 'EDIT', ['Object was edited'], ['def']);

		// edit again
		$this->changeLog->addLogEntry($uuid, $class, 'EDIT', ['Object was edited'], ['ghi']);

		// delete it
		$this->changeLog->addLogEntry($uuid, $class, 'DELETE', ['Object was deleted'], []);

		// undelete it
		$this->changeLog->addLogEntry($uuid, $class, 'UNDELETE', ['Object was undeleted'], []);

		// destroy it
		$this->changeLog->addLogEntry($uuid, $class, 'DESTROY', ['Object was destroyed'], []);

		// return the number of edits made
		return 6;
	}

	private function createMockClass()
	{
		return [
			'change_log'=>true,
			'label'=>$this->classType,
			'class_type'=>$this->classType
		];
	}

}
