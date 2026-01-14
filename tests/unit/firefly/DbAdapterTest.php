<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 02:46
 * @package neon
 */

namespace newicon\tests\unit\firefly;

use \neon\tests\unit\firefly\LocalAdapterTest;

class DbAdapterTest extends LocalAdapterTest
{
	public function setUp():void
	{
		neon()->firefly->driveManager->default = 'db';
		neon()->firefly->driveManager->drives['db'] = [
			'driver' => 'db'
		];
		$this->drive = neon()->firefly->drive('db');
	}

	public function testGetName()
	{
		$this->assertTrue($this->drive->getName() == 'db');
	}
}