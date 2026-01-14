<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core;

defined('DIR_NEON') or define('DIR_NEON', dirname(__DIR__));

use neon\core\helpers\Hash;

/**
 * initializes neon environment.
 * This handles the exchange between the local config and booting Yii
 */
class Env {

	/**
	 * Set up the environment - Load the environment variables
	 */
	public static function environment()
	{
		// We could make this greedy by removing this if statement
		//  - then we try and load additional variables if the .ini file if it exists
		//  - environment variables already defined are immutable
		self::setEnvironmentFromFile(DIR_CONFIG.'/env.ini');
	}

	/**
	 * Get the NEON_SECRET environment variable
	 * @return string
	 */
	public static function getNeonSecret()
	{
		if (empty(env('NEON_SECRET')))
			Env::createEnvironmentVariable('NEON_SECRET');
		return env('NEON_SECRET');
	}

	/**
	 * Get the NEON_ENCRYPTION_KEY environment variable
	 * @return string
	 */
	public static function getNeonEncryptionKey()
	{
		if (empty(env('NEON_ENCRYPTION_KEY')))
			Env::createEnvironmentVariable('NEON_ENCRYPTION_KEY');
		return env('NEON_ENCRYPTION_KEY');
	}


	/**
	 * Uses an env.ini config file to set up the environment
	 * @param  string  $file  - the path to the config file
	 * @throws \Exception - if the environment variable value is an array
	 */
	public static function setEnvironmentFromFile($file, $overwrite=false)
	{
		if (file_exists($file)) {
			// we need to load in the locally set environment ini file
			$iniSettings = parse_ini_file($file, false, INI_SCANNER_TYPED);
			foreach($iniSettings as $name => $value) {
				Env::setEnvironmentVariable($name, $value, $overwrite);
			}
		}
	}

	/**
	 * Set an environment variable
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @param  bool  $overwrite  Whether to force overwriting the environment variable if it already exists defaults to false
	 * @throws \Exception - if the environment variable value is an array
	 */
	public static function setEnvironmentVariable($key, $value, $overwrite=false)
	{
		if (is_array($value)) {
			throw new \Exception('Environment variables do not support nesting. The variable "'.print_r($key, true).'" is poop.');
		}
		// Don't overwrite existing environment variables if $overwrite is false
		// note the `getenv($key)` could be a string 'false' which would mean it is indeed set
		if (!$overwrite && getenv($key) !== false) {
			return;
		}

		// If PHP is running as an Apache module and an existing
		// Apache environment variable exists, overwrite it
		if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($key)) {
			apache_setenv($key, $value);
		}
		if (function_exists('putenv')) {
			putenv("$key=$value");
		}
		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
	}

	/**
	 * Initialises the environment
	 */
	public static function bootstrap()
	{
		// The DIR_ROOT must be defined.
		// Do not be tempted to use `getcwd()` to automatically define the DIR_ROOT as this breaks xdebug and tests
		if (!defined('DIR_ROOT')) {
			throw new \Exception('DIR_ROOT constant must be defined as the path to the root of your neon application');
		}

		defined('DIR_CONFIG') || define('DIR_CONFIG', DIR_ROOT . '/config');
		defined('DIR_VAR') || define('DIR_VAR', DIR_ROOT . '/var');
		defined('DIR_SYSTEM') || define('DIR_SYSTEM', DIR_ROOT . '/system');
		defined('DIR_PUBLIC') || define('DIR_PUBLIC', DIR_ROOT . '/public');

		Env::environment();
		if (!class_exists('Neon'))
			require DIR_NEON.'/core/Yii.php';

		\Neon::setAlias('neon', DIR_NEON);
		\Neon::setAlias('root', DIR_ROOT);
		\Neon::setAlias('config', DIR_CONFIG);
		\Neon::setAlias('var', DIR_VAR);
		// set up the system folder path
		\Neon::setAlias('system', DIR_SYSTEM);
		\Neon::setAlias('webroot', DIR_PUBLIC);
		\Neon::setAlias('public', DIR_PUBLIC);
	}

	/**
	 * Create a neon web application
	 *
	 * @return \neon\core\ApplicationWeb
	 * @param  array  $config  - Properties here will overwrite any previously defined config options
	 * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
	 */
	public static function createWebApplication($config = [])
	{
		self::bootstrap();
		return new \neon\core\ApplicationWeb($config);
	}

	/**
	 * Create a Console Application
	 *
	 * @return \neon\core\ApplicationConsole
	 * @param  array  $config  - Properties here will overwrite any previously defined config options
	 */
	public static function createConsoleApplication($config = [])
	{
		self::bootstrap();
		return new \neon\core\ApplicationConsole($config);
	}

	/**
	 * Create missing environment variables
	 * This is mostly for backwards compatibility
	 * @param string $name
	 * @param boolean $saveToEnvFile
	 */
	private static function createEnvironmentVariable($name, $saveToEnvFile=true)
	{
		$value = Hash::uuid64().Hash::uuid64().Hash::uuid64().Hash::uuid64();
		if ($saveToEnvFile && file_exists(DIR_CONFIG.'/env.ini'))
			file_put_contents(DIR_CONFIG.'/env.ini', PHP_EOL."$name = '$value'", FILE_APPEND | LOCK_EX);
		static::setEnvironmentVariable($name, $value, true);
	}

}
