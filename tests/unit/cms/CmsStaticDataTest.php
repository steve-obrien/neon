<?php

namespace neon\tests\unit\cms;

use neon\core\test\Unit;

use \neon\cms\services\cmsManager\CmsManager;
use \Codeception\Util\Debug;
use Yii;

/**
 * Class CmsStaticDataTest
 * @package neon\tests\unit\cms
 *
 */
class CmsStaticDataTest extends Unit
{
	public static function setUpBeforeClass(): void {
		$sql = "TRUNCATE TABLE cms_static_content";
		neon()->db->createCommand($sql)->execute();
	}

	public function setUp(): void {
		$this->cms = new CmsManager();
		$sql = "TRUNCATE TABLE cms_static_content";
		neon()->db->createCommand($sql)->execute();
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function testStaticManagement()
	{
		$staticContent = [];
		$NUM_TESTS = 10;
		for ($i=0; $i<$NUM_TESTS; $i++) {
			$staticContent[$i] = [
				'key'=>$this->randomString(),
				'pageId'=>($i%2 ? $this->randomString(22) : ''),
				'content'=>$this->randomString(100)
			];
		}

		// check to see if the content already exists in the database or not
		// newly created static content won't have been added
		$requests = $this->cms->bulkCreateStaticContentIds($staticContent);
		$results = $this->cms->bulkStaticContentRequest($requests);
		$this->assertCount($NUM_TESTS, $results);
		foreach ($results as $id=>$r) {
			$this->assertTrue(array_key_exists($id, $requests));
			$this->assertNull($r);
		}

		// now add new values of the content to the database
		foreach ($staticContent as &$s) {
			$saved = $this->cms->editStaticContent($s['key'], $s['pageId'], $s['content']);
			$this->assertTrue($saved);
		}
		// and see if it was saved
		$results = $this->cms->bulkStaticContentRequest($requests);
		$this->assertCount($NUM_TESTS, $results);
		foreach ($results as $id=>$r) {
			$this->assertTrue(array_key_exists($id, $requests));
			$this->assertEquals($r['content'], $requests[$id]['content']);
		}
	}
}