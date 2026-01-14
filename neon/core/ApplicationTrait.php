<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core;

use Neon;
use neon\core\db\Migrator;
use \neon\core\helpers\Arr;

/**
 * Provide common operations for main Application (web application and console Applications)
 *
 * @property array $apps Store the array configuration of installed community and third party applications
 * @property \neon\firefly\App $firefly
 *
 * @method
 */
trait ApplicationTrait
{
	public $debug = true;

	public $env = 'dev';

	public $systemFolderPath = null; // store the systemFolderPath required by neon

	/**
	 * @return \neon\firefly\services\fileManager\interfaces\IFileManager;
	 */
	public function getFirefly()
	{
		return neon()->getApp('firefly');
	}

	/**
	 * @return \neon\cms\App
	 */
	public function getCms()
	{
		return neon()->getApp('cms');
	}

	/**
	 * This is equal to neon()->settings->manager
	 * @return neon\settings\interfaces\ISettingsManager
	 */
	public function getSettingsManager()
	{
		return $this->getSettings()->getSettingsManager();
	}

	/**
	 * @return \neon\settings\App
	 */
	public function getSettings()
	{
		return neon()->getApp('settings');
	}

	/**
	 * @return \neon\daedalus\App
	 */
	public function getDaedalus()
	{
		return neon('daedalus');
	}

	/**
	 * Alias for Daedalus
	 * @return \neon\daedalus\App
	 */
	public function getDds()
	{
		return neon('dds');
	}

	/**
	 * @return \neon\dev\App
	 */
	public function getDev()
	{
		return neon('dev');
	}

	/**
	 * @return \neon\phoebe\App
	 */
	public function getPhoebe()
	{
		return neon('phoebe');
	}

	/**
	 * get the resolved system folder path
	 * @return string
	 */
	public function getSystemFolderPath()
	{
		return $this->getAlias($this->systemFolderPath);
	}

	/**
	 * Dependant function - check if a module exists with the specified name
	 * @param string $name
	 * @return boolean
	 */
	abstract public function hasModule($name);

	/**
	 * Whether this is a web app
	 * @return boolean
	 */
	abstract public function isWebApp();
	/**
	 * Defines path aliases.
	 * This method calls [[Yii::setAlias()]] to register the path aliases.
	 * This method is provided so that you can define path aliases when configuring a module.
	 * @property array list of path aliases to be defined. The array keys are alias names
	 * (must start with `@`) and the array values are the corresponding paths or aliases.
	 * See [[setAliases()]] for an example.
	 * @param array $aliases list of path aliases to be defined. The array keys are alias names
	 * (must start with `@`) and the array values are the corresponding paths or aliases.
	 * For example,
	 *
	 * ```php
	 * [
	 *     '@models' => '@app/models', // an existing alias
	 *     '@backend' => __DIR__ . '/../backend',  // a directory
	 * ]
	 * ```
	 */
	abstract public function setAliases($aliases);

	/**
	 * Retrieves the child module of the specified ID.
	 * This method supports retrieving both child modules and grand child modules.
	 * @param string $id module ID (case-sensitive). To retrieve grand child modules,
	 * use ID path relative to this module (e.g. `admin/content`).
	 * @param bool $load whether to load the module if it is not yet loaded.
	 * @return Module|null the module instance, `null` if the module does not exist.
	 * @see hasModule()
	 */
	abstract public function getModule($id, $load = true);
	abstract function get($id, $throwException = true);


	/**
	 * One config function to rule them all.
	 * This function resolves and modifies the config before it is loaded into the neon
	 * Note: this is run during the preInit step of Neon
	 *
	 * @param  array  $config  - we can adjust the config - this variable holds the resolved config
	 * @return array - the resolved configuration array
	 */
	public function resolveConfig(&$config)
	{
		$appType = $this->isWebApp() ? 'web' : 'console';
		// get core configs
		$common = self::getConfig('@neon/core/config/common.php');
		$app = self::getConfig("@neon/core/config/$appType.php");
		// project overrides (you can override the common and env_*.php config files that are in the core)
		// by specifying the config files with the same name inside the root of the projects config folder
		$projectCommon = self::getConfig('@config/common.php', false);
		$projectConfig = self::getConfig("@config/$appType.php", false);
		// not sure we need this - use env() variables in your common config
		// $projectEnv = self::getConfig("@config/$appType/env_".YII_ENV.'.php', false);
		// merge and create final config
		$config = Arr::merge($common, $app, $projectCommon, $projectConfig, $config);
		return $this->resolvedConfig = $config;
	}

	/**
	 * @var array store the final resolved config used by the app - mainly here for debug purposes
	 */
	public $resolvedConfig;

