<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core;

use neon\install\helpers\InstallHelper;
use yii\log\Logger;


/**
 * The main Application.
 * This also manages Apps
 * Neon provides access to core apps and application apps.
 * The core apps are defined in the property coreApps
 * There are two steps to loading an App
 *
 * 1. set
 * ------
 *
 * The setting of an app simply means adding it to the underlying modules property on this class
 * and setting an alias that correctly maps the namespace to the class location for autoloading.
 * This mapping is defined by the App configuration itself via its alias property.
 * At this point the App will not be called and the root App class will not be instantiated.
 * Neon finds community apps through its $apps property.
 * Uninstalling and installing Apps is really the process of managing this setting and unsetting.
 * Once an App is removed from the $apps property it will not exist as far as neon is concerned.
 *
 * 2. bootstrap
 * ------------
 *
 * This process instantiates the App and runs its bootstrap function
 * There is also the need to install an App when it is first loaded and uninstall after
 * The installation process is the process of creating the corresponding database needs for the App.
 * This is not managed as part of the bootup process.
 *
 * @property string $basePath The root directory of the application.
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime" directory under [[basePath]].
 * @property string $systemPath The directory that stores application specific files required by neon. Defaults to the "system" directory under [[basePath]].
 * @property string $timeZone The time zone used by this application.
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * @property string $vendorPath The directory that stores vendor files. Defaults to "vendor" directory under [[basePath]].
 *
 * Services:
 * @property \neon\core\web\AssetManager                      $assetManager    - The asset manager application component. This property is read-only.
 * @property \yii\rbac\ManagerInterface                 $authManager     - The auth manager application component.
 *                                                                         Null is returned if auth manager is not configured. This property is read-only.
 * @property \yii\base\Security                         $security        - The security application component. This property is read-only.
 * @property \neon\core\web\View                        $view            - The view application component that is used to render
 *                                                                         various view files. This property is read-only.
 * @property \neon\core\web\UrlManager                        $urlManager      - The URL manager for this application. This property is read-only.
 * @property \yii\caching\Cache                         $cache           - The cache application component. Null if the component is not
 *                                                                         enabled. This property is read-only.
 * @property \yii\caching\ArrayCache                    $cacheArray      - A PHP in memory cache - caches things inside a php array specific to the current request.
 * @property \neon\core\db\Connection                   $db              - The database connection. This property is read-only.
 * @property \yii\web\ErrorHandler                      $errorHandler    - The error handler application component. This property is read-only.
 * @property \neon\core\services\Formatter                        $formatter       - The formatter application component. This property is read-only.
 * @property \yii\i18n\I18N                             $i18n            - The internationalization application component. This property is read-only.
 * @property \yii\log\Dispatcher                        $log             - The log dispatcher application component. This property is read-only.
 * @property \neon\core\mail\Mailer                  $mailer          - The mailer application component. This property is read-only.
 * @property \neon\core\web\Request                     $request         - The request component. This property is read-only.
 * @property \neon\core\web\Response                    $response        - The response component. This property is read-only.
 * @property \neon\settings\interfaces\ISettingsManager   $settingsManager - Get the settings manager
 * @property \neon\user\services\User                   $user            - The user component. This property is read-only.
 *
 * Magic methods
 * @method \neon\user\services\User getUser()
 *
 * App provider properties:
 * @property  \neon\dev\App       $dev
 * @property  \neon\daedalus\App       $dds
 * @property  \neon\settings\App  $settings
 * @property  \neon\phoebe\App    $phoebe
 * @property  \neon\firefly\App   $firefly
 * @property  \neon\cms\App       $cms
 */
class ApplicationWeb extends \yii\web\Application
{
	/**
	 * @event Event an event raised  after all neon apps have been bootstrapped
	 */
	const EVENT_AFTER_NEON_BOOTSTRAP = 'afterNeonBootstrap';

	const EVENT_APP_END = 'AppEnd';

	use ApplicationTrait;

	/**
	 * !IMPORTANT!
	 * Whether the application supports multiple tenants
	 * Neon creates database for individual accounts to support multi tenancy
	 * @var bool
	 */
	public $multiTenant = true;

