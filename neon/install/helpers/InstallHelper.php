<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace neon\install\helpers;

use neon\core\Env;
use \neon\install\forms\InstallDatabase;
use yii\web\Response;

/**
 * Class to aid installation
 */
class InstallHelper
{
	/**
	 * @return string path to local config file
	 */
	public static function getLocalConfigFile()
	{
		return \Neon::getALias(DIR_CONFIG . '/env.ini');
	}

	/**
	 * @return string path to the local config sample file
	 */
	public static function getLocalConfigSampleFile()
	{
		return \Neon::getALias('@neon/install/helpers/env.sample.ini');
	}

	/**
	 * Get the install wizard steps
	 * @return array [progress key => Wizard step]
	 */
	public static function getInstallSteps()
	{
		return [

			0 => 'Ready Check', // (a requirements check stage)
			1 => 'Configure', // condition=>self::hasLocalConfigFile  // create config file
			2 => 'Database Install',// condition=>self::tablesExist  // install tables
			3 => 'Account', // condition=>self::hasUserAccount // install user admin account
			4 => 'Log In' // (just a "well done, all done", link to log in page)
		];
	}

	/**
	 * Match progress steps to the appropriate route that handles them
	 * @return array
	 */
	public static function progressToRoute()
	{
		return [
			0 => ['/install/index/index'],
			1 => ['/install/index/index'],
			2 => ['/install/database/index'],
			3 => ['/install/account/index'],
			4 => ['/install/account/complete']
		];
	}

	/**
	 * Whether the current request is attempting to route to an install controller
	 * @return boolean true if the current route is an install path one
	 */
	public static function isInstallRoute()
	{
		$path = \Neon::$app->getRequest()->getPathInfo();
		$isInstallRoute = (strpos($path, 'install') === 0);
		return $isInstallRoute;
	}

	/**
	 * Returns the install route
	 * @return array install route
	 */
	public static function installRoute()
	{
		return ['/install/index/dispatch'];
	}

	/**
	 * Returns whether the current controller route is an error action route.
	 * This is useful to test if we are currently processing an error.
	 * @return boolean
	 */
	public static function isErrorRoute()
	{
		$path = \Neon::$app->getRequest()->getPathInfo();
		return (strpos($path, neon()->errorHandler->errorAction) === 0);
	}

	/**
	 * Checks if the local config file exists
	 * @return boolean
	 */
	public static function hasLocalConfigFile()
	{
		return file_exists(\Neon::getALias('@config/env.ini'));
	}

	/**
	 * Test we have a correct db connection
	 * @return boolean
	 */
	public static function hasDbConnection()
	{
		try {
			\Neon::$app->db->open();
			return true;
		} catch(\Exception $e) {
			return false;
		}
	}

	/**
	 * Check if the system has an admin (superuser) installed
	 * @return boolean
	 * @throws \yii\db\Exception
	 */
	public static function hasAdminAccount()
	{
		return self::checkForUserAccounts(true);
	}

	/**
	 * Check if the system has a user installed
	 * @return boolean
	 * @throws \yii\db\Exception
	 */
	public static function hasUserAccount()
	{
		return self::checkForUserAccounts();
	}

	/**
	 * Get the required route to redirect to
	 * based on the current progress through the installation
	 */
	public static function getRequiredInstallationRoute()
	{
		$progress = self::getProgress();
		$routes = self::progressToRoute();
		return $routes[$progress];
	}

	/**
	 * return the progress you have made
	 * @return int
	 */
	public static function getProgress()
	{
		$step = 0;
		if (self::hasLocalConfigFile()) {
			// config file exists so step 1 is complete
			$step = 1;
			if (self::hasDbConnection() && self::coreInstalled()) {
				// database tables exist so step 2 is complete
				$step = 2;
				if (!self::hasUserAccount()) {
					// you need to create a user account
					$step = 3;
				} else {
					// all finished!
					$step = 4;
				}
			}
		}
		return $step;
	}

