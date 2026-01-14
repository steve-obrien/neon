<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 23/12/2015
 * Time: 10:40
 */
namespace neon\core\db;

use \Neon;
use \yii\db\Query as YiiQuery;
use \yii\helpers\ArrayHelper;
use yii\console\Exception;
use \neon\core\helpers\Console;
use yii\helpers\FileHelper;


/**
 * Class to emulate Yii's migration functionality
 * without the direct output of console messages
 */
class Migrator extends \yii\base\Component
{
	/**
	 * @var base migration name
	 */
	const BASE_MIGRATION = 'm000000_000000_base';

	/**
	 * Name of the migration table
	 * @var string
	 */
	public $migrationTable = '{{%migration}}';

	/**
	 * When specified certain functions become app specific
	 * @var string
	 */
	public $app;

	/**
	 * @var string
	 */
	public $migrationPath;

	/**
	 * @return \yii\db\Connection
	 */
	public function getDb()
	{
		return Neon::$app->getDb();
	}

	/**
	 * Run migrations in date order for a specific app only
	 * @param string $appName
	 * @throws \Exception
	 */
	public function runMigrationsForApp($appName)
	{
		$migrations = $this->getNewMigrationsForApp($appName);
		$this->runMigrations($migrations);
	}

	/**
	 * Will try to run all available migrations in date order
	 * @throws \Exception
	 */
	public function runAllMigrations()
	{
		$this->runMigrations($this->getNewMigrations());
	}

	/**
	 * Run migrations using a particular alias
	 * @param string $alias - an alias path like @root/system/migrations
	 * @throws \Exception
	 */
	public function runMigrationsForAlias($alias)
	{
		if (strpos($alias, '@') === false) {
			throw new \Exception('You must specify an alias path. Alias paths begin with @');
		}
		$migrations = $this->getNewMigrationsForAlias($alias);
		$this->runMigrations($migrations);
	}

	/**
	 * runs an array of migrations
	 * @param array $migrations - array of migration class name strings
	 * @return bool
	 * @throws \Exception
	 */
	public function runMigrations($migrations)
	{
		if (empty($migrations)) {
			// no migrations to run
			return true;
		}
		foreach ($migrations as $migration) {
			if (!$this->migrateUp($migration)) {
				throw new \Exception('Migration failed : ' . $migration);
			}
		}
	}

	/**
	 * Returns the migrations that are not applied.
	 * @param  string  $appName - the app to run migrations for
	 * @throws \Exception if the $app does not exist
	 * @return array list of new migrations
	 */
	public function getNewMigrationsForApp($appName)
	{
		$app = Neon::$app->getApp($appName);
		if ($app === null)
			throw new \Exception('No app exists with id "'.$appName.'"');
		$alias = $app->getClassAlias().'/migrations';
		$migrationAlias = $app->getMigrationAlias().'/migrations';
		$applied = [];
		foreach ($this->getMigrationHistory(null) as $version => $time) {
			$applied[$version] = true;
		}
		$appMigrations = $this->getNewMigrationsInPath($alias, $applied, $migrationAlias);
		$systemMigrations = $this->getNewMigrationsInPath("@system/{$appName}/migrations", $applied);
		$migrations = array_merge($appMigrations, $systemMigrations);
		return $this->sortMigrationsByDate($migrations);
	}

