<?php

namespace neon\tests\unit\daedalus;

use \neon\tests\unit\daedalus\DdsBaseTestUnit;
use \neon\daedalus\services\ddsManager\DdsObjectManager;
use \neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use \Codeception\Util\Debug;
use neon\core\helpers\Hash;
use Yii;

/**
 * Class ObjectManagementTest
 * @package neon\tests\unit\daedalus
 *
 * @property DdsDataDefinitionManager $dds
 * @property DdsObjectManager $ddo
 */
class ObjectManagementTest extends DdsBaseTestUnit
{
	public function setUp():void
	{
		$this->dds = new DdsDataDefinitionManager();
		$this->storage = $this->dds->listStorageTypes();
		$this->dataTypeRef = $this->randomString(10);
		$this->dds->addDataType($this->dataTypeRef, "Short Text", "Short Text", '', "textshort");
		$this->classType = $this->randomString();
		$this->dds->addClass($this->classType, 'test');
		$this->memberRef1 = $this->randomString(10);
		$this->memberRef2 = $this->randomString(10);
		$this->memberId1 = $this->dds->addMember($this->classType, $this->memberRef1, $this->dataTypeRef, $this->randomString(), '');
		$this->memberId2 = $this->dds->addMember($this->classType, $this->memberRef2, $this->dataTypeRef, $this->randomString(), '');
		//
		$this->ddo = new DdsObjectManager;
		//
		// use this to create an additional class if required
		$this->classType2 = null;
	}

	public function tearDown():void
	{
		parent::tearDown();
		$sql = "DROP TABLE IF EXISTS ddt_{$this->classType}; ";
		if ($this->classType2)
			$sql = "DROP TABLE IF EXISTS ddt_{$this->classType2}; ";
		$sql .= "DROP TABLE IF EXISTS ddt_person_test;";
		$sql .= "TRUNCATE TABLE dds_link;";
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function testListObjects()
	{
		$NUM = 10;
		// check nothing found with or without deleted set
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);
		$obs = $this->ddo->listObjects($this->classType, $total, true);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);

		// create some objects
		for ($i=0; $i<$NUM; $i++) {
			$data = [
				'_uuid' => $this->uuid64(),
				$this->memberRef1=>$this->randomString(10),
				$this->memberRef2=>$this->randomString(10),
			];
			$this->ddo->addObject($this->classType, $data);
		}