	/**
	 * @var array Store the array configuration of installed community and third party applications
	 * for example:
	 * ```php
	 * [
	 *		// unique module => app configuration
	 *		'crm' => [
	 *			'class' => '\neon\crm\App',
	 *			'alias' => [
	 *				'@newicon/crm' => '@root/newicon/crm'
	 *			]
	 *		]
	 * ]
	 * ```
	 */
	private $_apps = [];

	/**
	 * @var boolean  note that the apps have already been ordered
	 */
	private $_appsRequiredOrder = false;

	/**
	 * Get the apps available
	 * This is ordered by required apps
	 * @return array
	 */
	public function getApps()
	{
		if ($this->_appsRequiredOrder == false) {
			$this->orderAppsByRequired($this->_apps, $ordered);
			$this->setApps($ordered);
			$this->_appsRequiredOrder = true;
		}
		return $this->_apps;
	}

	/**
	 * set the apps available
	 * @param array $apps
	 */
	public function setApps($apps)
	{
		$this->_apps = $apps;
	}

	/**
	 * @var array The core apps array
	 * Similar array to that of self::$modules and self::$apps
	 * but this denotes the core apps that cannot be uninstalled
	 */
	private $_coreApps = [];

	/**
	 * @var boolean  note that the core apps have already been ordered
	 */
	private $_coreAppsRequiredOrder = false;

	/**
	 * Get the core apps available
	 * @return array
	 */
	public function getCoreApps()
	{
		if ($this->_coreAppsRequiredOrder == false) {
			$this->orderAppsByRequired($this->_coreApps, $ordered);
			$this->setCoreApps($ordered);
			$this->_coreAppsRequiredOrder = true;
		}
		return $this->_coreApps;
	}

	/**
	 * Get all core and user apps
	 * @return array
	 */
	public function getAllApps()
	{
		return array_merge($this->getCoreApps(), $this->getApps());
	}

	/**
	 * Set the core apps available
	 * @param array $coreApps
	 */
	public function setCoreApps($coreApps)
	{
		$this->_coreApps = $coreApps;
	}

	/**
	 * Get a config array from a config path
	 * Can specify a yii style alias.
	 * Set the $required parameter to false to return an empty array if the config file does not exist
	 *
	 * @param string $path full Yii style alias (@config/path/config.php) or file path string to the config file
	 * @param boolean $required whether the config is required, will throw an exception if the file does not exist
	 * @throws \Exception if a config file does not return an array or the config file can not be found and $required is true
	 *
	 * @return array
	 */
	public static function getConfig($path, $required = true)
	{
		$file = Neon::getAlias($path);
		$fileExists = file_exists($file);
		// The file does NOT exist but it IS required
		if (!$fileExists && $required) {
			// exceptions thrown at this stage create white screens of doom
			dp('The config file alias: "' . $path . '" can not be found. File path: "' . $file . '". This config file is required.');
			throw new \Exception('The config file alias: "' . $path . '" can not be found. File path: "' . $file . '". This config file is required.');
		}
		// The file does NOT exist and it is NOT required
		if (!$fileExists && !$required) {
			// the config is not required so we can just return an empty array
			return [];
		}
		$config = require $file;
		if (!is_array($config)) {
			// exceptions thrown at this stage create white screens of doom
			dp('The file "' . $file . '" is not a valid config file. It must return an array');
			throw new \Exception('The file "' . $file . '" is not a valid config file. It must return an array');
		}
		return $config;
	}

	/**
	 * This function loads and bootstraps the community and third party apps
	 * This has to be called in the initial bootup script
	 * Seperating loading of the core and community apps means that we can easily
	 * fall back to a robust core state
	 * It is safe to call on the database at this point.
	 * Check user log in credentials etc.  You may want to only load certain apps based on users
	 * specific user crednetials. Note this is not called automatically during start up ($app->run())
	 * This currently has to be called seperately
	 *
	 * @return void
	 */
	public function registerAllApps()
	{
		Neon::beginProfile('Application::registerAllApps', 'neon\Application');
		// We CAN access the database at this point.
		// This would be useful to get the current list of apps installed / enabled
		// This may delegate to a core Store App. Or App Store.
		// This would simply return the the config
		// However we should not burden the system with loading or knowing about
		// all apps on every request!
		$apps = [];

		if (file_exists(Neon::getAlias('@config/apps.php'))) {
			$apps = require Neon::getAlias('@config/apps.php');
		}
		// the custom apps, will override the core apps,
		// core apps are simply apps defined in the common config $this->apps
		// which exist on the apps key after the preInitilization
		// meaning it would be possible to override an entire core module
		// such as the user, or admin module.
		// Load Additional App Modules
		$this->setApps(Arr::merge($this->getApps(), $apps));
		// remove disabled apps

		$this->registerApps($this->getApps());
		$this->bootstrapApps($this->getApps());
		Neon::endProfile('Application::registerAllApps', 'neon\Application');
	}