	/**
	 * Has database name been set in the neon()->db?
	 * This captures an installer blip where:
	 * - Database object has been created WITHOUT a database name configured in the dsn
	 * - this is required during installation so we can create the database (as the database does not exist yet)
	 * We run into this problem when the views render partials that run the parent install layout
	 * that tests what step of the process we are in (and check tables exist)
	 * An example:
	 * - The database config options have been set.
	 * - The config file has been created but neon has not yet been reloaded from that config
	 * @return boolean
	 */
	public static function hasDbNameBeenSet()
	{
		return neon()->db->hasDatabaseName();
	}

	/**
	 * Check if the core is installed
	 * @return boolean
	 */
	public static function coreInstalled()
	{
		$migrations = static::getAllMigrations();
		return empty(array_filter($migrations['core']));
	}

	/**
	 * Get all migrations that need to be run
	 * as an array divided into core and apps and then indexed by app name
	 * the keys `core` and `apps` will always exist.
	 * If either returns an empty array then there are no migrations to run.
	 * @return array
	 * ```php
	 * [
	 *     'core' => [
	 *         'core' => ['migration_file'],
	 *         'firefly' => ['migration_file']
	 *         // ...  only apps with migrations are returned
	 *     ],
	 *     'apps' => [
	 *         'tedx' => [
	 *             '@tedx/migrations/m171012_132214_tedx_populate_real_event_times',
	 *             '@tedx/migrations/m171013_095057_tedx_create_community_partners_page'
	 *         ]
	 *     ]
	 * ]
	 * ```
	 */
	public static function getAllMigrations()
	{
		$migrations = ['core' => [], 'apps' => []];
		foreach(neon()->getCoreApps() as $appName => $config) {
			$app = neon()->getApp($appName);
			if ($app) {
				$ms = $app->getNewMigrations();
				$migrations['core'][$appName] = $ms;
			}
		}
		foreach(neon()->getApps() as $appName => $config) {
			$app = neon()->getApp($appName);
			if ($app) {
				$ms = $app->getNewMigrations();
				$migrations['apps'][$appName] = $ms;
			}
		}
		return $migrations;
	}

	/**
	 * Check if a table exists
	 * @param string $tableName
	 * @return boolean
	 */
	public static function tableExists($tableName)
	{
		$result = neon()->db->createCommand('SHOW TABLES like "'.$tableName.'"')->execute();
		return ($result == 1);
	}

	/**
	 * Generate the config file contents.
	 * Relies on the env.sample.php
	 * @param \neon\install\forms\InstallDatabase $model
	 * @throws \Exception if the config sample file is not found
	 * @return string
	 */
	public static function createConfigFileContents(InstallDatabase $model)
	{
		$configSampleFile = self::getLocalConfigSampleFile();
		if (!file_exists($configSampleFile)) {
			throw new \Exception('The installation process generates the configuration from the env.sample.ini file.  Please reload this into the project and try the process again');
		}
		$configSample = file($configSampleFile);
		// could be auto generated from the passed in model data
		$variables = [
			'{{ENVIRONMENT}}' => $model->environment,
			'{{DEBUG}}' => $model->debug,
			'{{TIMEZONE}}' => $model->timezone,
			'{{DB_NAME}}' => $model->name,
			'{{DB_HOST}}' => $model->host,
			'{{DB_USERNAME}}' => $model->username,
			'{{DB_PASSWORD}}' => $model->password,
			'{{DB_TABLE_PREFIX}}' => $model->table_prefix,
			'{{NEON_ENCRYPTION_KEY}}' => Env::getNeonEncryptionKey(),
			'{{NEON_SECRET}}' => Env::getNeonSecret()
		];
		$configFile = str_replace(array_keys($variables), array_values($variables), $configSample);
		return implode("", $configFile);
	}

