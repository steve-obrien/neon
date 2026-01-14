<?php

namespace neon\tests\unit\phoebe;

use \neon\core\test\Unit;
use \neon\phoebe\services\PhoebeService;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;
use neon\tests\unit\daedalus\mocks\DdsAppMigratorMock;

class PhoebeTest extends Unit {

	public $dds;

	/**
	 * @inheritdoc
	 */
	public static function tearDownAfterClass(): void {
		neon()->db->createCommand('TRUNCATE phoebe_class')->execute();
		neon()->db->createCommand('TRUNCATE phoebe_object')->execute();
		neon()->db->createCommand('TRUNCATE phoebe_object_history')->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function setUp():void {
		$this->phoebe = new PhoebeService;
		$this->basic = $this->phoebe->getPhoebeType('basic');

		// can either save to actual migration or use mocked version
		//
		//$this->migrator = new DdsAppMigrator(DIR_TEST.'/temp/system/daedalus/migrations');
		$this->migrator = new DdsAppMigratorMock(DIR_TEST.'/temp/DOES/NOT/EXIST');
		// set the migrator on the factory
		DdsAppMigratorFactory::setMigrator($this->migrator);
		// open our migration
		static $testCount = 0;
		$this->migrator->openMigrationFile('DataTypeManagementTest_'.$testCount++);
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

	/**
	 * Test the IPhoebe interface
	 */
	public function testIPhoebe() {
		$types = $this->phoebe->listPhoebeTypes();
		$this->assertNotCount(0, $types);

		foreach ($types as $type) {
			$item = $this->phoebe->getPhoebeType($type['key']);
			switch ($type['status']) {
				case 'AVAILABLE':
				case 'IN_DEVELOPMENT':
					$this->assertInstanceOf('\neon\phoebe\interfaces\IPhoebeType', $item);
					break;
				case 'WISHLIST':
					$this->assertNull($item);
					break;
			}
		}
	}

	/**
	 * Test the class creation part of IPhoebe
	 */
	public function testIPhoebeTypeClassCreation() {
		$testModule = 'Creation Test';
		// create a class and see if increments count by one
		$classes = $this->basic->listClasses();
		$initialCount = count($classes);
		$classType = $this->randomString();
		$createdClass = $this->basic->addClass($classType, $testModule);
		$classes = $this->basic->listClasses();
		$this->assertEquals(($initialCount + 1), count($classes));
		$this->assertInstanceOf('\neon\phoebe\interfaces\IPhoebeClass', $createdClass);
		$this->assertEquals($testModule, $createdClass->module);
		$attr = $createdClass->toArray();
		$this->assertArrayHasKey('module', $attr);
		$this->assertEquals($testModule, $attr['module']);

		// create the class again and see if there is an error
		try {
			$this->basic->addClass($classType, 'creation test');
			$this->assertFalse(true);
		} catch (\RuntimeException $e) {
			$this->assertTrue(true);
		}

		// get the class and see if it exists
		$gotClass = $this->basic->getClass($classType);
		$this->assertNotNull($gotClass);

		// get this via getClasses and check also there
		$gotClasses = $this->basic->getClasses([$classType]);
		$this->assertNotNull($gotClasses[$classType]);

		// destroy the class and see that one was removed
		$this->assertEquals(1, $createdClass->destroyClass());
	}

	/**
	 * Test the class creation part of IPhoebe
	 */
	public function testIPhoebeTypeClassLifecycle() {
		// create a class and check counts
		$initialCount = count($this->basic->listClasses());
		$initialCountIncDeleted = count($this->basic->listClasses($p, [], [], true));
		$className = $this->randomString();
		$class = $this->basic->addClass($className, 'lifecycle test');
		$this->assertCount(($initialCount + 1), $this->basic->listClasses());

		// try editing the class with the same values. Should return false
		$attributes = $class->toArray();
		$result = $class->editClass($attributes);
		$this->assertFalse($result);

		// edit the class with an illegitimate field. Should return false
		$result = $class->editClass(['class_type' => 'mynewclasstype']);
		$this->assertFalse($result);

		// edit the class with a legitimate field. Should return true
		$result = $class->editClass(['version' => ($attributes['version'] + 1)]);
		$this->assertTrue($result);

		// edit the class with a illegitimate value field. Should return array
		$result = $class->editClass(['version' => 'IAmNotAnInteger']);
		$this->assertCount(1, $result);

		// set the definition and then get it back and check is same
		$definition = ['a' => 'b', 'c' => 123, 'd' => ['beep' => 'beep']];
		$result = $class->editClass(['definition' => $definition]);
		$this->assertTrue($result);
		$savedClass = $this->basic->getClass($class->classType);
		$this->assertArraySubset($definition, $savedClass->definition);

		// clear the definition and then get it back and check is same
		$result = $class->editClass(['definition' => []]);
		$this->assertTrue($result);
		$savedClass = $this->basic->getClass($class->classType);
		$this->assertArraySubset([], $savedClass->definition);

		// delete and undelete the class and make sure things behave as expected
		$class->deleteClass();
		$this->assertCount($initialCount, $this->basic->listClasses());
		$this->assertCount(($initialCountIncDeleted + 1), $this->basic->listClasses($p, [], [], true));
		$class->undeleteClass();
		$this->assertCount(($initialCount + 1), $this->basic->listClasses());

		// this should be the same as the initial count sa both appear in the list with 0 extra undeleted
		$this->assertCount(($initialCountIncDeleted + 1), $this->basic->listClasses($p, [], [], true));

		// destroy the class and see that one was removed
		$this->assertEquals(1, $class->destroyClass());
	}

	/**
	 * Test the class versioning part of IPhoebe
	 */
	public function testIPhoebeTypeClassVersioning() {
		$classType = $this->randomString();
		// create a class and check counts
		$class = $this->basic->addClass($classType, 'versioning test');
		$classCount = count($this->basic->listClasses());
		$this->assertCount(1, $this->basic->listClassVersions($classType));

		// set the class to be version_locked
		$result = $class->editClass(['version_locked' => 1]);
		$this->assertTrue($result);
		$this->assertCount($classCount, $this->basic->listClasses());
		$this->assertCount(1, $this->basic->listClassVersions($classType));

		// now change the version
		$result = $class->editClass(['version' => 2, 'label' => 'hello']);
		// this won't result in a new row in the listing
		$this->assertCount($classCount, $this->basic->listClasses());
		// but should produce a new version
		$this->assertCount(2, $this->basic->listClassVersions($classType));

		$latestClass = $this->basic->getClass($classType);
		$this->assertEquals(2, $latestClass->version);
		$earlierClass = $this->basic->getClass($classType, 1);
		$this->assertEquals(1, $earlierClass->version);

		// undo the version locking
		$class->editClass(['version_locked' => 0]);
		// now change the version
		$result = $class->editClass(['version' => 3, 'label' => 'hello']);
		// this won't result in a new row in the listing
		$this->assertCount($classCount, $this->basic->listClasses());
		// nor produce a new version
		$this->assertCount(2, $this->basic->listClassVersions($classType));

		$latestClass = $this->basic->getClass($classType);
		$this->assertEquals(3, $latestClass->version);
		$missingClass = $this->basic->getClass($classType, 2);
		$this->assertNull($missingClass);
		$earlierClass = $this->basic->getClass($classType, 1);
		$this->assertEquals(1, $earlierClass->version);

		// get this via getClasses and check also there
		$gotClasses = $this->basic->getClasses([$classType]);
		$this->assertNotNull($gotClasses[$classType]);
		$this->assertEquals(3, $gotClasses[$classType]->version);

		// destroy the class and see that two were removed
		$this->assertEquals(2, $class->destroyClass());
	}

	public function testPhoebeObjectLifecycle() {
		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'object lifecycle');
		$class->editClass([
			'definition' => ['hello' => 'there'],
			'label' => $this->randomString(20),
			'description' => $this->randomString(100)
		]);

		// create an object, check it was created and get it by getObject
		$this->assertCount(0, $this->basic->listObjects($classType));
		$created = $this->basic->addObject($classType);
		$object = $this->basic->getObject($created->uuid);
		$this->assertArraySubset($created->toArray(), $object->toArray());
		$this->assertCount(1, $this->basic->listObjects($classType));

		// check the object class is the one we've created
		$objectClass = $object->getIPhoebeClass();
		$this->assertEquals($objectClass->class_type, $classType);
		$this->assertEquals($objectClass->version, $object->version);
		$this->assertEquals($objectClass->phoebe_type, $object->phoebe_type);

		// edit an object
		$data = $this->randomString();
		$object->editObject($data);
		$this->assertEquals($object->data, $data);

		// TODO some checks to make sure it correctly rejects other edits
		// TODO some checks to make sure it correctly rejects other edits
		// TODO some checks to make sure it correctly rejects other edits

		// delete, undelete and destroy the object
		$object->deleteObject();
		$this->assertCount(0, $this->basic->listObjects($classType));
		$object->undeleteObject();
		$this->assertCount(1, $this->basic->listObjects($classType));
		$object->destroyObject();
		$this->assertCount(0, $this->basic->listObjects($classType));
	}

	public function testPhoebeObjectHistory() {
		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'object history');
		$class->editClass([
			'definition' => ['hello' => 'there'],
			'label' => $this->randomString(20),
			'description' => $this->randomString(100),
			'object_history' => true
		]);

		// create an object, check it was created and get it by getObject
		$this->assertCount(0, $this->basic->listObjects($classType));
		$created = $this->basic->addObject($classType);
		$object = $this->basic->getObject($created->uuid);
		$this->assertArraySubset($created->toArray(), $object->toArray());
		$this->assertCount(1, $this->basic->listObjects($classType));
		$this->assertCount(0, $created->listHistory());

		// edit an object
		$created->editObject(['label' => $this->randomString()]);
		$this->assertCount(1, $created->listHistory());
		$created->editObject(['description' => $this->randomString()]);
		$this->assertCount(2, $created->listHistory());

		// clear the history
		$created->clearHistory();
		$this->assertCount(0, $created->listHistory());

		// create some more history
		$created->editObject(['label'=> $this->randomString(),'description' => $this->randomString()]);
		$this->assertCount(1, $created->listHistory());

		// delete, undelete and destroy the object
		$object->deleteObject();
		$this->assertCount(0, $this->basic->listObjects($classType));
		$this->assertCount(1, $created->listHistory());
		$object->undeleteObject();
		$this->assertCount(1, $this->basic->listObjects($classType));
		$this->assertCount(1, $created->listHistory());
		$object->destroyObject();
		$this->assertCount(0, $this->basic->listObjects($classType));
		$this->assertCount(0, $created->listHistory());
	}