	public $masterDomain = 'localhost';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->isInstalled() && neon()->dev)
			neon()->dev->debugBootstrap();
	}

	/**
	 * Pre initialization
	 * amend the config before it is processed
	 * This will merge the common, and the environment configuration together
	 * This runs before the bootstrap function
	 * @inheritdoc
	 */
	public function preInit(&$config)
	{
		$this->resolveConfig($config);
		parent::preInit($config);
	}

	/**
	 * @inheritdoc
	 */
	public function handleRequest($request)
	{
		// Install check middleware
		// ------------------------
		// Redirects to the install process if not installed
		// If we are not on an install route and we are not currently trying to display an error.
		// Then we want to redirect to the install process.
		if (!$this->isInstalled() && !InstallHelper::isInstallRoute() && !InstallHelper::isErrorRoute()) {
			// We cannot do a simple redirect as browsers (especially chrome) will cache the result
			// And you will wonder why the home page always redirects back to the install page
			// And you will comment out virtually every line in the entire code base
			// You will even break point line by line and painfully execute through a request process. That will all work.
			// You will waste life.
			// Then you will shake cursed fist at browser overlords.
			// In short. Don't redirect for this.
			$this->catchAll = InstallHelper::installRoute();
			return parent::handleRequest($request);
		}

		// Secure SSL check middleware
		// ---------------------------
		// Redirects to a secure url if sslOnly setting has been set
		if ($this->isInstalled() && !$request->isSecureConnection && !neon()->isDevMode() && setting('admin', 'sslOnly')) {
			//otherwise redirect to same url with https
			$secureUrl = str_replace('http', 'https', $request->absoluteUrl);
			//use 301 for a permanent redirect
			return neon()->getResponse()->redirect($secureUrl, 301);
		}

		return parent::handleRequest($request);
	}

	/**
	 * @inheritdoc
	 */
	protected function bootstrap()
	{
		parent::bootstrap();

		// Register the core apps
		$this->registerApps($this->getCoreApps());
		// Check neon is installed
		if (! $this->isInstalled() || InstallHelper::isInstallRoute()) {
			// We can not bootstrap the apps as they can not rely on a database connection
			// so conclude the bootstrap process
			return;
		}

		\Neon::beginProfile('Application::bootstrapCoreApps', 'neon\Application');
		$this->bootstrapApps($this->getCoreApps());
		\Neon::endProfile('Application::bootstrapCoreApps', 'neon\Application');
		// raise an event - this is useful for components created early to ensure all neon apps have booted
		$this->trigger(self::EVENT_AFTER_NEON_BOOTSTRAP);
	}

	/**
	 * Dev module documentation path
	 * @return string
	 */
	public function getDocsPath()
	{
		return __DIR__ . '/docs';
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$result = parent::run();
		define('NEON_STOP', microtime(true));
		$this->trigger(self::EVENT_APP_END);
		return $result;
	}

	/**
	 * Get the tenant Db
	 * Returns the database connection component.
	 * @return \yii\db\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->get('accountManager')->getTenantDb();
	}

	/**
	 * Per session is installed cache
	 * @var null|bool
	 */
	private $_isInstalled = null;

	/**
	 * Whether neon is installed
	 * @return boolean
	 */
	public function isInstalled()
	{
		if ($this->_isInstalled === null) {
			// If we are installed then the database environment variables should be set
			// Or we should have a local config file
			$this->_isInstalled = InstallHelper::hasLocalConfigFile();
		}
		return $this->_isInstalled;
	}

	/**
	 * If the current Neon::$app (\Yii::$app) instance is a web app
	 * @return bool
	 */
	public function isWebApp()
	{
		return true;
	}

	/**
	 * If the current Neon::$app (\Yii::$app) instance is a console app
	 * @return bool
	 */
	public function isConsoleApp()
	{
		return false;
	}

	/**
	 * Perhaps this should be in a generic view / page object
	 * @return string
	 */
	public function powered()
	{
		return \Neon::powered();
	}

	/**
	 * This returns the registered logger - use for actually sending log messages
	 * The getLog function also available returns the Log Dispatcher which is responsible for managing the various log targets
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->getLog()->getLogger();
	}

	/**
	 * Returns the configuration of core application components.
	 * @see set()
	 */
	public function coreComponents()
	{
		return [
			'accountManager' => ['class' => 'neon\core\services\accountManager\AccountManager'],
			'request' =>        ['class' => 'neon\core\web\Request'],
			'response' =>       ['class' => 'neon\core\web\Response'],
			'log' =>            ['class' => 'yii\log\Dispatcher'],
			'view' =>           ['class' => 'yii\web\View'],
			'formatter' =>      ['class' => 'yii\i18n\Formatter'],
			'i18n' =>           ['class' => 'yii\i18n\I18N'],
			'mailer' =>         ['class' => 'yii\swiftmailer\Mailer'],
			'urlManager' =>     ['class' => 'yii\web\UrlManager'],
			'assetManager' =>   ['class' => 'yii\web\AssetManager'],
			'security' =>       ['class' => 'yii\base\Security'],
			'session' =>        ['class' => 'yii\web\Session'],
			'user' =>           ['class' => 'yii\web\User'],
			'errorHandler' =>   ['class' => 'neon\core\web\ErrorHandler'],
		];
	}
}
