<?php

namespace neon\core;

use yii\console\UnknownCommandException;

/**
 * The main Application.
 * This also manages Apps
 * The hub is made of core apps and community apps.
 * The core apps are defined in the property coreApps
 * There are two steps to loading an App
 *
 * 1. set
 * ------
 *
 * The setting of an app simply means adding it to the underlying modules property on this class
 * and setting an alias that correctly maps the namespace to the class location for autoloading.
 * This mapping is defined by the App configuration istelf via its alias propety.
 * At this point the the App will not be called and the root App class will not be instantiated.
 * The ub finds community apps through its $apps property.
 * Uninstalling and installing Apps is really the process of managing this setting and unsetting.
 * Once an App is removed from the $apps property it will not exist as far as the Hub is concerned.
 *
 * 2. bootstrap
 * ------------
 *
 * This process instatiates the App and runs its bootstrap function
 * There is also the need to install an App when it is first loaded and uninstall after
 * The installation process is the process of creating the coresponding database needs for the App.
 * This is not managed as part of the bootup process.
 *
 */
class ApplicationConsole extends \yii\console\Application
{
	use ApplicationTrait;

	/**
	 * @var string the default route of this application. Defaults to 'help',
	 * meaning the `help` command.
	 */
	public $defaultRoute = 'help';

	public $masterDomain = 'localhost';

	/**
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
	public function bootstrap()
	{
		parent::bootstrap();
		// Register the core apps
		$this->registerApps($this->getCoreApps());
	}

	public function runAction($route, $params = [])
	{
		try {
			parent::runAction($route, $params);
		} catch (UnknownCommandException $e) {
			// if can not find the command - assume its a core command
			try {
				parent::runAction("core/$route", $params);
			} catch (UnknownCommandException $e) {
				throw new UnknownCommandException($route, $this, 0, $e);
			}
		}
	}

	/**
	 * Returns the configuration of the built-in commands.
	 * @return array the configuration of the built-in commands.
	 */
	public function coreCommands()
	{
		//$commands = parent::coreCommands();
//		$commands['migrate'] = '\neon\core\console\controllers\MigrateController';

		return [
			'help' => '\neon\core\console\HelpController',
			// 'test' => '\neon\core\console\TestController',
			// 'shell' => '\neon\core\console\ShellController',
			// 'asset' => 'yii\console\controllers\AssetController',
			// 'cache' => 'yii\console\controllers\CacheController',
			// 'fixture' => 'yii\console\controllers\FixtureController',
			// 'message' => 'yii\console\controllers\MessageController',
			// 'serve' => '\neon\core\console\ServeController',
			// 'migrate' => '\neon\core\console\MigrateController',
			// 'publish' => '\neon\core\console\PublishController',
		];
	}

	/**
	 * Get the tenant Db
	 * Returns the database connection component.
	 * @return \yii\db\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->get('db');
	}

	/**
	 * If the current Neon::$app (\Yii::$app) instance is a web app
	 * @return bool
	 */
	public function isWebApp()
	{
		return false;
	}

	/**
	 * If the current Neon::$app (\Yii::$app) instance is a console app
	 * @return bool
	 */
	public function isConsoleApp()
	{
		return true;
	}

	/**
	 * Returns the configuration of core application components.
	 * @see set()
	 */
	public function coreComponents()
	{
		return [
			'accountManager' => ['class' => 'neon\core\services\accountManager\AccountManager'],
			'log' =>           ['class' => 'yii\log\Dispatcher'],
			'view' =>          ['class' => 'neon\core\web\View'],
			'formatter' =>     ['class' => 'yii\i18n\Formatter'],
			'i18n' =>          ['class' => 'yii\i18n\I18N'],
			'mailer' =>        ['class' => 'yii\swiftmailer\Mailer'],
			'urlManager' =>    ['class' => 'yii\web\UrlManager'],
			'assetManager' =>  ['class' => 'yii\web\AssetManager'],
			'security' =>      ['class' => 'yii\base\Security'],
			'request' =>       ['class' => 'neon\console\Request'],
			'response' =>      ['class' => 'yii\console\Response'],
			'session' =>       ['class' => 'yii\web\Session'],
			'user' =>          ['class' => 'yii\web\User'],
			'errorHandler' =>  ['class' => 'yii\console\ErrorHandler'],
		];
	}

	public function getSession()
	{
		return $this->get('session');
	}

	public function getLogger()
	{
		return $this->getLog()->getLogger();
	}	
}
