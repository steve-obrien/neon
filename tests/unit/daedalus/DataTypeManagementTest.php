<?php

namespace neon\tests\unit\daedalus;

use \neon\tests\unit\daedalus\DdsBaseTestUnit;
use \neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use \neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory;
use \neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;
use \neon\tests\unit\daedalus\mocks\DdsAppMigratorMock;

use \Codeception\Util\Debug;
use Yii;

class DataTypeManagementTest extends DdsBaseTestUnit
{
	/**
	 * @var DdsAppMigrator
	 */
	public $migrator=null;

	public function setUp():void {
		// can either save to actual migration or use mocked version
		//
		//$this->migrator = new DdsAppMigrator(DIR_TEST.'/temp/system/daedalus/migrations');
		$this->migrator = new DdsAppMigratorMock(DIR_TEST.'/temp/DOES/NOT/EXIST');

		// set the migrator on the factory
		DdsAppMigratorFactory::setMigrator($this->migrator);

		// open our migration
		static $testCount = 0;
		$this->migrator->openMigrationFile('DataTypeManagementTest_'.$testCount++);

		$this->dds = new DdsDataDefinitionManager;
		$this->storage = $this->dds->listStorageTypes();
	}

	public function tearDown():void
	{
		// can either save / output or delete migration file
		// delete migration
		//$this->migrator->closeMigrationFile(true);
		// save or output to debug
		$this->migrator->closeMigrationFile();

		// clear the migrator for other tests
		DdsAppMigratorFactory::clearMigrator();
	}

	public function testStorageTypes() {
		$storageTypes = $this->dds->listStorageTypes();
		$this->assertNotCount(0, $storageTypes);
		foreach ($storageTypes as $s) {
			$this->assertNotNull($s['storage_ref']);
			$this->assertNotNull($s['label']);
			$this->assertNotNull($s['type']);
			$this->assertNotNull($s['description']);
		}
	}

	public function testDataTypeLifecycle() {
		$prefix = 'ddtlifecycletest_';
		$dts = $this->dds->listDataTypes();
		$initialCount = count($dts);
		$dts = $this->dds->listDataTypes(true);
		$initialAllCount = count($dts);
		$basicStorageRef = $this->storage[0]['storage_ref'];

		// create and edit data types
		foreach ($this->storage as $s) {
			// create a data types ref
			$dataTypeRef = $prefix.$this->randomString(10);
			// as this is random it'll be unique except so rarely that we don't care
			$this->assertTrue($this->dds->isDataTypeUnique($dataTypeRef));
			$label = $this->randomString();
			$description = $this->randomString();
			$definition = "{ a constraint }";
			$this->dds->addDataType($dataTypeRef, $label, $description, $definition, $s['storage_ref']);
			// now as this has been added, trying another one will fail
			$this->assertFalse($this->dds->isDataTypeUnique($dataTypeRef));

			// get the data type and check ok
			$dt = $this->dds->getDataType($dataTypeRef);
			$this->assertEquals($label, $dt['label']);
			$this->assertEquals($description, $dt['description']);
			$this->assertEquals($definition, $dt['definition']);
			$this->assertEquals($s['storage_ref'], $dt['storage_ref']);

			// change the values
			$changes = [
				'data_type_ref' => $this->randomString(10),
				'label' => $this->randomString(),
				'description' => $this->randomString(),
				'definition' => "{ a new constraint }",
				'storage_ref' => $basicStorageRef
			];
			$this->dds->editDataType($dataTypeRef, $changes);
			$dt = $this->dds->getDataType($dataTypeRef);
			// data type should not get changed
			$this->assertEquals($dataTypeRef, $dt['data_type_ref']);
			$this->assertEquals($changes['label'], $dt['label']);
			$this->assertEquals($changes['description'], $dt['description']);
			$this->assertEquals($changes['definition'], $dt['definition']);
			// storage ref should not get changed
			$this->assertEquals($s['storage_ref'], $dt['storage_ref']);
		}

		// delete undelete and destroy
		$dts = $this->dds->listDataTypes();
		$this->assertCount(count($this->storage)+$initialCount, $dts);
		foreach ($dts as $dt) {
			if (strpos($dt['data_type_ref'], $prefix)===0)
				$this->dds->deleteDataType($dt['data_type_ref']);
		}
		$dts = $this->dds->listDataTypes();
		$this->assertCount($initialCount, $dts);
		// now include all deleted
		$dts = $this->dds->listDataTypes(true);
		$this->assertCount(count($this->storage)+$initialAllCount, $dts);
		// undelete
		foreach ($dts as $dt)
			$this->dds->undeleteDataType($dt['data_type_ref']);
		$dts = $this->dds->listDataTypes();
		$this->assertCount(count($this->storage)+$initialCount, $dts);
		// now destroy
		foreach ($dts as $dt) {
			if (strpos($dt['data_type_ref'], $prefix)===0)
				$this->dds->destroyDataType($dt['data_type_ref']);
		}
		$dts = $this->dds->listDataTypes();
		$this->assertCount($initialCount, $dts);
		$dts = $this->dds->listDataTypes(true);
		$this->assertCount($initialAllCount, $dts);
	}
}