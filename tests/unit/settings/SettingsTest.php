<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 02:46
 * @package neon
 */

namespace newicon\tests\unit\settings;

use neon\core\test\Unit;
use neon\settings\services\settingsManager\models\Setting;

class SettingsTest extends Unit
{

	public function testSettings()
	{
		Setting::deleteAll();
		$all = neon()->settingsManager->getAll(true);
		$this->assertEquals([], $all);
		$this->assertTrue(neon()->settingsManager->set('test', 'my.test.setting', 'testy'));
		$value = neon()->settingsManager->get('test', 'my.test.setting');
		$this->assertEquals('testy', $value);

		$all = neon()->settingsManager->getAll(true);
		$shouldBe = [
			"test" => ["my.test.setting" => "testy",],
		];
		$this->assertEquals($shouldBe, $all);
		// add more settings
		neon()->settingsManager->set('test', 'a different setting', 'the setting value');
		neon()->settingsManager->set('cms', 'CMS_SETTING', 'CMS_VALUE');
		$all = neon()->settingsManager->getAll(true);
		$shouldBe = [
			"test" => ["my.test.setting" => "testy", "a different setting" => 'the setting value'],
			'cms' => ['CMS_SETTING' => 'CMS_VALUE']
		];
		$this->assertEquals($shouldBe, $all);
		$shouldBe = neon()->settingsManager->get('cms', 'CMS_SETTING');
		$this->assertEquals($shouldBe, 'CMS_VALUE');
	}

	public function testSessionCache()
	{
		neon()->settingsManager->getAll(true);
		// this should be added to getall even though we have not refetched from the db
		neon()->settingsManager->set('anewone', 'a_new_one', 'A NEW ONE');
		$all = neon()->settingsManager->getAll();
		$this->assertArrayHasKey('anewone', $all);
		$this->assertEquals($all['anewone']['a_new_one'], 'A NEW ONE');
	}

}