	/**
	 * Find all migrations in a specified path
	 * @param string $pathAlias  the path alias to look in for migrations
	 * @param string $migrationAlias  the alias in the migrations table
	 * @param array $applied  the set of already applied migrations
	 * @return array   the set of new migrations to be run
	 */
	private function getNewMigrationsInPath($pathAlias, $applied, $migrationAlias=null)
	{
		if ($migrationAlias === null)
			$migrationAlias = $pathAlias;
		$migrations = [];
		$migrationPath = Neon::getAlias($pathAlias);
		if (file_exists($migrationPath)) {
			$handle = opendir($migrationPath);
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..' || is_dir($file) || pathinfo($file, PATHINFO_EXTENSION)!='php') {
					continue;
				}
				$path = $migrationPath . DIRECTORY_SEPARATOR . $file;
				$migrationName = $migrationAlias . '/' . str_replace('.php', '', $file);
				if (is_file($path) && !isset($applied[$migrationName])) {
					$migrations[] = $migrationName;
				}
			}
			closedir($handle);
		}
		return $migrations;
	}

	/**
	 * Get all new migrations for all current apps
	 * @return array
	 */
	public function getNewMigrations()
	{
		$apps = [];
		if ($this->app !== null) {
			$apps = [$this->app => []];
		} else {
			$apps = neon()->getActiveApps();
		}
		$migrations = [];
		foreach ($apps as $app => $appConfig) {
			$migrations = array_merge($migrations, $this->getNewMigrationsForApp($app));
		}
		return $this->sortMigrationsByDate($migrations);
	}

	/**
	 * Upgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	public function migrateUp($class)
	{
		if ($class === self::BASE_MIGRATION) {
			return true;
		}
		$migration = $this->createMigration($class);
		if ($migration->up() !== false) {
			$this->addMigrationHistory($class);
			$this->refreshSchema();
			return true;
		} else {
			$this->logError("*** failed to apply migration up in $class", 'migration');
			return false;
		}
	}

	/**
	 * Downgrades with the specified migration class.
	 * @param string $class the migration class name
	 * @return boolean whether the migration is successful
	 */
	public function migrateDown($class)
	{
		if ($class === self::BASE_MIGRATION) {
			return true;
		}
		$migration = $this->createMigration($class);
		if ($migration->down() !== false) {
			$this->removeMigrationHistory($class);
			return true;
		} else {
			$this->logError("*** failed to apply migration down in $class");
			return false;
		}
	}

	/**
	 * Creates a new migration instance.
	 * @param string $class the migration class name
	 * @return \yii\db\Migration the migration instance
	 */
	public function createMigration($class)
	{
		// load in the file
		$path = Neon::getAlias($class.'.php');
		require_once($path);
		$class = str_replace('.php', '', basename($path));
		// instantiate the class
		return new $class(['db' => $this->db]);
	}

	/**
	 * Store a migration executed elsewhere in the migration table
	 * @param string $class  the migration class name
	 */
	public function storeExternallyExecutedMigration($class)
	{
		$this->addMigrationHistory($class);
	}

	/**
	 * Migrates to the specified apply time in the past.
	 * @param integer $time UNIX timestamp value.
	 */
	public function migrateToTime($time)
	{
		$count = 0;
		$migrations = array_values($this->getMigrationHistory(null));
		while ($count < count($migrations) && $migrations[$count] > $time) {
			++$count;
		}
		if ($count === 0) {
			$this->stdout("Nothing needs to be done.\n", Console::FG_GREEN);
		} else {
			$this->actionDown($count);
		}
	}

	/**
	 * Migrates to the certain version.
	 * @param string $version name in the full format.
	 * @return integer CLI exit code
	 * @throws \Exception if the provided version cannot be found.
	 */
	public function migrateToVersion($version)
	{
		$originalVersion = $version;

		// try migrate up
		$migrations = $this->getNewMigrations();
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				$this->actionUp($i + 1);

				return self::EXIT_CODE_NORMAL;
			}
		}

		// try migrate down
		$migrations = array_keys($this->getMigrationHistory(null));
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				if ($i === 0) {
					$this->stdout("Already at '$originalVersion'. Nothing needs to be done.\n", Console::FG_YELLOW);
				} else {
					$this->actionDown($i);
				}

				return self::EXIT_CODE_NORMAL;
			}
		}

		throw new \Exception("Unable to find the version '$originalVersion'.");
	}

	/**
	 * get the all the migration history or for a particular app
	 */
	public function getMigrationHistory($limit)
	{
		if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
			$this->createMigrationHistoryTable();
		}
		$query = (new YiiQuery)->select(['version', 'apply_time'])
			->from($this->migrationTable)
			->orderBy('apply_time DESC, version DESC')
			->limit($limit);
		if ($this->app) {
			$migrationAlias = $this->app->migrationAlias;
			$query->where(['like', 'version', "$migrationAlias/"]);
		}
		$rows = $query->createCommand($this->db)->queryAll();
		$history = ArrayHelper::map($rows, 'version', 'apply_time');
		unset($history[self::BASE_MIGRATION]);

		return $history;
	}

	/**
	 * Creates the migration history table.
	 */
	public function createMigrationHistoryTable()
	{
		$tableName = $this->db->schema->getRawTableName($this->migrationTable);
		$this->db->createCommand()->createTable($this->migrationTable, [
			'version' => 'VARCHAR(180) NOT NULL PRIMARY KEY',
			'apply_time' => 'DOUBLE(16,4)',
		])->execute();
		$this->db->createCommand()->insert($this->migrationTable, [
			'version' => self::BASE_MIGRATION,
			'apply_time' => microtime(true),
		])->execute();
	}

	/**
	 * @inheritdoc
	 */
	public function stdout($message, $color='')
	{
		echo $message;
	}

	/**
	 * @inheritdoc
	 */
	protected function addMigrationHistory($version)
	{
		$command = $this->db->createCommand();
		$command->insert($this->migrationTable, [
			'version' => $version,
			'apply_time' => microtime(true),
		])->execute();
	}

	/**
	 * @inheritdoc
	 */
	protected function removeMigrationHistory($version)
	{
		$command = $this->db->createCommand();
		$command->delete($this->migrationTable, [
			'version' => $version,
		])->execute();
	}

	/**
	 * Log an error
	 * @param $message
	 */
	public function logError($message)
	{
		\Neon::error($message, 'migration');
	}

	/**
	 * Log a message
	 * @param  string  $message
	 * @param  string  $type - can be one of 'info' | 'error' | 'danger' | 'warning' | 'success'
	 * @return string / stream
	 */
	public function log($message, $type='info')
	{
		$colors = [
			'info' => Console::FG_BLUE, 'warning' => Console::FG_YELLOW,
			'error' => Console::FG_RED, 'danger' => Console::FG_RED,
			'success' => Console::FG_GREEN,
		];
		$prefixes = [
			'info' => 'Info: ', 'warning' => '(!) - ',
			'error' => 'Error: ', 'danger' => '(!) - ',
			'success' => 'Success: ',
		];
		if (neon()->isConsoleApp()) {
			if (Console::streamSupportsAnsiColors(\STDOUT)) {
				$color = isset($colors[$type]) ? $colors[$type] : [];
				$string = Console::ansiFormat($message, [$color]);
			} else {
				$prefix = isset($prefixes[$type]) ? $prefixes[$type] : '';
				$string = $prefix.$message;
			}
			return Console::stdout("$string\n");
		}

		else {
			return $message;
		}
	}

	/**
	 * Function to refresh the schema caching.
	 * @return bool|int
	 */
	public function refreshSchema()
	{
		$this->db->schema->refresh();
	}

	/**
	 * Sort migrations by date order taking into account differences in
	 * format (e.g. YYYY vs YY) and some mistakes in handwritten ones.
	 * @param array $migrations  the set of migrations to be ordered as
	 *   [] => migrationPathAndName
	 * @return an array of migrations in the correct date order
	 */
	private function sortMigrationsByDate($migrations)
	{
		$sortableMigrations = [];
		foreach ($migrations as $migration) {
			// remove any paths to get the migration itself
			$migrationPart = $migration;
			if (strpos($migration,'/')!==false)
				$migrationPart = substr($migration, strrpos($migration,'/')+1);

			$parts = explode('_', substr($migrationPart,1)); // remove initial m

			// check it was a migration and has enough parts
			if (count($parts) == 1) {
				if (!is_numeric($parts[0]))
					continue;
				$date = $parts[0];
				$time = '000000';
			} else {
				$date = $parts[0];
				$time = $parts[1];
			}

			// some handwritten migrations have bad datetimes
			// capture case where no time has been set or is actually a date
			if (!is_numeric($time) || strlen($time) != 6)
				$time = '000000';
			// some datetimes are YYYYMMDD and others YYMMDD. Canonicalise these
			if (strlen($date)==6)
				$date = "20$date";
			// turn this into 'YYYY-MM-DD HH:MM:SS' and then into a timestamp
			$dateTime = substr($date,0,4)."-".substr($date,4,2)."-".substr($date,6,2)." ".substr($time,0,2).":".substr($time,2,2).":".substr($time,4,2);
			$timestamp = strtotime($dateTime);
			if ($timestamp == false)
				throw new \Exception("Invalid datetime in migration $migration. Date(yyyymmdd)=$date and time(hhmmss)=$time. Please fix.");
			// beware migrations with same timestamp
			$sortableMigrations[$timestamp][] = $migration;
		}
		ksort($sortableMigrations);
		$sortedMigrations = [];
		foreach ($sortableMigrations as $mgs) {
			foreach ($mgs as $mig)
				$sortedMigrations[] = $mig;
		}
		return $sortedMigrations;
	}

}
