<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\interfaces;

/**
 * Factory to get hold of IDdsAppMigrators
 */
interface IDdsAppMigratorFactory
{
	/**
	 * create a migrator singleton
	 * @param string $path  the path to store migrations in
	 */
	public static function createMigrator($path);


	/**
	 * Dependency inject a migrator
	 */
	public static function setMigrator(\neon\daedalus\interfaces\IDdsAppMigrator $migrator);


	/**
	 * clear the currently set migrator
	 */
	public static function clearMigrator();

}