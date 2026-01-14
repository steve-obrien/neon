<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 18/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\tests\unit\core;
use neon\core\test\Unit;

/**
 * Class AppLoadingTest
 *
 * Test the Neon applications ability to load apps - and resolve app dependencies
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\tests\unit\core
 */
class AppLoadingTest extends Unit
{
	public function testDependencyResolving()
	{
		$apps = [
			'user' => ['requires' => 'dds, core, cms'],
			'cms' => ['requires' => 'dds'],
			'dds' => ['requires' => 'core'],
			'core' => []
		];
		$byDependency = [];
		neon()->orderAppsByRequired($apps, $byDependency);
		$testOrder = array_keys($byDependency);
		$this->assertTrue($testOrder[0] == 'core');
		$this->assertTrue($testOrder[1] == 'dds');
		$this->assertTrue($testOrder[2] == 'cms');
		$this->assertTrue($testOrder[3] == 'user');
		$this->assertEquals([
			'core' => [],
			'dds' => ['requires' => 'core'],
			'cms' => ['requires' => 'dds'],
			'user' => ['requires' => 'dds, core, cms'],
		], $byDependency);
	}
}
