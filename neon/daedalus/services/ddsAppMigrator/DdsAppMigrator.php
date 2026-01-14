<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsAppMigrator;

use neon\daedalus\interfaces\IDdsAppMigrator;
use neon\daedalus\services\ddsManager\DdsCore;
use neon\core\helpers\Hash;
use neon\core\db\Migrator;

class DdsAppMigrator extends DdsCore implements IDdsAppMigrator
{
	/**
	 * Aliased path to migrations folder
	 * @var string
	 */
	private $_alias = '';

	/**
	 * path to migrations folder
	 * @var string
	 */
	private $_path = '';

	/**
	 * the full path and name of the output file
	 * @var string
	 */
	private $_file = '';

	/**
	 * the file name
	 * @var type
	 */
	private $_filename = '';

	/**
	 * the migration class name
	 * @var type
	 */
	private $_classname = '';

	/**
	 * whether or not any migrations have been stored in a migration file
	 * @var type
	 */
	private $_hasMigrations = false;

	/**
	 * The set of up migrations to be saved to file
	 * @var array
	 */
	private $_upMigrations = [];

	/**
	 * The set of down migrations to be saved to file
	 * @var array
	 */
	private $_downMigrations = [];


	/**
	 * Construct a DdsAppMigrator
	 * @param string $path  path to store migrations in
	 */
	public function __construct($path)
	{
		$this->_alias = $path;
		$this->_path = neon()->getAlias($path);
	}

	public function __destruct()
	{
		// close the migration file on destruction in case of errors
		$this->closeMigrationFile();
	}

	// ------------------------------------- //
	// ---------- IDdsAppMigrator ---------- //
	// ------------------------------------- //

	/**
	 * @inheritdoc
	 */
	public function openMigrationFile($label)
	{
		// create the migration folder if it doesn't exist for backwards compatibility
		if (!file_exists($this->_path)) {
			mkdir($this->_path, 0755, true);
		}

		if (!empty($this->_file))
			throw new \RuntimeException('Migration Open: You cannot open a new migration without first closing the previous one');

		// clear any migrations that may have been created just in case they were needed
		$this->clearMigrations();

		// and create the file
		return $this->createMigrationFile($label);
	}

	/**
	 * @inheritdoc
	 */
	public function storeMigration($upMigration, $downMigration)
	{
		$id = Hash::uuid64();
		$this->_hasMigrations = true;
		$this->_upMigrations[$id] = $this->prepareMigration($upMigration);
		$this->_downMigrations[$id] = $this->prepareMigration($downMigration);
		return $id;
	}

	/**
	 * @inheritdoc
	 */
	public function removeMigration($id)
	{
		unset($this->_upMigrations[$id]);
		unset($this->_downMigrations[$id]);
	}

	/**
	 * @inheritdoc
	 */
	public function closeMigrationFile($delete=false)
	{
		if (!$this->_file)
			return;
		if ($delete || !$this->_hasMigrations) {
			$this->deleteMigration();
			return;
		}
		// turn on and off foreign key checks around the migrations
		$foreignKeysOff = "SET foreign_key_checks = 0;";
		$foreignKeysOn = "SET foreign_key_checks = 1;";
		// save the up migrations
		$this->saveToFile(PHP_EOL.PHP_EOL."//".PHP_EOL."// Up Migrations".PHP_EOL."//");
		$this->saveMigration('up', $foreignKeysOff);
		foreach ($this->_upMigrations as $up) {
			$this->saveMigration('up', $up);
		}
		$this->saveMigration('up', $foreignKeysOn);

		// save the down migrations
		$this->saveToFile(PHP_EOL.PHP_EOL."//".PHP_EOL."// Down Migrations".PHP_EOL."//");
		$this->saveMigration('down', $foreignKeysOff);
		foreach (array_reverse($this->_downMigrations) as $down)
			$this->saveMigration('down', $down);
		$this->saveMigration('down', $foreignKeysOn);

		// save this migration in the current users migration table
		$migrator = new Migrator;
		$migrator->storeExternallyExecutedMigration($this->_alias.'/'.$this->_classname);

		// and reset
		$this->resetParameters();
	}

	// -------------------------------------- //
	// ---------- Internal Methods ---------- //
	// -------------------------------------- //

	/**
	 * create a migration file to be used for this session
	 * @param string $label  a label for the migration file
	 */
	protected function createMigrationFile($label)
	{
		$label = $this->canonicaliseRef($label);
		$this->_classname = 'm'.gmdate('Ymd_His')."_dds_{$label}";
		$this->_filename = $this->_classname.'.php';
		$this->_file = $this->_path.'/'.$this->_filename;

		// create a migration file
		$templateFile = __DIR__.'/templates/ddsMigration.php';
		$input = file_get_contents($templateFile);
		$output = str_replace('__MIGRATION__',$this->_classname,$input);
		file_put_contents($this->_file, $output, LOCK_EX);
		return $this->_filename;
	}

	/**
	 * Save a migration to file
	 * @param string $direction  either 'up' or 'down'
	 * @param string $migration  the migration string
	 */
	protected function saveMigration($direction, $migration)
	{
		if (empty($migration))
			return;
		if (!$this->_file)
			throw new \RuntimeException('Attempting to store migrations without first opening a migration file');
		$migrations = is_array($migration) ? $migration : [ $migration ];
		foreach ($migrations as $m)
			$this->saveToFile($this->_classname."::addMigration('$direction', \"$m\");");
	}

	protected function saveToFile($message)
	{
		file_put_contents($this->_file, $message.PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	/**
	 * delete a migration file
	 */
	private function deleteMigration()
	{
		if ($this->_file && file_exists($this->_file))
			unlink($this->_file);
	}

	/**
	 * reset any parameters created for a particular migration
	 */
	private function resetParameters()
	{
		$this->_file = null;
		$this->_filename = null;
		$this->_classname = null;
		$this->clearMigrations();
	}

	/**
	 * @inheritdoc
	 */
	private function clearMigrations()
	{
		$this->_hasMigrations = false;
		$this->_upMigrations = [];
		$this->_downMigrations = [];
	}

	/**
	 * Prepare a migration for storage
	 *
	 * @param string $migration
	 * @return string
	 */
	private function fixMigration($migration)
	{
		// get the number of backslashed backslashes correct for migration file
		return str_replace('\\\\','\\\\\\\\',$migration ?? '');
	}

	/**
	 * Prepare a set of Migrations for storage
	 */
	private function prepareMigration($migration)
	{
		$migrations = is_array($migration) ? $migration : [ $migration ];
		$prepared = [];
		foreach ($migrations as $m)
			$prepared[] = $this->fixMigration($m);
		return $prepared;
	}
}