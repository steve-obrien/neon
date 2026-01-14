<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core;
use neon\core\helpers\Str;
use neon\core\interfaces\IDataMapProvider;

/**
 * The base abstract class for Neon apps
 */
abstract class BaseApp extends \yii\base\Module
implements
	AppInterface,
	interfaces\IDataMapProviderFinder
{
	public $enabled = true;
	/**
	 * @var string the defaultRoute for controllers set to index will load indexController
	 */
	public $defaultRoute = 'index';

	/**
	 * @var array of required app keys
	 * this enables apps to be loaded in the correct order which is useful during installation
	 * .e.g ['dds', 'firefly']
	 */
	public $requires = [];

	/**
	 * @var array an alias array this sets up the alias for the app
	 * An example value loading the admin app, you can also add additional aliases if you wish
	 * ~~~php
	 * [
	 *	 '@neon/admin' => '@neon/apps/admin'
	 * ],
	 * ~~~
	 */
	public $alias = [];

	/**
	 * Alternative ways of referring to this app
	 * @var array
	 */
	public $appAliases = [];

	/**
	 * @var string  an alternative name for the alias used for migrations if
	 * different to the @see getClassAlias. This is useful if you change the
	 * app naming but want to keep old migrations running the same
	 */
	protected $_migrationAlias = '';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		// initiate the controllerNamespace property
		// this will set the namespace to be ./controllers for web app
		// and ./commands for console apps
		if ($this->controllerNamespace === null) {
			$class = \get_class($this);
			if (($pos = strrpos($class, '\\')) !== false) {
				$controllers = neon()->isConsoleApp() ? 'console' : 'controllers';
				$this->controllerNamespace = substr($class, 0, $pos) . '\\' . $controllers;
			}
		}
		parent::init();
		$this->configure();
	}

	/**
	 * Override in config with an array to change menu options
	 * for e.g:
	 * ```php
	 * 'menu' => [
	 *     [
	 *         'label' => 'App Forms',
	 *         'order' => 1020,
	 *         'url' => ['/phoebe/appforms/index/index'],
	 *         'visible' => function() { // rules },
	 *     ],
	 * ]
	 * ```
	 * @see getMenu
	 * @see setMenu
	 * @var null
	 */
	protected $_menu = null;

	/**
	 * Enable default phoebe menu to be overridden
	 * @param $menu
	 */
	public function setMenu($menu)
	{
		$this->_menu = $menu;
	}

	/**
	 * @inheritdoc
	 */
	public function getMenu()
	{
		// if the menu has been overridden
		if ($this->_menu !== null)
			return $this->_menu;
		return $this->getDefaultMenu();
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{}

	/**
	 * @inheritdoc
	 */
	public function configure()
	{}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [];
	}

	/**
	 * Convince function to ```Neon::app('settings')->settingsManager->get($this->id, ...)```
	 *
	 * @param string $name
	 * @param string|null $default - default null
	 * @param boolean $refresh - default false
	 * @return type
	 */
	public function getSetting($name, $default=null, $refresh=false)
	{
		$settingManager = neon()->settingsManager;
		return $settingManager->get($this->id, $name, $default, $refresh);
	}

	/**
	 * Convince function to ```Neon::app('settings')->settingsManager->set($this->id, ...)```
	 *
	 * @param string $name
	 * @param string $value
	 * @return boolean success / fail
	 */
	public function setSetting($name, $value)
	{
		$settingManager = neon()->settingsManager;
		return $settingManager->set($this->id, $name, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return ucwords($this->id);
	}

	/**
	 * Implements the install interface.
	 * This by default will run all migrations defined in the apps `migrations` folder
	 * To provide additional install steps override this method
	 */
	public function install()
	{
		$this->runMigrations();
	}

	/**
	 * @inheritdoc
	 */
	public function uninstall(){}

	/**
	 * Get the alias key to this module (App)
	 * The alias sets up the autoloading
	 * e.g. @neon/user
	 * @return string
	 */
	public function getClassAlias()
	{
		return '@' . rtrim(str_replace(['\\', 'App'], ['/', ''], get_called_class()), "/");
	}

	/**
	 * Get the migration prefix key to this module (App) for use in migrations.
	 * Because databases will store historical information about an apps migrations
	 * a change in name of the corresponding class will cause migrations to become
	 * lost. This, in conjunction with $this->migrationAlias allows the class
	 * to change but the migrations to remain the same.
	 * @return string
	 */
	public function getMigrationAlias()
	{
		$alias = $this->getClassAlias();
		if (!empty($this->_migrationAlias)) {
			$alias = dirname($alias).'/'.$this->_migrationAlias;
		}
		return $alias;
	}

	/**
	 * Get the path to app directory
	 *
	 * @return bool|string
	 */
	public function getPath()
	{
		return neon()->getAlias($this->getClassAlias());
	}

	/**
	 * Get the assets path for this app
	 *
	 * @return string
	 */
	public function getAssetsPath()
	{
		return $this->getPath() . '/assets';
	}

	/**
	 * @return string
	 */
	public function getDocsPath()
	{
		return $this->getPath() . '/docs';
	}

	/**
	 * Get the url to the published assets
	 * - if assets are not published it will trigger publishing of the assets
	 *
	 * @see $this->getAssetsPath()
	 * @throws \yii\base\InvalidArgumentException if the asset to be published does not exist.
	 * @throws \yii\base\InvalidConfigException if the target directory [[basePath]] is not writeable.
	 * @return array the path (directory or file path) and the URL that the asset is published as.
	 */
	public function publishAssets()
	{
		return neon()->assetManager->publish($this->getAssetsPath());
	}

	/** IDataMapProvider **/
	/** ======================= **/

	/**
	 * @inheritdoc
	 */
	public function getDataMapProviders()
	{
		$availableMaps = [];
		foreach(neon()->getAllApps() as $name => $config) {
			$app = neon($name);
			if ($app instanceof IDataMapProvider) {
				$maps = $app->getDataMapTypes();
				if (!empty($maps)) {
					$availableMaps[$name] = [
						'key' => $name,
						'name' => $app->getName(),
						'maps' => $maps
					];
				}
			}
		}
		return $availableMaps;
	}
	/**
	 * @inheritdoc
	 */
	public function getDataMapProvider($name)
	{
		// can be an app or a service provider
		$app = neon($name);
		$provider = $app ? $app : neon()->get($name, false);
		if ($provider === null) {
			throw new \Exception("The map provider '$name' specified can not be found.");
		}
		if (! $provider instanceof IDataMapProvider) {
			throw new \Exception("The Provider '$name' must implement the 'neon\\core\\interfaces\\IDataMapProvider' interface.");
		}
		return $provider;
	}

	private $_migrator = null;

	/**
	 * Returns the migrator object to enable database migrations
	 * @return \neon\core\db\Migrator
	 */
	public function getMigrator()
	{
		if ($this->_migrator === null) {
			$this->_migrator = new \neon\core\db\Migrator();
		}
		return $this->_migrator;
	}

	/**
	 * Run all the migrations for this app.
	 * Will essentially run all migration classes defined in the apps `migrations` folder
	 */
	public function runMigrations()
	{
		$this->getMigrator()->runMigrationsForApp($this->id);
	}

	/**
	 * Return the migrations that need to be run for this app
	 *
	 * @return array
	 */
	public function getNewMigrations()
	{
		return $this->getMigrator()->getNewMigrationsForApp($this->id);
	}

	/**
	 * Get all php classes within the app in a particular folder or/and implementing a particular class/interface
	 *
	 * @param string $subPath - search within a app subdirectory [default=''] meaning all directories
	 * @param string $instanceOf - a string defining a class or interface that the ckasses must implement or be
	 * @return array
	 */
	public function getClasses($subPath='', $instanceOf=false)
	{
		$path = $this->getPath().Str::start($subPath, '/');
		$classes = [];
		if (!file_exists($path)) return [];
		foreach(\neon\core\helpers\File::findFiles("$path", ['only' => ['*.php']]) as $file) {
			$class = $this->getClassFromFile($file);
			// the class is not valid then skip
			if (!$class) continue;
			// if we want to check the instance of the class and the $class is NOT an $instanceOf the class then skip
			if ($instanceOf && !is_a($class, $instanceOf, true)) continue;
			$classes[] = $class;
		}
		return $classes;
	}

	/**
	 * Gets the full class (namespace\class) name that can be autoloaded or false if not valid.
	 *
	 * @param string $file - file path
	 * @return bool|string - the class name of false if not valid
	 */
	public function getClassFromFile($file)
	{
		$alias = $this->getClassAlias();
		$fileName = basename($file);
		if (in_array($fileName, ['Yii.php', 'Neon.php'])) return false;
		if (!ctype_upper($fileName[0]))
			return false;
		$class = str_replace([$this->getPath(), '/', '@', '.php'], ['', '\\', '\\', ''], $alias.$file);
		return $class;
	}
}
