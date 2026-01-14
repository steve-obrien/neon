<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\tests\unit\daedalus\mocks;

use neon\daedalus\interfaces\IDdsAppMigrator;
use neon\core\helpers\Hash;

class DdsAppMigratorMock implements IDdsAppMigrator
{
	private $open = false;
	private $label = '';
	private $upMigrations = [];
	private $downMigrations = [];
	public $showDebug = false;

	// ------------------------------------- //
	// ---------- IDdsAppMigrator ---------- //
	// ------------------------------------- //

	/**
	 * @inheritdoc
	 */
	public function openMigrationFile($label)
	{
		$this->open = true;
		$this->label = $label;
		return Hash::uuid64().$label;
	}

	/**
	 * @inheritdoc
	 */
	public function storeMigration($upMigration, $downMigration)
	{
		$id = Hash::uuid64();
		$this->upMigrations[$id] = $upMigration;
		$this->downMigrations[$id] = $downMigration;
		return Hash::uuid64();
	}

	/**
	 * @inheritdoc
	 */
	public function removeMigration($id)
	{
		unset($this->upMigrations[$id]);
		unset($this->downMigrations[$id]);
	}

	/**
	 * @inheritdoc
	 */
	public function closeMigrationFile($delete=false)
	{
		if ($this->open && !$delete && $this->showDebug) {
			codecept_debug(PHP_EOL.PHP_EOL.'UP MIGRATIONS FOR '.$this->label);
			codecept_debug(print_r($this->upMigrations,true));
			codecept_debug(PHP_EOL.PHP_EOL.'DOWN MIGRATIONS FOR '.$this->label);
			codecept_debug(print_r(array_reverse($this->downMigrations),true));
		}
		$this->open = false;
		$this->upMigrations = [];
		$this->downMigrations = [];
	}

}