	/**
	 * Sets many apps
	 * @see $this->registerApp()
	 * @param array $apps
	 *
	 * @return void
	 */
	public function registerApps($apps)
	{
		foreach ($apps as $name => $config) {
			$this->registerApp($name, $config);
		}
	}

	/**
	 * Bootstrap apps
	 * @param  array  $apps
	 */
	public function bootstrapApps($apps)
	{
		foreach ($apps as $app => $config) {
			$this->bootstrapApp($app, $config);
		}
	}

	/**
	 * Bootstrap the app
	 *
	 * @param  string  $app  The app name
	 * @param  array  $config  The app config array - must contain a class key
	 */
	public function bootstrapApp($app, $config)
	{
		if (isset($config['enabled']) && $config['enabled'] == false) {
			return;
		}
		\Neon::beginProfileTickerBomb($profiletoken = $config['class'] . '::bootstrap', "★ $app");
		// tell the module to bootstrap
		$this->getApp($app)->setup();
		\Neon::endProfileTickerBomb($profiletoken, "★ $app", 0.050);
	}

	/**
	 * Loads an individual app.
	 * This performs the function of registering the app with the current Application
	 * It sets up an alias so the App class can be found and registers this with the modules property
	 * @param string $name The app name
	 * @param array $config a configuration array
	 * @throws \Exception when a module name conflicts with a core module name
	 *
	 * @return void
	 */
	public function registerApp($name, $config)
	{
		if (isset($config['enabled']) && $config['enabled'] == false) {
			return;
		}
		// A way of creating aliases to Apps
		// this allows you to use the namespace hub/crm for the classes.
		// but it could live anywhere.
		// for example '@newicon/crm' => '@root/modules/community/crm'
		if (isset($config['alias']) && !empty($config['alias'])) {
			$this->setAliases($config['alias']);
		}

		//  make sure there is no name clash with a core module
		if (array_key_exists($name, $this->modules)) {
			throw new \Exception('A core module exists with this name, "' . $name . '" and cannot be overridden');
		}
		$this->setModule($name, $config);

		// set up any alternatives for this
		if (isset($config['appAliases']))
			$this->setModuleAliases($name, $config['appAliases']);
	}

	/**
	 * Get a neon app
	 * alternative syntax for @see getApp()
	 *
	 * @alias getApp
	 * @param string $name the unique app name
	 * @return \neon\core\BaseApp | null
	 */
	public function app($name)
	{
		return $this->getApp($name);
	}

	/**
	 * Get a neon app
	 * example usage:
	 * ```
	 * $userApp = neon()->getApp('user');
	 * ```
	 * @param string $name the unique app name
	 * @return \neon\core\BaseApp | null
	 */
	public function getApp($name)
	{
		if ($name === 'neon') return $this;
		$module = $this->getModuleFromName($name);
		return $this->getModule($module);
	}

	/**
	 * If an app exists
	 * @param string $name
	 * @return boolean
	 */
	public function hasApp($name)
	{
		return $this->hasModule($name);
	}

	/**
	 * Return an array of neon Apps
	 * This gets all core and user defined apps
	 *
	 * @return \neon\core\BaseApp[] an array of App objects indexed by their name
	 */
	public function getActiveApps()
	{
		$apps = array_merge($this->getCoreApps(), $this->getApps());
		$ret = [];
		foreach ($apps as $app => $config) {
			$appObject = $this->getApp($app);
			// apps that are disabled will return null
			if ($appObject !== null)
				$ret[$app] = $this->getApp($app);
		}
		return $ret;
	}

	/**
	 * order a set of apps by their requires so we can install them correctly
	 * @param array $apps  the set of apps
	 * @param array $ordered  the ordered set of apps
	 * @param array $read  the set of already read apps (to prevent circular references)
	 *
	 * @return void
	 */
	public function orderAppsByRequired($apps, &$ordered = null, &$read = null)
	{
		static $initialApps = null;
		if ($ordered == null)
			$initialApps = $apps;
		$ordered = $ordered == null ? [] : $ordered;
		$read = $read == null ? [] : $read;
		foreach ($apps as $key => $app) {
			$read[$key] = true;
			if (!empty($app['requires'])) {
				$requires = explode(',', $app['requires']);
				$required = [];
				foreach ($requires as $r) {
					$r = trim($r);
					// prevent circular references breaking the system
					if (!array_key_exists($r, $read))
						$required[$r] = $initialApps[$r];
				}
				if (count($required)) {
					$this->orderAppsByRequired($required, $ordered, $read);
				}
			}
			if (!array_key_exists($key, $ordered))
				$ordered[$key] = $app;
		}
	}

