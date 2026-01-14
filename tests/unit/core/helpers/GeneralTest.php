<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 07/03/2018
 * Time: 13:50
 */

namespace unit\core\helpers;

use neon\cms\components\CmsUrlRule;
use neon\cms\models\CmsPage;
use neon\cms\models\CmsUrl;
use neon\core\helpers\Hash;
use neon\core\test\Unit;
use yii\helpers\Url;

class GeneralTest extends Unit
{
	public function setUp():void
	{
		neon()->urlManager->baseUrl = '';
		neon()->urlManager->setScriptUrl('index');
		neon()->urlManager->enablePrettyUrl = true;
		neon()->urlManager->showScriptName = false;
		neon()->urlManager->setHostInfo('http://neon-url-test.test');
		// create a cms page
		$cms = new CmsPage([
			'id' => Hash::uuid64(),
			'nice_id' => 'my_test_page',
			'title' => 'My nice test page',
			'status' => 'PUBLISHED',
		]);
		$cms->save();
		$url = new CmsUrl(['nice_id' => 'my_test_page', 'url' => '/really-pretty-custom-url']);
		$url->save();

		// add the cms url rule
		neon()->urlManager->rules[] = new CmsUrlRule();

		neon()->urlManager->addRules([
			'login' => '/user/account/login',
			'logout' => '/user/account/logout'
		]);
	}

	public function tearDown():void
	{
		CmsUrl::deleteAll();
		CmsPage::deleteAll();
	}

	public function testUrl()
	{
		$this->assertEquals('/admin/index/index', url('/admin/index/index'));
		$this->assertEquals('/admin/index/index', url('admin/index/index'));

		$this->assertEquals('http://neon-url-test.test/login', url('/user/account/login', true));
		$this->assertEquals('http://neon-url-test.test/logout', url('/user/account/logout', true));
		$this->assertEquals('http://neon-url-test.test/admin/index/index', url('/admin/index/index', true));

		$this->assertEquals('/really-pretty-custom-url', url(['/cms/render/page', 'nice_id' => 'my_test_page']));
		$this->assertEquals('http://neon-url-test.test/really-pretty-custom-url', url(['/cms/render/page', 'nice_id' => 'my_test_page'], true));

		$this->assertEquals('/my-page-that-does/not/exist', url('my-page-that-does/not/exist'));

		// lets check bugerring about with the baseurl does not feck it up
		neon()->urlManager->baseUrl = '/one';

		$this->assertEquals('/one/admin/index/index', url('/admin/index/index'));
		$this->assertEquals('/one/admin/index/index', url('admin/index/index'));

		$this->assertEquals('http://neon-url-test.test/one/login', url('/user/account/login', true));
		$this->assertEquals('http://neon-url-test.test/one/logout', url('/user/account/logout', true));
		$this->assertEquals('http://neon-url-test.test/one/admin/index/index', url('/admin/index/index', true));

		$this->assertEquals('/one/really-pretty-custom-url', url(['/cms/render/page', 'nice_id' => 'my_test_page']));
		$this->assertEquals('http://neon-url-test.test/one/really-pretty-custom-url', url(['/cms/render/page', 'nice_id' => 'my_test_page'], true));

		$this->assertEquals('/one/my-page-that-does/not/exist', url('my-page-that-does/not/exist'));

	}


	/**
	 * @dataProvider envProvider
	 */
	public function testEnv($envValue, $expected)
	{
		putenv('TEST_ENV_VALUE='.$envValue);
		$this->assertThat(env('TEST_ENV_VALUE'), $this->equalTo($expected));
	}

	public function envProvider()
	{
		return [
			// truthy
			['envValue' => '"true"', 'expected' => 'true'],
			['envValue' => 'true'  , 'expected' => true],
			['envValue' => 'on'    , 'expected' => true],
			['envValue' => 'yes'   , 'expected' => true],
			// falsey
			['envValue' => '"false"'  , 'expected' => 'false'],
			['envValue' => 'false'    , 'expected' => false],
			['envValue' => 'no'       , 'expected' => false],
			['envValue' => 'off'      , 'expected' => false],
			['envValue' => 'none'     , 'expected' => false],

			['envValue' => 'empty'     , 'expected' => ''],
			['envValue' => 'null'      , 'expected' => null],

			['envValue' => '"THIS THING ON?"', 'expected' => 'THIS THING ON?'],
			['envValue' => '\'hello\'', 'expected' => '\'hello\''],

		];
	}
}