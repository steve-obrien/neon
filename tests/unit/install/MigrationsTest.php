<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 18/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\tests\unit\install;

use neon\install\helpers\InstallHelper;
use neon\core\test\Unit;

/**
 */
class MigrationsTest extends Unit
{
	public function testAllMigrations()
	{
		neon_test_database_create();
		$userTableExists = InstallHelper::tableExists('user_user');
		$this->assertTrue($userTableExists);
	}
}