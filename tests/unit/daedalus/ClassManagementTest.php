<?php

namespace neon\tests\unit\daedalus;

use neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;
use neon\tests\unit\daedalus\mocks\DdsAppMigratorMock;

/**
 * Class ClassManagementTest
 * @package neon\tests\unit\daedalus
 *
 * @property DdsDataDefinitionManager $dds
 */
class ClassManagementTest extends DdsBaseTestUnit
{
	/**
	 * @var DdsDataDefinitionManager
	 */
	public $dds;

	/**
	 * @var DdsAppMigrator
	 */
	public $migrator;

	public function setUp(): void
	{
		// can either save to actual migration or use mocked version
		//
		//$this->migrator = new DdsAppMigrator(DIR_TEST.'/temp/system/daedalus/migrations');
		$this->migrator = new DdsAppMigratorMock(DIR_TEST.'/temp/DOES/NOT/EXIST');
		// set the migrator on the factory
		DdsAppMigratorFactory::setMigrator($this->migrator);
		// open our migration
		static $testCount = 0;
		$this->migrator->openMigrationFile('DataTypeManagementTest_'.$testCount++);

		$this->dds = new DdsDataDefinitionManager();
		$this->storage = $this->dds->listStorageTypes();
		$this->dataTypeRef = $this->randomString(10);
		$this->dds->addDataType($this->dataTypeRef, "Short Text", "Short Text", '', "textshort");
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

	public function testClassLifecycle() {
		// check there's no classes
		$classes = $this->dds->listClasses('test', $total, true);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $classes);

		// create a class
		$classType = $this->randomString(10);
		// as this is random we'd expect it to be unique except exceptionally rarely
		$this->assertTrue($this->dds->isClassTypeUnique($classType));
		$label = $this->randomString(40);
		$description = $this->getJsonArray();
		$this->dds->addClass($classType, 'test');
		$this->dds->editClass($classType, ['label'=>$label, 'description'=>$description, 'change_log'=>1]);
		// and now we'd expect it to not be unique
		$this->assertFalse($this->dds->isClassTypeUnique($classType));

		// check it's listed in test module
		$classes = $this->dds->listClasses('test', $total);
		$this->assertEquals(1, $total);
		// and in the null module but not in another
		$allClasses = $this->dds->listClasses(null, $total);
		$this->assertCount(1, $allClasses);
		$this->assertCount(0, $this->dds->listClasses('notthere', $total));

		// check it was saved
		$myClass = $this->dds->getClass($classes[0]['class_type']);
		$this->assertEquals($myClass['label'], $label);
		$this->assertEquals($myClass['description'], $description);
		$this->assertEquals($myClass['change_log'], 1);
		$this->assertEquals($myClass['module'], 'test');

		// edit it
		$changes = [
			'label' => $this->randomString(),
			'description' => $this->randomString(),
			'change_log'=>0,
			'module' => null
		];
		$this->dds->editClass($classType, $changes);
		// check it was saved
		$myClass = $this->dds->getClass($classes[0]['class_type']);
		$this->assertEquals($changes['label'], $myClass['label']);
		$this->assertEquals($changes['description'], $myClass['description']);
		$this->assertEquals(null, $myClass['module']);
		$this->assertEquals(0, $myClass['change_log']);

		// check it appears in the test list despite having a null module
		$classes = $this->dds->listClasses('test', $total);
		$this->assertEquals(1, $total);
		$this->assertCount(1, $classes);

		// delete the class
		$this->dds->deleteClass($classType);
		$this->assertCount(0, $this->dds->listClasses(null, $total));
		$this->assertCount(1, $this->dds->listClasses(null, $total, true));
		// undelete the class
		$this->dds->undeleteClass($classType);
		$this->assertCount(1, $this->dds->listClasses(null, $total));
		$this->assertCount(1, $this->dds->listClasses(null, $total, true));
		// destroy the class
		$this->dds->destroyClass($classType);
		$this->assertCount(0, $this->dds->listClasses(null, $total));
		$this->assertCount(0, $this->dds->listClasses(null, $total, true));
	}

