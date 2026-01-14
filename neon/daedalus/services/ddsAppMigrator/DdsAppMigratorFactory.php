<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsAppMigrator;

use neon\daedalus\interfaces\IDdsAppMigratorFactory;

class DdsAppMigratorFactory implements IDdsAppMigratorFactory
{
	protected static $migrator = null;

	/**
	 * create a migrator singleton
	 */
	public static function createMigrator($path)
	{
		if (self::$migrator == null)
			self::$migrator = new DdsAppMigrator($path);
		return self::$migrator;
	}

	/**
	 * set a migrator a migrator to use if not standard
	 * e.g. for mocking out in tests
	 */
	public static function setMigrator(\neon\daedalus\interfaces\IDdsAppMigrator $migrator)
	{
		self::$migrator = $migrator;
	}

	/**
	 * clear the currently set migrator
	 */
	public static function clearMigrator()
	{
		self::$migrator = null;
	}
}