	public function testPhoebeOverrideBasicLifecycle()
	{
		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'Class Override Lifecycle');
		$class->editClass([
			'definition' => ['hello' => 'there'],
			'label' => $this->randomString(20),
			'description' => $this->randomString(100),
		]);

		// test we can create an override and retrieve it
		$overrides = $class->listOverrides();
		$this->assertCount(0, $overrides);
		$label = $this->randomString(10);
		$oUuid = $class->addOverride($label);
		$overrides = $class->listOverrides();
		$this->assertCount(1, $overrides);
		$this->assertEquals($oUuid, key($overrides));
		$this->assertEquals($label, current($overrides));
		$override = $class->getOverride($oUuid);
		$this->assertEquals($label, $override['label']);

		// edit the override
		$from = date('Y-m-d 00:00:00');
		$until = date('Y-m-d 23:59:59');
		$selector = $this->randomString();
		$label = $this->randomString(10);
		$isActive = !!$override['is_active'];
		$changes['label'] = $label;
		$changes['active_from'] = $from;
		$changes['active_to'] = $until;
		$changes['selector'] = $selector;
		$changes['is_active'] = $isActive;
		$result = $class->editOverride($oUuid, $changes);
		$this->assertTrue($result);
		// and get it back again
		$override = $class->getOverride($oUuid);
		$this->assertArraySubset($changes, $override);