	public function testMemberLifecycle() {
		$classes = $this->dds->listClasses('test', $total, true);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $classes);

		// create a class to add members to
		$classType = $this->randomString(10);
		$label = $this->randomString(10);
		$this->dds->addClass($classType, 'test');
		$this->dds->editClass($classType, ['label'=>$label, 'description'=>$this->randomString()]);

		// list the members on the class
		$members = $this->dds->listMembers($classType);
		$this->assertCount(0, $members);
		$members = $this->dds->listMembers($classType, true);
		$this->assertCount(0, $members);

		// now add a 'choice' member
		$memberRef = $this->randomString();
		$this->assertTrue($this->dds->isMemberRefUnique($classType, $memberRef));
		$label = $this->randomString();
		$description = $this->randomString();
		$choices = [$this->randomString()=>$this->randomString()];
		$memberRef = $this->dds->addMember($classType, $memberRef, 'choice', $label, $description, ['choices'=>$choices]);
		$this->assertFalse($this->dds->isMemberRefUnique($classType, $memberRef));

		// check saved properly
		$members = $this->dds->listMembers($classType);
		$this->assertCount(1, $members);
		$member = $this->dds->getMember($classType, current($members)['member_ref']);
		$this->assertEquals($label, $member['label']);
		$this->assertEquals($description, $member['description']);
		$this->assertEquals($choices, $member['choices']);

		// get just a couple of fields
		$memberFields = $this->dds->getMember($classType, current($members)['member_ref'], ['description','choices','IDontBelong']);
		$this->assertEquals($description, $member['description']);
		$this->assertEquals($choices, $memberFields['choices']);
		$this->assertCount(2, $memberFields);

		// edit the member
		$changes = [
			'label' => $this->randomString(),
			'description' => $this->randomString(),
			'choices' => [$this->randomString()=>$this->randomString()]
		];
		$this->dds->editMember($classType, $memberRef, $changes);
		$member = $this->dds->getMember($classType, current($members)['member_ref']);
		$this->assertEquals($changes['label'], $member['label']);
		$this->assertEquals($changes['description'], $member['description']);
		$this->assertEquals($changes['choices'], $member['choices']);

		// delete, undelete and destroy the member
		$this->dds->deleteMember($classType, $memberRef);
		$this->assertCount(0, $this->dds->listMembers($classType));
		$this->assertCount(1, $this->dds->listMembers($classType, true));
		$this->dds->undeleteMember($classType, $memberRef);
		$this->assertCount(1, $this->dds->listMembers($classType));
		$this->dds->destroyMember($classType, $memberRef);
		$this->assertCount(0, $this->dds->listMembers($classType));
		$this->assertCount(0, $this->dds->listMembers($classType, true));

