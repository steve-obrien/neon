<?php

namespace neon\tests\unit\daedalus;

use \neon\tests\unit\daedalus\DdsBaseTestUnit;
use \neon\daedalus\services\ddsManager\DdsObjectManager;
use \neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use \Codeception\Util\Debug;
use Yii;
use neon\core\helpers\Hash;

/**
 * Class ObjectMapTest
 * @package neon\tests\unit\daedalus
 *
 * @property DdsDataDefinitionManager $dds
 * @property DdsObjectManager $ddo
 */
class ObjectMapTest extends DdsBaseTestUnit
{
	public function setUp():void
	{
		$this->dds = new DdsDataDefinitionManager();
		$this->storage = $this->dds->listStorageTypes();
		$this->dataTypeRef = $this->randomString(10);
		$this->dds->addDataType($this->dataTypeRef, "Short Text", "Short Text", '', "textshort");
		$this->classType = $this->randomString();
		$this->dds->addClass($this->classType, 'map_test');
		$this->memberRef1 = $this->randomString(10);
		$this->memberRef2 = $this->randomString(10);
		$this->memberId1 = $this->dds->addMember($this->classType, $this->memberRef1, $this->dataTypeRef, $this->randomString(), '');
		$this->memberId2 = $this->dds->addMember($this->classType, $this->memberRef2, $this->dataTypeRef, $this->randomString(), '');
		//
		$this->ddo = new DdsObjectManager;
		//
		// use these to create additional classes if required
		$this->classType2 = null;
		$this->classType3 = null;
		$this->classType4 = null;
	}