	/**
	 * Whether we can write and create the new config file
	 *
	 * @return bool
	 */
	public static function canWriteConfig()
	{
		return is_writable(DIR_CONFIG);
	}

	/**
	 * Get the current database configuration as defined by the current config
	 * WITHOUT the database information
	 * @return array
	 */
	public static function getDbConfig()
	{
		return [
			'dsn' => 'mysql:host='.env('DB_HOST', '127.0.0.1'),
			'username' => env('DB_USER'),
			'password' => env('DB_PASSWORD'),
			'tablePrefix' => env('DB_TABLE_PREFIX', ''),
		];
	}

	/**
	 * Write the config file
	 *
	 * @param InstallDatabase $model
	 * @return bool true on success false on failure
	 */
	public static function createConfigFile(InstallDatabase $model)
	{
		$config = self::createConfigFileContents($model);
		return file_put_contents(self::getLocalConfigFile(), $config) ? true  : false;
	}

	/**
	 * Whether the config file exists
	 *
	 * return bool
	 */
	public static function configFileExists()
	{
		return file_exists(self::getLocalConfigFile());
	}

	/**
	 * Check the system requirements
	 * Returns an array of tests that detect if the system meats the requirements for Neon
	 *
	* @return array of requirement tests:
	 *  for e.g:
	 *
	 * ```php
	 * [
	 *     name => PHP version
	 *     mandatory => 1
	 *     condition => 1
	 *     by => Neon Framework
	 *     memo => PHP 5.6.0 or higher is required.
	 *     error => 1// will be true if failed and mandatory
	 *     warning => 1 // be true if failed and not madatory
	 * ]
	 * ```
	 *
	 */
	public static function checkRequirements()
	{
		require_once(neon()->getAlias('@yii/requirements/YiiRequirementChecker.php'));
		$requirementsChecker = new \YiiRequirementChecker();
		return $requirementsChecker->check(__DIR__.'/requirements.php')->getResult();
	}

	const ERROR_DB_UNKNOWN_DATABASE = 1;
	const ERROR_DB_UNKNOWN_HOST = 2;
	const ERROR_DB_ACCESS_DENIED = 3;
	const ERROR_DB = 4;

	/**
	 * Checks the database connection object and reports errors
	 * Will return a 0 if no errors are detected
	 * @param \neon\core\db\Connection $db
	 * @return int 0 for successful connection
	 */
	public static function checkDatabaseConnection(\neon\core\db\Connection $db)
	{
		try {
			$db->open();
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			if (preg_match('/\bUnknown database\b/i', $msg)) {
				return static::ERROR_DB_UNKNOWN_DATABASE;
			}
			else if (preg_match('/\bUnknown .* server host\b/i', $msg)) {
				return static::ERROR_DB_UNKNOWN_HOST;
			}
			else if (preg_match('/\bAccess denied for user\b/i', $msg)) {
				return static::ERROR_DB_ACCESS_DENIED;
			}
			else {
				return static::ERROR_DB;
			}
		}
		return 0;
	}

	/**
	 * Helper functhod to see if any user accounts exist
	 * @param boolean $super  set to true if checking for superuser accounts only
	 * @return boolean
	 * @throws \yii\db\Exception
	 */
	private static function checkForUserAccounts($super=false)
	{
		$userTable = \neon\user\models\User::tableName();
		try {
			$query = 'SELECT `id` from ' . $userTable;
			if ($super)
				$query .= " WHERE `super`=1";
			$result = neon()->db->createCommand($query)->queryOne();
		} catch (\yii\db\Exception $e) {
			// if this has thrown an exception because the user table does not exist.
			//  Then we know we still need to complete the install
			if (preg_match('/\bBase table or view not found\b/i', $e->getMessage())) {
				return false;
			}
			throw $e;
		}
		// if the result is false then no admin records exist
		return ($result === false) ? false : true;
	}


}
