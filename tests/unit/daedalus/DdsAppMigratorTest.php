<?php

namespace neon\tests\unit\daedalus;

use \neon\core\test\Unit;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;

/**
 * Class ClassManagementTest
 * @package neon\tests\unit\daedalus
 *
 * @property DdsAppMigrator $dds
 */
class DdsAppMigratorTest extends Unit
{
	private $migrator = null;

	private $path = DIR_TEST.'/_root/system';

	public function setUp():void {
		$this->migrator = new DdsAppMigrator($this->path);
	}

	public function tearDown():void {
		$this->migrator->closeMigrationFile(true);
	}

	protected function file($filename)
	{
		return $this->path.'/'.$filename;
	}

	/**
	 * check that opening and closing a migration results in no file overall
	 */
	public function testMigrationOpenAndClose()
	{
		$label = $this->randomLabel();
		$name = $this->migrator->openMigrationFile($label);
		$this->assertTrue(strpos($name, $label)!==null);
		$this->assertFileExists($this->file($name));
		$this->migrator->closeMigrationFile();
		$this->assertFileNotExists($this->file($name));
	}


	/**
	 * @expectedException RuntimeException
	 */
	public function testMigrationMultipleOpenException()
	{
		$this->expectException(\RuntimeException::class);
		$this->migrator->openMigrationFile($this->randomLabel());
		$this->migrator->openMigrationFile($this->randomLabel());
	}

	public function testMigrationStoring()
	{
		$name = $this->migrator->openMigrationFile($this->randomLabel());
		$up = $this->randomString();
		$down = $this->randomString();
		// prefix second with first to simplify testing for entries below
		$up2 = $up.$this->randomString();
		$down2 = $down.$this->randomString();

		// check up and down not in file
		$content = file_get_contents($this->file($name));
		$this->assertTrue(strpos($content, $up)===false);
		$this->assertTrue(strpos($content, $down)===false);

		// store the migrations
		$this->migrator->storeMigration($up, $down);
		$id2 = $this->migrator->storeMigration($up2, $down2);

		// check up and down still not in file
		$content = file_get_contents($this->file($name));
		$this->assertTrue(strpos($content, $up)===false);
		$this->assertTrue(strpos($content, $down)===false);

		// remove the second migration
		$this->migrator->removeMigration($id2);

		// and close the migration file
		$this->migrator->closeMigrationFile();

		// check that up and down are now in file
		$content = file_get_contents($this->file($name));
		$this->assertTrue(strpos($content, $up)!==false);
		$this->assertTrue(strpos($content, $down)!==false);

		// check that up2 and down2 are not in file
		$content = file_get_contents($this->file($name));
		$this->assertTrue(strpos($content, $up2)===false);
		$this->assertTrue(strpos($content, $down2)===false);

		// remove the migration
		unlink($this->file($name));
	}

	protected function randomLabel()
	{
		return str_replace('-','_',$this->randomString());
	}
}