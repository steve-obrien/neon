<?php

namespace neon\tests\unit\core\view;

use neon\core\test\Unit;
use yii\web\HttpException;

class RequestTest extends Unit
{
	public function setUp():void
	{

	}

	public function testValidateData()
	{
		$r = new \neon\core\web\Request();
		// test required rules work when null specified
		$req = $r->validateData(['page' => null],[
			['page', 'required']
		]);
		$this->assertTrue($req->hasErrors());

		// test defaults are applied
		$req = $r->validateData(['page' => 'default', 'other' => null, 'thing' => 123], []);
		$this->assertEquals($req->page, 'default');
		$this->assertEquals($req->other, null);
		$this->assertEquals($req->thing, 123);

		$_GET['page'] = '123';
		$req = $r->validateData(['page' => null], []);
		$this->assertEquals($req->page, '123');

		// fresh requests (as GET params are cached internally)
		$r = new \neon\core\web\Request();
		$_GET['page'] = 125;
		$req = $r->validateData(['page' => null, 'length' => 10], [
			['page', 'number', 'max'=>122, 'tooBig' => 'The page must be less than 124'], // have an error
			['length', 'number', 'min'=>0, 'tooSmall' => 'length must be bigger than 0'] // have an error
		]);
		$this->assertTrue($req->hasErrors());
		$this->assertEquals($req->getErrors(), ['page' => ['The page must be less than 124']]);
		$this->assertEquals($req->length, 10);

		$r = new \neon\core\web\Request();
		$this->expectException(HttpException::class);
		$req = $r->validate(['id' => null], [
			['id', 'required'],
			['id', 'string', 'length' => 22]
		], 404);
	}

}