	/**
	 * Gets the main neon database that holds all accounts and tenant information
	 * @return null|object
	 */
	public function getNeonDb()
	{
		return $this->get('db');
	}

	/**
	 * Get the alias key to this module (App)
	 * The alias sets up the autoloading
	 *
	 * @return mixed
	 */
	public function getClassAlias()
	{
		return '@' . str_replace(['\\', 'Application'], ['/', ''], get_called_class());
	}

	/**
	 * An alias function to \Neon::getAlias()
	 * @see \Neon::getAlias
	 * @param string $alias
	 * @param bool $throwException whether to throw an exception
	 *
	 * @return bool|string
	 */
	public function getAlias($alias, $throwException = true)
	{
		return \Neon::getAlias($alias, $throwException);
	}

	/**
	 * Whether the app is in dev mode
	 *
	 * @return boolean
	 */
	public function isDevMode()
	{
		return $this->env == 'dev';
	}

	/**
	 * The install specific to the core apps (admin, user, settings, etc)
	 * @return void
	 */
	public function installCore()
	{
		foreach ($this->getCoreApps() as $appName => $config) {
			$app = $this->getModule($appName);
			$app->install();
		}
	}

	/**
	 * Run the install function of each app
	 * @return void
	 */
	public function installApps()
	{
		foreach ($this->getApps() as $appName => $config) {
			$app = $this->getApp($appName);
			$app->install();
		}
	}

	/**
	 * Install the core and all registered apps
	 * @return void
	 */
	public function install()
	{
		$this->installCore();
		$this->installApps();
	}

	/**
	 * @return string neon version number
	 */
	public function getVersion()
	{
		return \Neon::getVersion();
	}

	/**
	 * @return string yii version number
	 */
	public function getYiiVersion()
	{
		return \Neon::getYiiVersion();
	}

	/**
	 * Return the total execution time
	 *
	 * @return float
	 */
	public function getExecutionTime()
	{
		return microtime(true) - NEON_START;
	}

	/**
	 * Get an array of available neon version numbers
	 * @return array of version number strings - from oldest to newest
	 */
	public function getNeonVersions()
	{
		return [];
		// Was calling neon to get versions
		// $url = 'https://neon.newicon.net/api/versions/';
	}

	/**
	 * Get an array of version numbers greater than the current version number
	 * ordered from oldest to newest. Latest neon version is end(neon()->getNeonVersionsNewer())
	 * @return array - empty array if there are no available neon version upgrades
	 */
	public function getNeonVersionsNewer()
	{
		$versions = $this->getNeonVersions();
		$newerVersion = [];
		$neonVersion = neon()->getVersion();
		foreach ($versions as $v) {
			if (version_compare($v, $neonVersion, '>')) {
				$newerVersion[] = $v;
			}
		}
		return $newerVersion;
	}

	/**
	 * Get the latest neon version available
	 * @return string|false the version string or false on failure
	 */
	public function getNeonVersionLatest()
	{
		$versions = $this->getNeonVersions();
		return end($versions);
	}

	public function isLatestNeonVersion()
	{
		$versions = $this->getNeonVersions();
		return empty($versions);
	}

	/**
	 * Set of aliases to modules either for backward compatibility or
	 * for convenient short cuts.
	 */
	private static $_moduleAliases;

	/**
	 * Set up the aliases to a particular module
	 * @param string $name  the name of the app
	 * @param array $aliases  any aliases for the app
	 */
	private function setModuleAliases($name, $aliases)
	{
		foreach ($aliases as $alias)
			static::$_moduleAliases[$alias] = $name;
	}

	/**
	 * Returns the name for the app. If the alias is the name it returns that
	 * otherwise it converts the alias to the actual name
	 * @param string $alias  the name to check
	 * @return string  the app name
	 */
	private function getModuleFromName($name)
	{
		return (isset(static::$_moduleAliases[$name]) ? static::$_moduleAliases[$name] : $name);
	}

	/**
	 * Loop through each app and get all php classes within the app in a particular
	 * folder or/and implementing a particular class/interface
	 * @param string $subPath - search within a app subdirectory [default=''] meaning all directories
	 * @param string $instanceOf - a string defining a class or interface that the classes must implement or be
	 * @return array indexed by the app name to a list of valid classes
	 */
	public function getAppClasses($subPath = '', $instanceOf = false)
	{
		profile_begin('neon:getAppClasses');
		foreach (neon()->getAllApps() as $key => $config) {
			$app = neon()->getApp($key);
			// disabled apps wil return null
			if ($app === null) continue;
			$classes[$key] = $app->getClasses('form/fields', $instanceOf);
		}
		profile_end('neon:getAppClasses');
		return $classes;
	}
}