		// list the objects and check ok
		$obs = $this->ddo->listObjects($this->classType, $total, true, false);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);

		// list with the basic information and ordering on a base field
		$obs = $this->ddo->listObjects($this->classType, $total, true, false, 0, 100, ['_uuid'=>'ASC']);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);

		// list with the full information and ordering on a member field
		$obs = $this->ddo->listObjects($this->classType, $total, true, true, 0, 100, [$this->memberRef1=>'ASC']);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);

		// list with the full information and ordering on a member field and returning one item
		$obs = $this->ddo->listObjects($this->classType, $total, true, true, 0, 1, [$this->memberRef1=>'ASC']);
		$this->assertCount(1, $obs);
		$this->assertEquals($NUM, $total);
	}

	public function testObjectLifecycle()
	{
		$NUM = 10;
		// check nothing found with or without deleted set
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);
		$obs = $this->ddo->listObjects($this->classType, $total, true);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);

		// create some objects
		$objId2Data = [];
		$objId2Label = [];
		for ($i=0; $i<$NUM; $i++) {
			$data = [
				$this->memberRef1=>$this->randomString(10),
				$this->memberRef2=>$this->randomString(10),
			];
			if ($i==5) // test incorrect uuids
				$data['_uuid'] = $this->uuid64().'notauuid';
			else if ($i%2) // test correct uuids
				$data['_uuid'] = $this->uuid64();

			$label = $this->randomString();
			$id = $this->ddo->addObject($this->classType, $data);
			if ($i==5) {
				$this->assertNotEquals($data['_uuid'], $id);
				// save the new value back in
				$data['_uuid'] = $id;
			} else if ($i%2) {
				$this->assertEquals($data['_uuid'], $id);
			}
			$objId2Data[$id] = $data;
			$objId2Label[$id] = $label;
		}

		// list the objects and check ok
		$obs = $this->ddo->listObjects($this->classType, $total, true, false);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);
		foreach ($obs as $o) {
			$this->assertEquals($o['_class_type'], $this->classType);
			$this->assertEquals($o['_deleted'], 0);
		}

		// now get each item and make sure has right value
		foreach ($obs as $o) {
			$object = $this->ddo->getObject($o['_uuid']);
			$data = $objId2Data[$o['_uuid']];
			foreach($data as $memRef=>$value) {
				$this->assertEquals($object[$memRef], $value);
			}
			$altData =  [
				$this->memberRef1=>$this->randomString(10),
				$this->memberRef2=>$this->randomString(10),
			];
			$this->ddo->editObject($o['_uuid'], $altData);
			$object = $this->ddo->getObject($o['_uuid']);
			foreach ($altData as $memRef=>$value) {
				$this->assertEquals($object[$memRef], $value);
			}
		}

		// delete objects and undelete objects
		foreach ($obs as $o)
			$this->ddo->deleteObject($o['_uuid']);
		$obs = $this->ddo->listObjects($this->classType, $total, false, false);
		$this->assertCount(0, $obs);
		$this->assertEquals(0, $total);
		$obs = $this->ddo->listObjects($this->classType, $total, true, false);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);
		foreach ($obs as $o)
			$this->ddo->undeleteObject($o['_uuid']);
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertCount($NUM, $obs);
		$this->assertEquals($NUM, $total);

		// destroy the little darlings mwahahahahahaha
		foreach ($obs as $o)
			$this->ddo->destroyObject($o['_uuid']);
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertCount(0, $obs);
		$this->assertEquals(0, $total);
		$obs = $this->ddo->listObjects($this->classType, $total, true, false);
		$this->assertCount(0, $obs);
		$this->assertEquals(0, $total);
	}

	public function testObjectFilterStandardRequests()
	{
		$NUM = 100;
		// check nothing found with or without deleted set
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);

		// add some integer, json and boolean types to the list
		$memberRefInt = 'integer_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefInt, 'integer', '', '');
		$memberRefBool = 'boolean_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefBool, 'boolean', '', '');
		$memberRefJSON = 'json_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefJSON, 'json', '', '');

		// create some objects with varying values of the integer
		for ($i = 0; $i < $NUM; $i++) {
			$data = [
				$this->memberRef1=>$this->randomString(10),
				$this->memberRef2=>$this->randomString(10),
				$memberRefInt=>$i,
				$memberRefBool => ($i%2 ? false : true),
				$memberRefJSON => [ "key"=>$this->randomString(), "int"=>$i ]
			];
			$id = $this->ddo->addObject($this->classType, $data);
			// and get it back again
			$saved = $this->ddo->getObject($id);
			$this->assertEquals($saved[$this->memberRef1], $data[$this->memberRef1]);
			$this->assertEquals($saved[$this->memberRef2], $data[$this->memberRef2]);
			$this->assertEquals($saved[$memberRefInt], $data[$memberRefInt]);
			$this->assertTrue($saved[$memberRefBool] === $data[$memberRefBool]);
			$this->assertCount(count($saved[$memberRefJSON]), $data[$memberRefJSON]);
			$this->assertEquals($saved[$memberRefJSON]['key'], $data[$memberRefJSON]['key']);
			$this->assertEquals($saved[$memberRefJSON]['int'], $i);
		}
		// check they've all come back
		$start = microtime(true);
		$obs = $this->ddo->listObjects($this->classType, $total);
		$end = microtime(true);
		codecept_debug("Time to list $NUM objects: ".round(1000*($end-$start),2)."ms");
		$this->assertTrue($total == $NUM);

		// now create some queries with dodgey filter names which
		// will be canonicalised into valid field names
		$start = microtime(true);
		$order = [ "----".$memberRefInt=>'ASC' ];
		$limit = [ 'start'=>"NAN", 'length'=>100, 'IDONTBELONG'=>"2dss"];
		$queries = [];

		// $query - simple equals
		$filters = [ "----".$memberRefInt, "=", 1];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// $query - simple multiple equals
		$filters = [ [ "----".$memberRefInt, "=", [1,2,3]], [$memberRefInt, "in", [2,3,4]], [$memberRefInt, "not in", [4,5,6]], [$memberRefInt, "!=", [5,6,7]]];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// $query - simple range
		$filters = [ [ $memberRefInt, ">", 1], [ "----".$memberRefInt, "<", 10] ];
		$limit['total']=false;
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// query - complex set of ANDs and ORs
		$filters = [
			[ [ $memberRefInt, ">=", 3 ], [ $memberRefInt, "<=", 4 ] ],
			[ [ $memberRefInt, ">", 6 ], [ $memberRefInt, "<=", 10 ] ],
			[ [ $memberRefInt, ">=", $NUM/2 ], [ $memberRefInt, "<=", min($NUM/2+100,$NUM) ] ]
		];
		$limit['total']=true;
		$totalKey = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);
		$queries[] = $totalKey;

		// query -
		$filters = [
			[ [ $memberRefInt, ">=", 3 ], [ $memberRefInt, "<=", 4 ] ],
			[ [ $memberRefInt, ">", 6 ], [ $memberRefInt, "<=", 10 ] ],
			[ [ $memberRefInt, ">=", $NUM/2 ], [ $memberRefInt, "<=", min($NUM/2+100,$NUM) ] ]
		];
		$limit['start']=10;
		$limit['total']=20;
		$totalReturnedKey = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);
		$queries[] = $totalReturnedKey;

		// query 5 - test string comparison
		$filters = [
			[ [ $memberRefJSON, "like", 'int' ], [ $memberRefJSON, "not like", 'banana' ] ],
		];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters);

		// get the requests
		$requests = $this->ddo->getObjectRequests();
		$this->assertCount(count($queries), $requests['requests'][$this->classType]);
		foreach ($queries as $q)
			$this->assertNotCount(0, $requests['requests'][$this->classType][$q]);
		$end = microtime(true);
		codecept_debug("Time to create the queries: ".round(1000*($end-$start),2)."ms");

		// now run an intermediate query independently of these
		$results = $this->ddo->runSingleObjectRequest($this->classType, $filters);
		$this->assertNotCount(0, $results['rows']);
		// null query
		$filters = [
			[ [ "----".$memberRefInt, ">=", 100*$NUM ], [ $memberRefInt, "<=", 4 ] ],
		];
		$results = $this->ddo->runSingleObjectRequest($this->classType, $filters);
		$this->assertCount(0, $results['rows']);

		// now commit the previous requests
		$start = microtime(true);
		$results = $this->ddo->commitRequests();
		$end = microtime(true);
		codecept_debug("Time to get ".count($results)." results: ".round(1000*($end-$start),2)."ms");

		// check that every query resulted in something
		foreach ($queries as $q) {
			$this->assertNotCount(0,$results[$q]);
			if ($q == $totalKey)
				$this->assertNotNull($results[$q]['total']);
			else if ($q == $totalReturnedKey)
				$this->assertEquals(20, $results[$q]['total']);
			else
				$this->assertNull($results[$q]['total']);
		}
	}

	public function testLogicFilters()
	{
		$NUM = 100;
		// add some integer, json and boolean types to the list
		$memberRefInt = 'integer_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefInt, 'integer', '', '');

		// create some objects with varying values of the integer
		for ($i = 0; $i < $NUM; $i++) {
			$data = [
				$memberRefInt=>$i,
			];
			$this->ddo->addObject($this->classType, $data);
		}

		// create a query with logic string
		$order = [ $memberRefInt=>'ASC' ];
		$limit = [ 'start'=>0, 'length'=>100];
		$queries = [];

		// logical query 1 - single query
		$filters = [[
			[ $memberRefInt, ">=", 3, 'K1' ],
			[ $memberRefInt, "<=", 4, 'K10' ],
			[ $memberRefInt, ">", 6, 'K100' ],
			[ $memberRefInt, "<=", 10, 'K1000' ],
			[ $memberRefInt, ">=", $NUM/2, '1K1' ],
			[ $memberRefInt, "<=", min($NUM/2+100,$NUM), '1K10' ],
			'logic' => "((K1 OR K10) AND (K100 OR K1000)) or (1K1 and 1K10) AND 1K10"
		]];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// logical query 2 - multiple queries
		$filters = [[
			[ $memberRefInt, ">=", 3, 'K1' ],
			[ $memberRefInt, "<=", 4, 'K2' ],
			[ $memberRefInt, ">", 6, 'K3' ],
			[ $memberRefInt, "<=", 10, 'K4' ],
			[ $memberRefInt, ">=", $NUM/2, 'K5' ],
			[ $memberRefInt, "<=", min($NUM/2+100,$NUM), 'K6' ],
			'logic' => "((K1 OR K2) AND NOT (K3 OR K4)) or (K5 and K6) AND K6"
		],[
			[ $memberRefInt, ">=", 3, 'K1' ],
			[ $memberRefInt, "<=", 4, 'K2' ],
			[ $memberRefInt, ">", 6, 'K3' ],
			[ $memberRefInt, "<=", 10, 'K4' ],
			[ $memberRefInt, ">=", $NUM/2, 'K5' ],
			[ $memberRefInt, "<=", min($NUM/2+100,$NUM), 'K6' ],
			'logic' => "((K1 AND K2) OR (K3 AND NOT K4)) or (K5 OR K6) AND K6"
		]];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// logical query 3 - null queries
		$filters = [[
			[ $memberRefInt, "=", 3, 'K300' ],
			[ $memberRefInt, "is null", 'K301' ],
			[ $memberRefInt, "null", 'K302' ],
			[ $memberRefInt, "not NULL", 'K303' ],
			[ $memberRefInt, "IS NOT NULL", 'K304' ],
			'logic' => "(K300 AND (K301 OR K303) AND (K302 OR K304))"
		]];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// logical query 4 - where key is also in string for next query
		$filters = [[
			[ $memberRefInt, "is null", $memberRefInt ],
			[ $memberRefInt, "=", '2', $memberRefInt.'2' ],
			'logic' => "$memberRefInt AND {$memberRefInt}2"
		]];
		$queries[] = $this->ddo->addObjectRequest($this->classType, $filters, $order, $limit);

		// now commit the previous requests
		$results = $this->ddo->commitRequests();
		foreach ($queries as $q)
			$this->assertNotCount(0, $results[$q]);
	}

	/**
	 * @dataProvider BadLogicChecker
	 * @expectedException InvalidArgumentException
	 */
	public function testBadLogicFilters($logic)
	{
		$this->expectException(\InvalidArgumentException::class);
		// add some integer, json and boolean types to the list
		$memberRefInt = 'integer_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefInt, 'integer', '', '');

		// create some objects with varying values of the integer
		$data = [
			$memberRefInt=>1,
		];
		$this->ddo->addObject($this->classType, $data);

		// logical query
		$filters = [[
			[ $memberRefInt, ">=", 3, 'K1' ],
			[ $memberRefInt, "<=", 4, 'K2' ],
			[ $memberRefInt, ">", 6, 'K3' ],
			'logic' => $logic
		]];
		$this->ddo->addObjectRequest($this->classType, $filters);
		$this->ddo->commitRequests();
	}

	public function badLogicChecker()
	{
		return [
			["K1 AND K12 AND K3"], // invalid K12
			["!K1 AND K2 OR K3"], // invalid character
			["K1 NOR K2"] // invalid word NOR
		];
	}

	/**
	 * Test the use of links within Daedalus. This test deals with setting up
	 * some link objects and linking to them. Then the objects are returned using the
	 * four main methods
	 *  . listObjects
	 *  . getObject
	 *  . runSingleObjectRequest
	 *  . addObjectRequest & commitRequests including filtering to test joins
	 * @dataProvider ObjectMultiLinkTypeProvider
	 */
	public function testObjectMultilinks($linkType)
	{
		$NUM = 10;

		$sql = "SELECT COUNT(*) as `count` FROM `dds_link`";
		$linkStartCount = neon()->db->createCommand($sql)->queryAll();

		// create links
		$linkMember1 = 'link_'.$this->randomString(10);
		$linkMember1ClassType = $this->randomString();
		$linkMember2ClassType = $this->randomString();
		$linkMember2 = 'link_'.$this->randomString(10);
		$linkMember1Id = $this->dds->addMember($this->classType, $linkMember1, $linkType, 'Link Member', '', ['link_class'=>$linkMember1ClassType]);
		$linkMember2Id = $this->dds->addMember($this->classType, $linkMember2, $linkType, 'Link Member', '', ['link_class'=>$linkMember2ClassType]);

		$savedLinkDef = $this->dds->getMember($this->classType, $linkMember1);
		$this->assertEquals($savedLinkDef['link_class'], $linkMember1ClassType);
		$this->dds->editMember($this->classType, $linkMember1, ['link_class'=>$this->randomString()]);
		$savedLinkDef = $this->dds->getMember($this->classType, $linkMember1);
		$this->assertNotEquals($savedLinkDef['link_class'], $linkMember1ClassType);

		// create some objects with links and test getObject
		$rowData = [];
		$rowIds = [];
		for ($i = 0; $i < $NUM; $i++) {
			$links = [];
			$data = [
				$linkMember1=>$links,
				$linkMember2=>$links
			];

			$rowIds[] = $id = $this->ddo->addObject($this->classType, $data);
			// sort rowIds for ease of comparison later
			sort($rowIds);
			$data[$linkMember1] = $rowIds;
			$data[$linkMember2] = array_reverse($rowIds);
			$this->ddo->editObject($id, $data);
			$rowData[$id] = $data;

			// and get it back again
			$saved = $this->ddo->getObject($id);

			// get links into alphabetical order for ease of comparison
			foreach ($saved[$linkMember1] as $ml)
				$this->assertTrue(in_array($ml, $data[$linkMember1]));
			foreach ($saved[$linkMember2] as $ml)
				$this->assertTrue(in_array($ml, $data[$linkMember2]));
		}
		$sql = "SELECT COUNT(*) as `count` FROM `dds_link`";
		$linkCount = neon()->db->createCommand($sql)->queryAll();
		$this->assertEquals( (2*(0.5*$NUM*($NUM+1))), $linkCount[0]['count']-$linkStartCount[0]['count']);

		// now test using listObjects
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals($NUM, $total);
		foreach ($obs as $o) {
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember1]), $o[$linkMember1]);
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember2]), $o[$linkMember2]);
		}

		// test using runSingleObjectRequest
		$results = $this->ddo->runSingleObjectRequest($this->classType);
		foreach ($results['rows'] as $o) {
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember1]), $o[$linkMember1]);
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember2]), $o[$linkMember2]);
		}

		// test filtering on joins using runSingleObjectRequest
		$results = $this->ddo->runSingleObjectRequest($this->classType,
			[[[$linkMember1,'=',$data[$linkMember1]]], [[$linkMember2,'=',$data[$linkMember2]]]]
		);
		foreach ($results['rows'] as $o) {
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember1]), $o[$linkMember1]);
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember2]), $o[$linkMember2]);
		}

		// test filtering on joins using runSingleObjectRequest
		$nullResults = $this->ddo->runSingleObjectRequest($this->classType, [$linkMember1,'!=',$data[$linkMember1]]);
		$this->assertCount(0, $nullResults['rows']);

		// delete some of the objects
		foreach ($results['rows'] as $i=>$o) {
			if (count($o[$linkMember1]) == 1 || $i%2==0)
				$this->ddo->deleteObject($o['_uuid']);
		}
		$deletedResults = $this->ddo->runSingleObjectRequest($this->classType);
		foreach ($deletedResults['rows'] as $o) {
			$this->assertNotCount(count($rowData[$o['_uuid']][$linkMember1]), $o[$linkMember1]);
			$this->assertNotCount(count($rowData[$o['_uuid']][$linkMember2]), $o[$linkMember2]);
		}

		// undelete some of the objects
		foreach ($results['rows'] as $i=>$o) {
			$this->ddo->undeleteObject($o['_uuid']);
		}
		$undeletedResults = $this->ddo->runSingleObjectRequest($this->classType);
		foreach ($undeletedResults['rows'] as $o) {
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember1]), $o[$linkMember1]);
			$this->assertCount(count($rowData[$o['_uuid']][$linkMember2]), $o[$linkMember2]);
		}

		// destroy the rows
		foreach ($results['rows'] as $i=>$o) {
			$this->ddo->destroyObject($o['_uuid']);
		}
		$sql = "SELECT COUNT(*) as `count` FROM `dds_link`";
		$linkCount = neon()->db->createCommand($sql)->queryAll();
		$this->assertEquals($linkStartCount[0]['count'], $linkCount[0]['count']);

	}

	public function testAddObjectsWithLinks()
	{
		$NUM = 20;
		$start = microtime(true);
		$linkType = 'link_multi';
		neon()->db->createCommand("TRUNCATE `dds_link`")->execute();

		// create links
		$linkMember1ClassType = $this->randomString();
		$linkMember2ClassType = $this->randomString();
		$linkMember1 = 'link_'.$this->randomString(10);
		$linkMember2 = 'link_'.$this->randomString(10);
		$this->dds->addMember($this->classType, $linkMember1, $linkType, 'Link Member 1', '', ['link_class'=>$linkMember1ClassType]);
		$this->dds->addMember($this->classType, $linkMember2, $linkType, 'Link Member 2', '', ['link_class'=>$linkMember2ClassType]);

		// create some objects with links
		$objects = [];
		$objectUuids = [];
		for ($i = 0; $i < $NUM; $i++) {
			$links = [];
			for ($j=0; $j<$NUM; $j++) {
				$links[] = Hash::uuid64();
			}
			$objectUuids[] = $uuid = Hash::uuid64();
			$objects[] = [
				'_uuid' => $uuid,
				$linkMember1=>$links,
				$linkMember2=>$links
			];
		}
		// add the objects and check links created
		$this->ddo->addObjects($this->classType, $objects, 10);
		$sql = "SELECT COUNT(*) as `count` FROM `dds_link`";
		$linkCount = neon()->db->createCommand($sql)->queryAll();
		$this->assertEquals(2*$NUM*$NUM, $linkCount[0]['count']);

		// delete the objects and check links still there
		$this->ddo->deleteObjects($objectUuids);
		$linkCount = neon()->db->createCommand($sql)->queryAll();
		$this->assertEquals(2*$NUM*$NUM, $linkCount[0]['count']);

		// destroy the objects and check gone
		$this->ddo->destroyObjects($objectUuids);
		$linkCount = neon()->db->createCommand($sql)->queryAll();
		$this->assertEquals(0, $linkCount[0]['count']);

		//dp('time to run '.$NUM.' inserts with '.(2*$NUM*$NUM).' links is', microtime(true)-$start);
	}

	public function ObjectMultiLinkTypeProvider()
	{
		return [
			[
				'link_multi'
			],
			[
				'file_ref_multi'
			],
		];
	}

	public function testMultiObjectEdits()
	{
		// check no objects exist
		$objects = $this->ddo->listObjects($this->classType);
		$count = count($objects);
		$this->assertEquals($count, 0);

		// add some objects
		$ids = [];
		for ($i = 0; $i < 5; $i++) {
			$data = [
				$this->memberRef1=>$this->randomString(10),
			];
			$ids[] = $this->ddo->addObject($this->classType, $data);
		}

		// check no or incorrect changes will fail
		$success = $this->ddo->editObjects($this->classType, [], [$this->memberRef1=>$this->randomString(5)]);
		$this->assertFalse($success);
		$success = $this->ddo->editObjects($this->classType, $ids, []);
		$this->assertFalse($success);
		$success = $this->ddo->editObjects($this->classType, $ids, ['IDontExistOnTheMember'=>$this->randomString(10)]);
		$this->assertFalse($success);


		// set up which objects you're going to test
		$newValue = $this->randomString(20);
		$updateIds = array_slice($ids, 0, 3);
		// add a few spurious ones
		$updateIds[] = $this->randomString(22);
		$updateIds[] = $this->randomString(22);
		$updateIds[] = $this->randomString(22);

		// update that subset and check only those have changed
		$success = $this->ddo->editObjects($this->classType, $updateIds, [$this->memberRef1=>$newValue, 'IDontExistOnTheMember'=>$this->randomString(10)]);
		$this->assertTrue($success);
		$objects = $this->ddo->listObjects($this->classType);
		foreach ($objects as $o) {
			if (in_array($o['_uuid'], $updateIds))
				$this->assertEquals($o[$this->memberRef1], $newValue);
			else
				$this->assertNotEquals($o[$this->memberRef1], $newValue);
		}

	}

	public function testMultiDeletesAndDestroys()
	{
		$NUM = 10;
		$this->classType2 = $this->randomString();
		$this->dds->addClass($this->classType2, 'test');
		$classType2MemberRef1 = $this->randomString(10);
		$classType2MemberId1 = $this->dds->addMember($this->classType2, $classType2MemberRef1, $this->dataTypeRef, $this->randomString(), '');

		// check nothing found with or without deleted set
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);
		$obs = $this->ddo->listObjects($this->classType2, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);

		// add some objects of both types
		$ids = [];
		for ($i = 0; $i < $NUM; $i++) {
			$ids[] = $this->ddo->addObject($this->classType, [$this->memberRef1=>$this->randomString(10)]);
			$ids[] = $this->ddo->addObject($this->classType2, [$classType2MemberRef1=>$this->randomString(10)]);
		}
		$objectIds = $ids;

		// count them to make sure they exist
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals($NUM, $total);
		$this->assertCount($NUM, $obs);
		$obs = $this->ddo->listObjects($this->classType2, $total);
		$this->assertEquals($NUM, $total);
		$this->assertCount($NUM, $obs);

		// now delete the objects
		for ($i=1; $i<=$NUM; $i++) {
			// delete one from each table
			$idsSubset = array_splice($objectIds, 0, 2);
			// delete them all
			$this->ddo->deleteObjects($idsSubset);

			// make sure they've gone
			$obs = $this->ddo->listObjects($this->classType, $total);
			$this->assertEquals($NUM-$i, $total);
			$this->assertCount($NUM-$i, $obs);
			$obs = $this->ddo->listObjects($this->classType2, $total);
			$this->assertEquals($NUM-$i, $total);
			$this->assertCount($NUM-$i, $obs);

			// and that the counts are correct
			$class1 = $this->dds->getClass($this->classType);
			$this->assertEquals($NUM, $class1['count_total']);
			$this->assertEquals($i, $class1['count_deleted']);
			$class2 = $this->dds->getClass($this->classType2);
			$this->assertEquals($NUM, $class2['count_total']);
			$this->assertEquals($i, $class2['count_deleted']);
		}

		// undelete one of the objects
		$this->ddo->undeleteObject($ids[0]);

		// and finally destroy the objects
		$this->ddo->destroyObjects($ids);

		// and check counts are correct
		$class1 = $this->dds->getClass($this->classType);
		$this->assertEquals(0, $class1['count_total']);
		$this->assertEquals(0, $class1['count_deleted']);
		$class2 = $this->dds->getClass($this->classType2);
		$this->assertEquals(0, $class2['count_total']);
		$this->assertEquals(0, $class2['count_deleted']);
	}

	/**
	 * This test intends to ensure that the logic for request and result keys works correctly
	 *
	 * You may call the same query multiple times in one request to detect changes
	 * for example:
	 * 1: get all objects
	 * 2: run some code that changes objects
	 * 3: get all objects
	 *
	 * calls in steps 1 and 3 should show different results
	 */
	public function testMultipleCallsGiveDifferentResults()
	{
		$class = $this->randomString();
		$this->dds->addClass($class, 'test');
		$member = $this->randomString();
		$this->dds->addMember($class, $member, 'textshort', 'test', 'test');
		$uuid = $this->ddo->addObject($class, [$member => 'my first item']);
		$resultKey = $this->ddo->addObjectRequest($class);

		$results = $this->ddo->commitRequests();
		$this->assertEquals($results[$resultKey]['rows'][0][$member], 'my first item');

		$this->ddo->editObject($uuid, [$member => 'my modified item']);
		$resultKey2 = $this->ddo->addObjectRequest($class);

		$results = $this->ddo->commitRequests();
		$this->assertEquals($results[$resultKey2]['rows'][0][$member], 'my modified item');
	}

	public function testRecalulationOfObjectCounts()
	{
		// check no objects exist
		$objects = $this->ddo->listObjects($this->classType);
		$count = count($objects);
		$this->assertEquals($count, 0);
		$objectCount = random_int(10,50);
		$deletedCount = random_int(2,9);

		// add some objects
		$ids = [];
		for ($i = 0; $i < $objectCount; $i++) {
			$data = [
				$this->memberRef1=>$this->randomString(10),
			];
			$ids[] = $this->ddo->addObject($this->classType, $data);
		}
		for ($i=0; $i < $deletedCount; $i++)
			$this->ddo->deleteObject($ids[$i]);

		$counts = $this->ddo->getObjectCounts($this->classType);
		$this->assertEquals($objectCount, $counts['total']);
		$this->assertEquals(($objectCount-$deletedCount), $counts['active']);
		$this->assertEquals($deletedCount, $counts['deleted']);

		neon()->db->createCommand("UPDATE dds_class SET count_total=0, count_deleted=0 WHERE class_type='$this->classType'")->execute();

		$counts = $this->ddo->getObjectCounts($this->classType);
		$this->assertEquals(0, $counts['total']);
		$this->assertEquals(0, $counts['active']);
		$this->assertEquals(0, $counts['deleted']);

		$this->ddo->recalculateObjectCount($this->classType);
		$counts = $this->ddo->getObjectCounts($this->classType);
		$this->assertEquals($objectCount, $counts['total']);
		$this->assertEquals(($objectCount-$deletedCount), $counts['active']);
		$this->assertEquals($deletedCount, $counts['deleted']);

	}


}