<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/hub/framework/license/
 * @date 19/12/2015 13:31
 * @author newicon.net
 */
namespace neon\core\console;

use Neon;
use neon\core\db\Migrator;
use Yii;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Class MigrateController
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\console\controllers
 * @property \neon\core\db\Migrator $migrator
 */
class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * @inheritdoc
     */
    public $templateFile = '@neon/core/views/migration.php';

	/**
	 * @var string Define the app that is responsible for this migration.
	 * This will point the console app to the migrations in the directory [app path]/migrations
	 * This is essentially a short hand for setting the --migrationPath to [app path]/migrations explicity
	 * example useage: ./neon migrate/create --app=user
	 */
	public $app;

	/**
	 * @inheritdoc
	 */
	public function options($actionID)
	{
		return array_merge(
			parent::options($actionID),
			['app'] // global for all actions
		);
	}

	protected $_migrator = null;

	/**
	 * @return \neon\core\db\Migrator
	 */
	public function getMigrator()
	{
		if ($this->_migrator === null)
			$this->_migrator = new Migrator;
		return $this->_migrator;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$continue = parent::beforeAction($action);
		if ($continue) {
			if ($this->migrationPath !== null) {
				$this->getMigrator()->migrationPath = $this->migrationPath;
			}
			if ($this->app !== null) {
				$this->getMigrator()->app = $this->app;
				// set the migration path from the app
				$app = neon($this->app);
				if ($app === null) {
					throw new Exception('No app exists with id "'.$this->app.'"');
				}
				$this->getMigrator()->migrationPath = $app->getClassAlias() . '/migrations';
				$this->migrationPath = $this->getMigrator()->migrationPath;
			}
			return true;
		}
		return false;
	}

	/**
	 * Create a new migration file specifying the name and the app
	 * @param string $name The migration class name
	 * @param string $app The app name this migration is for. e.g. 'user'
	 * @throws Exception
	 */
	public function actionGenerate($name, $app)
	{
		$this->createMigrationFile($name, $app);
	}

	/**
	 * Creates a new migration instance - you must use --app=name to specify the app name
	 * @param string $name the migration class name
	 * @throws Exception
	 * @return \yii\db\Migration the migration instance
	 */
	public function actionCreate($name)
	{
		if (!$this->app) {
			throw new Exception('You must specify the app this migration is for. For e.g. --app=core');
		}
		$this->createMigrationFile($name, $this->app);
	}

	/**
	 * Create a new migration file
	 * @param string $name The migration class name
	 * @param string $app The app name this migration is for. e.g. 'user'
	 * @throws Exception
	 */
	public function createMigrationFile($name, $app)
	{
		if (!preg_match('/^\w+$/', $name)) {
			throw new Exception("The migration name should contain letters, digits and/or underscore characters only.");
		}
		$name = 'm' . gmdate('Ymd_His') . '_' . $app . '_' . $name;
		$file = neon()->getAlias($this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php');
		if ($this->confirm("Create new migration '$file'?")) {
			$content = Yii::$app->getView()->renderFile(Neon::getAlias($this->templateFile), ['className' => $name]);
			FileHelper::createDirectory(dirname($file));
			file_put_contents($file, $content);
			$this->stdout("New migration created successfully.\n", Console::FG_GREEN);
		}
	}

	/**
	 * Returns the migrations that are not applied.
	 * @return array list of new migrations
	 */
	protected function getNewMigrations()
	{
		return $this->migrator->getNewMigrations();
	}

	/**
	 * Upgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	protected function migrateUp($class)
	{
		$this->stdout("*** applying $class\n", Console::FG_YELLOW);
		$start = microtime(true);
		$success = $this->migrator->migrateUp($class);
		$time = microtime(true) - $start;
		if ($success) {
			$this->stdout("*** applied $class (time: " . sprintf("%.3f", $time) . "s)\n\n", Console::FG_GREEN);
			return true;
		} else {
			$this->stdout("*** failed to apply $class (time: " . sprintf("%.3f", $time) . "s)\n\n", Console::FG_RED);
			return false;
		}
	}

	/**
	 * Downgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	protected function migrateDown($class)
	{
		$this->stdout("*** reverting $class\n", Console::FG_YELLOW);
		$start = microtime(true);
		$success = $this->migrator->migrateDown($class);
		if ($success) {
			$time = microtime(true) - $start;
			$this->stdout("*** reverted $class (time: " . sprintf("%.3f", $time) . "s)\n\n", Console::FG_GREEN);
			return true;
		} else {
			$time = microtime(true) - $start;
			$this->stdout("*** failed to revert $class (time: " . sprintf("%.3f", $time) . "s)\n\n", Console::FG_RED);
			return false;
		}
	}

	/**
	 * Creates a new migration instance.
	 * @param string $class the migration class name
	 * @return \yii\db\Migration the migration instance
	 */
	protected function createMigration($class)
	{
		return $this->migrator->createMigration($class);
	}

	/**
	 * Migrates to the specified apply time in the past.
	 * @param integer $time UNIX timestamp value.
	 */
	protected function migrateToTime($time)
	{
		$this->migrator->migrateToTime($time);
	}

	/**
	 * Migrates to the certain version.
	 * @param string $version name in the full format.
	 * @return integer CLI exit code
	 * @throws Exception if the provided version cannot be found.
	 */
	protected function migrateToVersion($version)
	{
		return $this->migrator->migrateToVersion($version);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMigrationHistory($limit)
	{
		return $this->migrator->getMigrationHistory($limit);
	}

	/**
	 * Creates the migration history table.
	 */
	protected function createMigrationHistoryTable()
	{
		$this->migrator->createMigrationHistoryTable();
	}

	/**
	 * @inheritdoc
	 */
	protected function addMigrationHistory($version)
	{
		$this->migrator->addMigrationHistory($version);
	}

	/**
	 * @inheritdoc
	 */
	protected function removeMigrationHistory($version)
	{
		$this->migrator->removeMigrationHistory($version);
	}
}
