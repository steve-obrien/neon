<?php

namespace neon\tests\unit\user;

use \Codeception\Util\Debug;
use neon\core\helpers\Str;
use Yii;

use neon\user\models\User;
use \neon\core\test\Unit;


/**
 * Class UserTest
 * @package neon\tests\unit\user
 */
class UserTest extends Unit
{
	public function setUp():void
	{
		User::deleteAll();
	}

	public function tearDown():void
	{
		User::deleteAll();
	}

	public function testObjectMaps()
	{
		$NUM = 4;
		$existing = User::getUserListUncached();
		$this->assertEquals([], $existing);
		$totalCount = $NUM;

		// create some users
		$data = [];
		for ($i = 0; $i < $NUM; $i++) {
			$data[$i] = [
				'username' => str_replace([' ', '.'], '', faker()->username),
				'email' => faker()->email,
				'password' => faker()->password(20)
			];
			$u = new User($data[$i]);
			$u->save();
		}
		// now get the mapping - use uncached to make sure got the new data
		$maps = User::getUserListUncached();
		$this->assertCount($totalCount, $maps);

		// and filter on the first value of data added
		$map = User::getUserList('', [
			'username'=>$data[0]['username'],
			'email'=>$data[0]['email']
		]);
		$this->assertCount(1, $map);
		$this->assertThat(current($map), $this->stringContains($data[0]['username']));
		$this->assertThat(current($map), $this->stringContains($data[0]['email']));
		// get more than one field back in the call
		$map = User::getUserList('', [], [
			'username',
			'email',
			'password_hash'
		]);
		$this->assertCount($totalCount, $map);
		foreach ($map as $m)
			$this->assertCount(4, $m);
	}
}