		// and finally destroy the class
		$this->dds->destroyClass($classType);
	}

	public function testClassMaps()
	{
		// create a class and add a member
		$classType = $this->randomString(10);
		$classLabel = $this->randomString(10);
		$this->dds->addClass($classType, 'test');
		$this->dds->editClass($classType, ['label'=>$classLabel, 'description'=>$this->randomString()]);
		$members = [];
		$memberRef1 = $this->randomString(10);
		$members[] = $this->dds->addMember($classType, $memberRef1, $this->dataTypeRef, $this->randomString(), $this->randomString());
		$memberRef2 = $this->randomString(10);
		$members[] = $this->dds->addMember($classType, $memberRef2, $this->dataTypeRef, $this->randomString(), $this->randomString());
		$memberRef3 = $this->randomString(10);
		$members[] = $this->dds->addMember($classType, $memberRef3, $this->dataTypeRef, $this->randomString(), $this->randomString());

		// test map fields
		$map = $this->dds->getMappableClasses();
		$this->assertCount(0, $map);
		foreach ($members as $m) {
			$member = $this->dds->getMember($classType, $m);
			$this->assertEquals(0, $member['map_field']);
			$mapField = $this->dds->getClassMapField($classType);
			$this->assertNull($mapField);
		}
		for ($i=0; $i<count($members); $i++) {
			$this->dds->setMemberAsMapField($classType, $members[$i]);
			$mappedMember = $this->dds->getMember($classType, $members[$i]);
			foreach ($members as $p => $m) {
				$member = $this->dds->getMember($classType, $m);
				if ($p == $i)
					$this->assertEquals(1, $member['map_field']);
				else
					$this->assertEquals(0, $member['map_field']);
				$map = $this->dds->getMappableClasses();
				$this->assertCount(1, $map);
				$this->assertEquals(key($map), $classType);
				$this->assertEquals(current($map), $classLabel);
				$mapField = $this->dds->getClassMapField($classType);
				$this->assertEquals($mapField, $mappedMember['member_ref']);
			}
		}

		foreach ($members as $m)
			$this->dds->destroyMember($classType, $m);
		$this->dds->destroyClass($classType);
	}

	/**
	 * check isMemberRefUnique throws an exception if the class is unknown
	 * @expectedException InvalidArgumentException
	 */
	public function testIsMemberRefUniqueException() {
		$this->expectException(\InvalidArgumentException::class);
		$this->dds->isMemberRefUnique($this->randomString(), $this->randomString());
	}

	/**
	 * check canonicalise returns a canonicalised string
	 */
	public function testCanonicalise()
	{
		// spaces are replaced by _ so strings won't be equal
		$candidate = $this->randomString(10)."    ".$this->randomString(10);
		$canoned = $this->dds->canonicalise($candidate);
		$this->assertNotNull($canoned);
		$this->assertNotEquals(0, strlen($canoned));
		$this->assertNotEquals($candidate, $canoned);
	}

	/**
	 * check the uuid64 member is available
	 */
	public function testAddUuid64Member()
	{
		$this->dataTypeMemberTest('uuid64');
	}

	/**
	 * check the multilink member is available
	 */
	public function testAddMultilinkMember()
	{
		$this->dataTypeMemberTest('link_multi', ['link_class'=>$this->randomString()]);
	}

	/**
	 * Method to test various datatypes are available properly in the database.
	 * Use when you create a new datatype and want to make sure it can be added
	 * as a member to a class
	 * @param string $dataTypeName
	 */
	protected function dataTypeMemberTest($dataTypeName, $additional=null)
	{
		// create a class to add members to
		$classType = $this->randomString(10);
		$label = $this->randomString(10);
		$this->dds->addClass($classType, 'test');
		$this->dds->editClass($classType, ['label'=>$label, 'description'=>$this->randomString()]);
		$memberRef = 'test_'.$dataTypeName;
		$dataType = $this->dds->getDataType($dataTypeName);
		$definition = json_decode($dataType['definition'], true);

		// ['data_type_ref', 'label', 'description', 'definition', 'storage_ref']
		$this->dds->addMember($classType, $memberRef, $dataTypeName, '', '', $additional);

		$tableName = "ddt_$classType";

		$query = neon()->db->query()->select('CHARACTER_MAXIMUM_LENGTH')
			->from('INFORMATION_SCHEMA.COLUMNS')
			->where(['table_name' => $tableName])
			->andWhere(['table_schema' => env('DB_NAME')])
			->andWhere(['column_name' => $memberRef]);
		$size = $query->scalar();
		$this->assertEquals($definition['size'], $size, 'Check the DDS data size is the same as the size specified');
	}

	protected function getJsonArray()
	{
		$description = [];
		for ($i=0; $i<10; $i++) {
			$iField = $this->randomString();
			$description[$iField]=[];
			for ($j=0; $j<2; $j++) {
				$description[$iField][$this->randomString()] = $this->randomString(5).'\\'.$this->randomString(5);
			}
		}
		return json_encode($description);
	}

}
