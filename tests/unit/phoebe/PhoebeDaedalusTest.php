<?php

namespace neon\tests\unit\phoebe;

use \neon\core\test\Unit;
use \neon\phoebe\services\PhoebeService;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;
use neon\tests\unit\daedalus\mocks\DdsAppMigratorMock;

class PhoebeDaedalusTest extends Unit {

	public static $tables = [];

	public $phoebe = null;

	public static function setUpBeforeClass():void {
		self::clearTables();
		// can either save to actual migration or use mocked version
		//
		//$this->migrator = new DdsAppMigrator(DIR_TEST.'/temp/system/daedalus/migrations');
		$migrator = new DdsAppMigratorMock(DIR_TEST.'/temp/DOES/NOT/EXIST');
		DdsAppMigratorFactory::setMigrator($migrator);
		parent::setUpBeforeClass();
	}

	public static function _tearDownAfterClass() {
		self::clearTables();
		// clear the migrator for other tests
		DdsAppMigratorFactory::clearMigrator();
	}

	protected static function clearTables() {
		neon()->db->createCommand('TRUNCATE phoebe_class')->execute();
		neon()->db->createCommand('TRUNCATE phoebe_object')->execute();
		neon()->db->createCommand('TRUNCATE phoebe_object_history')->execute();
		foreach (self::$tables as $table) {
			neon()->db->createCommand("DELETE FROM `dds_object` WHERE `_class_type` = '$table'")->execute();
			neon()->db->createCommand("DELETE FROM `dds_member` WHERE `class_type` = '$table'")->execute();
			neon()->db->createCommand("DELETE FROM `dds_class` WHERE `class_type` = '$table'")->execute();
			neon()->db->createCommand("DROP TABLE IF EXISTS `ddt_$table`")->execute();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setUp():void {
		// can either save to actual migration or use mocked version
		// DdsAppMigratorFactory::setMigrator(DdsAppMigrator(DIR_TEST.'/temp/system/daedalus/migrations'));
		DdsAppMigratorFactory::setMigrator(new DdsAppMigratorMock(DIR_TEST.'/temp/DOES/NOT/EXIST'));
		$this->tableFields = self::createDaedalusTables();
		self::$tables = array_keys($this->tableFields);
		$this->service = new PhoebeService;
		$this->phoebe = $this->service->getPhoebeType('daedalus');
		$this->ddsMgr = neon('dds')->getIDdsClassManagement();
		$this->ddsObjMgr = neon('dds')->getIDdsObjectManagement();

		$this->migrator = DdsAppMigratorFactory::createMigrator('');
		// open our migration
		static $testCount = 0;
		$this->migrator->openMigrationFile('DataTypeManagementTest_'.$testCount++);

	}

	public function tearDown():void {
		self::clearTables();
		// can either save / output or delete migration file
		// delete migration
		//$this->migrator->closeMigrationFile(true);
		// save or output to debug
		$this->migrator->closeMigrationFile();
	}



	/**
	 * Check that classes created in Daedalus map through to phoebe and that
	 * their lifecycle can be manipulated via phoebe
	 */
	public function testPhoebeDdsClassLifecycle()
	{
		// list the tables and check they match the number in Daedalus
		$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
		$ddsClasses = $this->ddsMgr->listClasses('daedalus_test', $total);
		$this->assertCount($total, $classes);

		// now play with their lifecycles
		$initialCount = count($classes);
		$this->assertGreaterThanOrEqual(2, $initialCount);
		foreach(self::$tables as $table) {
			$this->assertArrayHasKey($table, $classes);
			$this->assertNotNull($classes[$table]['label']);
			$this->assertNotNull($classes[$table]['description']);
			$this->assertNotNull($classes[$table]['module']);
			$this->assertNotNull($classes[$table]['count_total']);
			$this->assertNotNull($classes[$table]['count_deleted']);
			$this->assertNotNull($classes[$table]['count_current']);
		}

		// check table lifecycles
		$count = $initialCount;
		foreach (self::$tables as $table)
		{
			$class = $this->phoebe->getClass($table);
			$label = $this->randomString();
			$class->editClass(['label'=>$label]);
			$check = $this->phoebe->getClass($table);
			$this->assertEquals($check->label, $label);

			// delete the class
			$class->deleteClass();
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertCount(($count-1), $classes);

			// undelete the class
			$class->undeleteClass();
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertCount($count, $classes);

			// finally get rid of it and all daedalus tables to boot
			$class->destroyClass();
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertCount(($count-1), $classes);

			// reduce count by 1 as table now gone
			$count--;
		}

		// list the tables again and check they still match
		$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
		$ddsClasses = $this->ddsMgr->listClasses('daedalus_test',$total);
		$this->assertCount(count($ddsClasses), $classes);

	}

	/**
	 * Check that objects created in Phoebe are correctly mapping through
	 * to Daedalus and behaving correctly locally
	 */
	public function testPhoebeDaedalusObjectMapping()
	{
		// create/delete/destroy an object and then check the counts all make sense
		// and that all calls have correctly propagated to Daedalus
		$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
		foreach (self::$tables as $table) {
			$this->assertEquals(0, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(0, $classes[$table]['count_current']);

			// add an object
			$object = $this->phoebe->addObject($table);
			$ddsObject = $this->ddsObjMgr->getObject($object->uuid);
			$this->assertNotNull($ddsObject);
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertEquals(1, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(1, $classes[$table]['count_current']);

			// edit an object
			foreach ($this->tableFields[$table] as $field) {
				$newValue = $this->randomString();
				$object->editObject([$field=>$newValue]);
				$check = $this->phoebe->getObject($object->uuid);
				$this->assertEquals($check->data[$field], $newValue);
				$ddsObject = $this->ddsObjMgr->getObject($object->uuid);
				$this->assertEquals($ddsObject[$field], $newValue);
			}

			// delete an object
			$object->deleteObject();
			$ddsObject = $this->ddsObjMgr->getObject($object->uuid);
			$this->assertNotNull($ddsObject);
			$this->assertEquals(1, $ddsObject['_deleted']);
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertEquals(1, $classes[$table]['count_total']);
			$this->assertEquals(1, $classes[$table]['count_deleted']);
			$this->assertEquals(0, $classes[$table]['count_current']);

			// undelete an object
			$object->undeleteObject();
			$ddsObject = $this->ddsObjMgr->getObject($object->uuid);
			$this->assertNotNull($ddsObject);
			$this->assertEquals(0, $ddsObject['_deleted']);
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertEquals(1, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(1, $classes[$table]['count_current']);

			// destroy an object
			$object->destroyObject();
			$ddsObject = $this->ddsObjMgr->getObject($object->uuid);
			$this->assertNull($ddsObject);
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertEquals(0, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(0, $classes[$table]['count_current']);
		}
	}

	/**
	 * Test that changes to objects created and manipulated in Daedalus
	 * are correctly mapped through to Phoebe
	 */
	public function testDaedalusPhoebeObjectMapping()
	{
		$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
		foreach (self::$tables as $table) {
			$this->assertEquals(0, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(0, $classes[$table]['count_current']);

			// add an object to daedalus directly
			$data = [];
			foreach ($this->tableFields[$table] as $field)
				$data[$field] = $this->randomString();
			$ddsUuid = $this->ddsObjMgr->addObject($table, $data);
			$ddsObject = $this->ddsObjMgr->getObject($ddsUuid);

			// check found in listed count
			$classes = $this->phoebe->listClasses($paginator, ['module'=>'daedalus_test']);
			$this->assertEquals(1, $classes[$table]['count_total']);
			$this->assertEquals(0, $classes[$table]['count_deleted']);
			$this->assertEquals(1, $classes[$table]['count_current']);

			// get the object via phoebe
			$object = $this->phoebe->getObject($ddsUuid);
			foreach($this->tableFields[$table] as $field)
				$this->assertEquals($object->data[$field], $ddsObject[$field]);

			// delete the object via dds
			$this->ddsObjMgr->deleteObject($ddsUuid);
			$object = $this->phoebe->getObject($ddsUuid);
			$this->assertNotNull($object);
			$this->assertEquals(1, $object->data['_deleted']);

			// undelete the object via dds
			$this->ddsObjMgr->undeleteObject($ddsUuid);
			$object = $this->phoebe->getObject($ddsUuid);
			$this->assertNotNull($object);
			$this->assertEquals(0, $object->data['_deleted']);

			// destroy the object via dds
			$this->ddsObjMgr->destroyObject($ddsUuid);
			$object = $this->phoebe->getObject($ddsUuid);
			$this->assertNull($object);
		}
	}

	/**
	 * create a realistic set of daedalus tables
	 */
	protected static function createDaedalusTables()
	{
		$phoebe = neon('phoebe')->getService()->getPhoebeType('daedalus');

		$aboutSectionsDefinition = [
			"class" => 'about_sections',
			"name" => 'about_sections',
			'description' => 'About Sections',
			'label' => 'About Sections',
			'fields' => [
				["class" => "text", "name"=>"title"],
				["class" => "text", "name" => "tab"],
				["class" => "text", "name" => "content"]
			]
		];
		$phoebe->saveClassDefinition('about_sections', $aboutSectionsDefinition, 'daedalus_test');

		$blogArticleDefinition = [
			"class" => "blog_article",
			"name" => "blog_article",
			"label" => "Blog Article",
			'description' => 'Blog Article blah blah blah',
			"fields" => [
				["class" => "text", "name" => "title"],
				["class" => "text", "name" => "article"]
			]
		];
		$phoebe->saveClassDefinition('blog_article', $blogArticleDefinition, 'daedalus_test');

		// return the tables added and some text fields that can be edited in the tests
		return ['about_sections'=>['title','tab','content'], 'blog_article'=>['title','article']];
	}

}