		// delete it and see that it has gone
		$class->deleteOverride($oUuid);
		$overrides = $class->listOverrides();
		$this->assertCount(0, $overrides);
		// but check we can still get it
		$override = $class->getOverride($oUuid);
		$this->assertEquals($label, $override['label']);

		// undelete it and check that it is back
		$class->undeleteOverride($oUuid);
		$overrides = $class->listOverrides();
		$this->assertCount(1, $overrides);
		// and check we can still get it
		$override = $class->getOverride($oUuid);
		$this->assertEquals($label, $override['label']);

		// destroy it and make sure it has gone
		$class->destroyOverride($oUuid);
		$overrides = $class->listOverrides();
		$this->assertCount(0, $overrides);
		// and check we can't get it
		$override = $class->getOverride($oUuid);
		$this->assertNull($override);
	}

	public function testPhoebeOverrideBasicErrors()
	{
		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'Class Override Bad Data');
		$class->editClass([
			'definition' => ['hello' => 'there'],
			'label' => $this->randomString(20),
			'description' => $this->randomString(100),
		]);

		// create some bad settings
		$settings = [ 'selector'=>'', 'active_from'=>null, 'active_to'=> null ];

		// create the override without an active_from
		// one should be created
		$uuid = $class->addOverride(
			$this->randomString(),
			$settings['selector'],
			$settings['active_from'],
			$settings['active_to']
		);
		$this->assertTrue(is_string($uuid));
		$override = $class->getOverride($uuid);
		$this->assertNotNull($override['active_from']);

		// try adding it again and there should be an error
		// because these will clash on the active from and selector
		$errors = $class->addOverride(
			$this->randomString(),
			$settings['selector'],
			$settings['active_from'],
			$settings['active_to']
		);
		$this->assertTrue(is_array($errors), print_r($errors,true));

	}

	/**
	 * @dataProvider phoebeOverrideSearchProvider
	 * @param type $override
	 * @param type $criteria
	 */
	public function testPhoebeOverrideSearch($settings, $goodFilters, $badFilters)
	{
		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'Class Override Search');
		$class->editClass([
			'definition' => ['hello' => 'there'],
			'label' => $this->randomString(20),
			'description' => $this->randomString(100),
		]);
		// check there are no overrides
		$override = $class->findOverride();
		$this->assertNull($override);

		// create the override
		$oUuid = $class->addOverride(
			$this->randomString(),
			$settings['selector'],
			$settings['active_from'],
			$settings['active_to']
		);
		$this->assertTrue(is_string($oUuid));

		// get the override
		$overrideSaved = $class->getOverride($oUuid);
		$this->assertArraySubset($settings, $overrideSaved);

		// get the override with no filtering
		$override = $class->findOverride();
		if (empty($settings['selector']))
			$this->assertArraySubset($settings, $override);
		else
			$this->assertNull($override);

		// try and find the override given bad filters
		$override = $class->findOverride($badFilters);
		$this->assertNull($override);

		// try and find the override with the good filters
		$override = $class->findOverride($goodFilters);
		$this->assertArraySubset($settings, $override);
	}

	public function testPhoebeObjectAutoOverride()
	{
		// create some test criteria
		$helloInitial = ['hello'=>'there!'];
		$helloOverride = ['hello'=>'there! How are you today?'];

		// create a new class type
		$classType = $this->randomString();
		$class = $this->basic->addClass($classType, 'Class Override Search');
		$class->editClass([
			'definition' => $helloInitial,
			'label' => $this->randomString(20),
			'description' => $this->randomString(100),
		]);

		// create a new object based on this class
		$object = $this->basic->addObject($classType,false);
		$this->assertNull($object->getOverride());

		// try and add a nonsense override
		$object->setOverride($this->uuid64());
		$this->assertNull($object->getOverride());

		// create an override for the class
		$oUuid = $class->addOverride(
			$this->randomString()
		);
		// now edit the override with the overrides
		$class->editOverride($oUuid, ['overrides'=>$helloOverride]);

		// now create an object and check the override can be saved
		$object = $this->basic->addObject($classType, false);
		$result = $object->setOverride($oUuid);
		$this->assertEquals($oUuid, $object->getOverride());

		// now create an object and check the override was autmatically saved
		$object = $this->basic->addObject($classType);
		$this->assertEquals($oUuid, $object->getOverride());

		// now get hold of the class and apply an override and see
		// if the class now has the override applied.
		$this->assertArraySubset($helloInitial, $class->definition);
		$class->applyClassOverrideByUuid($oUuid);
		$this->assertArraySubset($helloOverride, $class->definition);

	}

	public function phoebeOverrideSearchProvider()
	{
		$selector = $this->randomString();
		$activeFrom = date('Y-m-d 00:00:00');
		$activeTo = date('Y-m-d 23:59:59');
		$now = date('Y-m-d H:i:s', time());
		$yesterday = date('Y-m-d 00:00:00', time()-86400);
		$tomorrow = date('Y-m-d 00:00:00', time()+86400);

		return [[
			// test one - testing date with activeFrom
			[ // parameter one
				'selector' => null,
				'active_from' => $activeFrom,
				'active_to' => null,
			],
			[ // good filters
				'date' => $tomorrow,
			],
			[ // bad filters
				'date' => $yesterday,
			]
		],[
			// test two - testing date with activeTo
			[ // parameter one
				'selector' => null,
				'active_from' => $activeFrom,
				'active_to' => $activeTo,
			],
			[ // good filters
				'date' => $now,
			],
			[ // bad filters
				'date' => $tomorrow,
			]
		],[
			// test three - testing selector
			[ // parameter one
				'selector' => $selector,
				'active_from' => $activeFrom,
				'active_to' => $activeTo,
			],
			[ // good filters
				'selector' => $selector
			],
			[ // bad filters
				'selector' => $this->randomString(),
			]
		],[
			// test four - testing selector and activeFrom
			[ // parameter one
				'selector' => $selector,
				'active_from' => $activeFrom,
				'active_to' => null,
			],
			[ // good filters
				'selector' => $selector,
				'date' => $tomorrow
			],
			[ // bad filters
				'selector' => $this->randomString(),
				'date' => $yesterday
			]
		],[
			// test five - testing selector and activeTo
			[ // parameter one
				'selector' => $selector,
				'active_from' => $activeFrom,
				'active_to' => $activeTo,
			],
			[ // good filters
				'selector' => $selector,
				'date' => $now
			],
			[ // bad filters
				'selector' => $this->randomString(),
				'date' => $tomorrow
			]
		]];
	}
}