	public function tearDown():void
	{
		parent::tearDown();
		$sql = "DROP TABLE IF EXISTS ddt_{$this->classType}; ";
		if ($this->classType2)
			$sql = "DROP TABLE IF EXISTS ddt_{$this->classType2}; ";
		if ($this->classType3)
			$sql = "DROP TABLE IF EXISTS ddt_{$this->classType3}; ";
		if ($this->classType4)
			$sql = "DROP TABLE IF EXISTS ddt_{$this->classType4}; ";
		$sql .= "DROP TABLE IF EXISTS ddt_person_test;";
		$sql .= "TRUNCATE TABLE dds_link;";
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function testObjectMaps()
	{
		$NUM = 10;
		// check nothing found with or without deleted set
		$obs = $this->ddo->listObjects($this->classType, $total);
		$this->assertEquals(0, $total);
		$this->assertCount(0, $obs);

		// add some integer and text types to the list
		$memberRefInt = 'integer_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefInt, 'integer', '', '');
		$memberRefText = 'text_'.$this->randomString(5);
		$this->dds->addMember($this->classType, $memberRefText, 'textshort', '', '');
		$this->dds->setMemberAsMapField($this->classType, $memberRefText);

		// create some objects with varying values of the integer
		$data = [];
		for ($i = 0; $i < $NUM; $i++) {
			$data[$i] = [
				$this->memberRef1=>$this->randomString(10),
				$this->memberRef2=>$this->randomString(10),
				$memberRefInt=>$i,
				$memberRefText => $this->randomString(10),
			];
			$id = $this->ddo->addObject($this->classType, $data[$i]);
		}

		// now get the mapping
		$map = $this->ddo->getObjectMap($this->classType);
		$this->assertCount($NUM, $map);
		foreach ($data as $d)
			$this->assertContains($d[$memberRefText], $map);

		// and filter on the first value of data added
		$map = $this->ddo->getObjectMap($this->classType, [
			$this->memberRef1=>$data[0][$this->memberRef1],
			$this->memberRef2=>$data[0][$this->memberRef2]
		]);
		$this->assertCount(1, $map);
		$this->assertEquals(current($map), $data[0][$memberRefText]);

		// try a filter based on three characters of the first map field
		// and check we can find at least one of them
		$substr = substr($data[0][$memberRefText],2,3);
		$map = $this->ddo->getObjectMap($this->classType, [
			'_map_field_'=>substr($data[0][$memberRefText],2,3)
		]);
		$this->assertGreaterThanOrEqual(1, count($map));

		// get more than one field back in the call
		$map = $this->ddo->getObjectMap($this->classType, [], [
			$memberRefText,
			$memberRefInt,
			$this->memberRef1
		]);
		$this->assertCount($NUM, $map);
		foreach ($map as $m)
			$this->assertCount(3, $m);
	}

	public function testLinkedObjectMaps()
	{
		// create a new class to link to
		$this->classType2 = $this->randomString();
		$this->dds->addClass($this->classType2, 'test');
		$class2Member = 'linking_'.$this->randomString();
		$class2MemberId = $this->dds->addMember($this->classType2, $class2Member, 'textshort', $this->randomString(), '');
		$this->dds->setMemberAsMapField($this->classType2, $class2Member);

		// add a link on the original to this classMember
		$linkingMember = $this->randomString();
		$this->dds->addMember($this->classType, $linkingMember, 'link_uni', '', '', ['link_class'=>$this->classType2]);

		// create an object in the second class
		$obj2MemberText = 'linked member '.$this->randomString();
		$obj2Id = $this->ddo->addObject($this->classType2, [
			$class2Member => $obj2MemberText
		]);
		$this->ddo->addObject($this->classType, [
			$this->memberRef1 => $this->randomString(),
			$this->memberRef2 => $this->randomString(),
			$linkingMember => $obj2Id
		]);

		$map = $this->ddo->getObjectMap($this->classType, [], [
			$this->memberRef1,
			$this->memberRef2,
			$linkingMember
		]);
		$this->assertCount(1, $map);
		$mapped = current($map);
		$this->assertEquals($obj2MemberText, $mapped[$class2Member]);
	}

	/**
	 * Check that a null request doesn't break anything
	 */
	public function testObjectMapNullRequest()
	{
		$mapRequest = $this->ddo->makeMapLookupRequest(null);
		$this->assertNull($mapRequest);
		$result = $this->ddo->getMapLookupResults($mapRequest);
		$this->assertEmpty($result);
	}

	/**
	 * Check that a request for a non-existent object doesn't break anything
	 */
	public function testObjectMapVoidRequest()
	{
		$this->ddo->clearMapRequests();
		$uuid = $this->uuid64();
		$mapRequest = $this->ddo->makeMapLookupRequest($uuid);
		$this->assertNotNull($mapRequest);
		$result = $this->ddo->getMapLookupResults($mapRequest);
		$this->assertEmpty($result[$uuid]);
	}

	/**
	 * Creates a series of chained map requests through a set of classes.
	 * Each chain request should request a map linked through a series of
	 * classes varying in length from 1 to $testNumber number of links
	 *
	 * The results should be a link
	 *
	 * @dataProvider objectMapsDataProvider
	 * @param int $testNumber
	 */
	public function testObjectMapRequests($testNumber)
	{
		$this->ddo->clearMapRequests();

		// 1. Create Classes
		// create the linked classes so that class (i+1) contains a link back
		// to class (i) via the (i+1) linkMember field. Class (i) has mapMember (i)
		//
		$classType = [];
		$mapMember = [];
		$linkMember = [];
		$start = microtime(true);
		for ($i=0; $i<$testNumber; $i++) {
			$classPrefix = "c{$i}_";
			$classType[$i] = $classPrefix."_".$this->randomString(4);
			$this->dds->addClass($classType[$i], 'test');

			// add a map field so we can map to it
			$mapMember[$i][0] = $classPrefix."map_default";
			$this->dds->addMember($classType[$i], $mapMember[$i][0], 'textshort', $this->randomString(), '');
			$this->dds->setMemberAsMapField($classType[$i], $mapMember[$i][0]);
			$mapMember[$i][1] = $classPrefix."map_alt";
			$this->dds->addMember($classType[$i], $mapMember[$i][1], 'textshort', $this->randomString(), '');

			// add a link field back to the previous class for all except the first class
			if ($i > 0) {
				$linkMember[$i] = $classPrefix."to_c".($i-1);
				$this->dds->addMember($classType[$i], $linkMember[$i], 'link_uni', '', '', ['link_class'=>$classType[($i-1)]]);
			}
		}

		// 2. Create linked objects
		// create the objects linking together backwards as a set of
		// $testNumber chains of lengths from 1 up to $testNumber links
		// and each object links back e.g. (0,2)->(0,1)->(0,0), (1,2)->(1,1)->(1,0)
		//
		$object = [];
		$data = [];
		for ($i=0; $i<$testNumber; $i++) {
			for ($j=0; $j<$testNumber; $j++) {
				$data[$i][$j] = [
					'_uuid'=>Hash::uuid64(),
					$mapMember[$j][0] => "($i,$j)".($j>0 ? " -> ($i,".($j-1).")" : ''),
					$mapMember[$j][1] => "ALT ($i,$j)".($j>0 ? " -> ($i,".($j-1).")" : ''),
				];
				if ($j>0) // set the link back to the previous one
					$data[$i][$j][$linkMember[$j]] = $object[$i][($j-1)];
				$object[$i][$j] = $this->ddo->addObject($classType[$j], $data[$i][$j]);
			}
		}
		$timeObjectCreation = microtime(true);

		// 3. Create map requests
		// create individual requests for the items at the end of the chains
		// also collect all of the map objects to request together at the end
		$dataMapRequest = [];
		$objectUuids = [];
		for ($i=0; $i<$testNumber; $i++) {
			// the end linked object is at $testNumber - 1 at the end.
			$endObjectUuid = $object[$i][($testNumber-1)];
			$objectUuids[] = $endObjectUuid;
			$memberChain = array_reverse($linkMember);
			for ($j=0;$j<$testNumber;$j++) {
				$subMemberChain = array_slice($memberChain, 0, $testNumber-$j-1);
				// forward order request
				$dataMapRequest[$i][$j][0] = $this->ddo->makeMapChainLookupRequest($endObjectUuid, $subMemberChain, [], true);
				// reverse order request
				$dataMapRequest[$i][$j][1] = $this->ddo->makeMapChainLookupRequest($endObjectUuid, $subMemberChain, [], false);
			}
		}

		// now make two requests for all of the created uuids
		for ($i=0; $i<$testNumber; $i++) {
			$memberChainToGet = array_slice($memberChain, 0, $i);
			$multiMapChainRequest[$i] = $this->ddo->makeMapChainLookupRequest($objectUuids, $memberChainToGet);
		}

		$multiMapRequest = $this->ddo->makeMapLookupRequest($objectUuids, [$mapMember[$testNumber-1][1], $mapMember[$testNumber-1][0]]);
		$timeRequests = microtime(true);

		// 4. Commit the requests
		//
		$this->ddo->commitMapRequests();
		$timeCommit = microtime(true);

		// 5. Get the results
		//
		for ($i=0; $i<$testNumber; $i++) {
			for ($j=0; $j<$testNumber; $j++) {
				for ($l=0; $l<=1; $l++) {
					$mapResult = $this->ddo->getMapChainLookupResults($dataMapRequest[$i][$j][$l]);
					// check we have the full chain
					$this->assertCount(($testNumber-$j), $mapResult);
					// check we have the correct values
					for ($k=0; $k<($testNumber-$j); $k++) {
						$item = ($l==0) ? $k : ($testNumber-$j-$k-1);
						// check the key maps to the correct object
						$this->assertEquals($object[$i][$k+$j], key($mapResult[$item]));
						// check the map value maps to the correct map value
						$this->assertEquals($data[$i][$j+$k][$mapMember[$j+$k][0]], current($mapResult[$item]));
					}
				}
			}
		}
		$timeGetMap = microtime(true);

		// TODO - ACTUALLY TEST THESE CORRECTLY
		// get the multiMapChainRequest results
		for ($i=0; $i<$testNumber; $i++) {
			$mapResult = $this->ddo->getMapChainLookupResults($multiMapChainRequest[$i]);
			//dp('multiMap chain result for '.$i, $mapResult);
		}

		// TODO - ACTUALLY TEST THESE CORRECTLY
		// get the multiMapRequest results
		for ($i=0; $i<$testNumber; $i++) {
			$mapResult = $this->ddo->getMapLookupResults($multiMapRequest);
			//dp('Multimap Result',$mapResult);
		}

		// clear up
		for ($i=0; $i<$testNumber; $i++) {
			for ($j=0; $j<$testNumber; $j++) {
				// yes this does say $j $i not $i $j
				$this->ddo->deleteObject($object[$j][$i]);
				$this->ddo->destroyObject($object[$j][$i]);
			}
			$this->dds->deleteClass($classType[$i]);
			$this->dds->destroyClass($classType[$i]);
		}
		$timeClearUp = microtime(true);
		$timeTotal = $timeClearUp - $start;

		/*dp( '', 'Most of this time is in creating and clearing out objects and tables',
			"timings for objects in ".(0.5*($testNumber+1)*$testNumber)." chains of $testNumber to 1 in length (total ".($testNumber*$testNumber).' objects)' ,
			'create objects '.floor(1000*($timeObjectCreation-$start)).'ms, '.(floor(10000*(($timeObjectCreation-$start)/($timeTotal)))/100).'%',
			'destroy objects '.floor(1000*($timeClearUp-$timeGetMap)).'ms, '.(floor(10000*(($timeClearUp-$timeGetMap)/($timeTotal)))/100).'%',
			'make requests '.floor(1000*($timeRequests - $timeObjectCreation)).'ms, '.(floor(10000*(($timeRequests - $timeObjectCreation)/($timeTotal)))/100).'%',
			'commit requests '.floor(1000*($timeCommit - $timeRequests)).'ms, '.(floor(10000*(($timeCommit - $timeRequests)/($timeTotal)))/100).'%',
			'get results '.floor(1000*($timeGetMap - $timeCommit)).'ms, '.(floor(10000*(($timeGetMap - $timeCommit)/($timeTotal)))/100).'%'
		);*/
	}

	/**
	 * Check that map requests on separate unlinked objects returns the
	 * expected results and doesn't mix different classes together
	 *
	 * @dataProvider objectMapsDataProvider
	 */
	public function testSeparateObjectMapRequests($testNumber)
	{
		$classNumber = $testNumber;
		$objectNumber = $testNumber;

		$this->ddo->clearMapRequests();

		//
		// create some independent classes
		//
		$classType = [];
		$mapMember = [];
		$linkMember = [];
		$objects = [];
		$data = [];
		$mapRequest = [];

		$start = microtime(true);

		// create some classes, some objects against those classes and a map request
		for ($i=0; $i<$classNumber; $i++) {
			$classPrefix = "c{$i}_";
			$classType[$i] = $classPrefix.$this->randomString();
			$this->dds->addClass($classType[$i], $this->randomString(), $this->randomString(), 'test', 'test');

			// add a map field so we can map to it
			$mapMember[$i] = 'mapped_member_'.$this->randomString();
			$this->dds->addMember($classType[$i], $mapMember[$i], 'textshort', $this->randomString(), '', '');
			$this->dds->setMemberAsMapField($classType[$i], $mapMember[$i]);

			// create a few objects on this class
			for ($j=0; $j<$objectNumber; $j++) {
				$data[$i][$j] = [$mapMember[$i]=>$this->randomString()];
				$objects[$i][$j] = $this->ddo->addObject($classType[$i], $data[$i][$j]);
			}
			$mapRequest[$i] = $this->ddo->makeMapLookupRequest($objects[$i]);
		}

		// commit the requests
		$this->ddo->commitMapRequests();

		//
		// get the results
		//
		$totalMapUuids = [];
		for ($i=0; $i<$classNumber; $i++) {
			$mapResult = $this->ddo->getMapLookupResults($mapRequest[$i]);
			// check you are only getting back what you expect
			$this->assertCount($objectNumber, $mapResult);
			// make sure the returned objects aren't already in the previous results
			foreach ($mapResult as $mrk=>$mr)
				$this->assertFalse(in_array($mrk, $totalMapUuids));
			$totalMapUuids += array_keys($mapResult);
		}

		// clear up the objects and classes
		for ($i=0; $i<$classNumber; $i++) {
			for ($j=0; $j<$classNumber; $j++) {
				$this->ddo->deleteObject($objects[$i][$j]);
				$this->ddo->destroyObject($objects[$i][$j]);
			}
			$this->dds->deleteClass($classType[$i]);
			$this->dds->destroyClass($classType[$i]);
		}
	}

	/**
	 * Run the map request test with increasing numbers of levels in the chain
	 * @return type
	 */
	public function objectMapsDataProvider()
	{
		return [[1], [2], [3], [7], [10]];
	}
}