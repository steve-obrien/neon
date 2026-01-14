<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\db;

use craft\errors\ShellCommandException;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use \neon\core\db\Query;
use neon\core\helpers\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use yii\base\ErrorException;
use yii\base\Exception;

/**
 * @inheritdoc
 */
class Connection extends \yii\db\Connection
{
	/**
	 * @inheritdoc
	 */
	public $commandClass = 'neon\core\db\Command';

	/**
	 * Use a fluid query interface
	 *
	 * @return \neon\core\db\Query
	 */
	public function query()
	{
		return new Query();
	}

	/**
	 * Returns a boolean indicating whether the database name has been set in the dsn
	 *
	 * @returns boolean
	 */
	public function hasDatabaseName()
	{
		// find the db name in the dsn string
		// https://regex101.com/r/aQO6vq/1 - use this link for a nice breakdown of how this regex works.
		$result = preg_match('/dbname=(.*?)(;|$)/i', $this->dsn, $matches);
		return $result;
	}

	/**
	 * Performs a backup operation.
	 *
	 * @throws \yii\base\Exception if the directory could not be created
	 * @return string The database backup file path
	 */
	public function backup(): string
	{
		$file = $this->getBackupFilePath();
		$this->backupTo($file);
		return $file;
	}

	/**
	 * Return the backup directory
	 *
	 * @throws \yii\base\Exception if the directory could not be created
	 * @return string
	 */
	public function getBackupDirectory(): string
	{
		$dir = neon()->getAlias('@var/backups');
		File::createDirectory($dir);
		return $dir;
	}

	/**
	 * Returns the path for a new backup file.
	 *
	 * @throws \yii\base\Exception if the directory could not be created
	 * @return string
	 */
	public function getBackupFilePath(): string
	{
		$currentVersion = neon()->getVersion();
		$dbName = env('DB_NAME');
		$filename = gmdate('ymd_His') . '_' . $dbName . '_' . $currentVersion . '.sql';
		return $this->getBackupDirectory()."/$filename";
	}

	/**
	 * Back up the database - by performing a database dump to the file specified
	 *
	 * @param string $file - file name and path of the db backup
	 * @throws ProcessFailedException - if the mysqldump command fails
	 * @throws \yii\base\Exception - if directory can not be created
	 * @return int The exit status code
	 */
	public function backupTo($file)
	{
		File::createDirectory(dirname($file));
		$process = new Process([
			'mysqldump',
			'--single-transaction',
			'--add-drop-table',
			'--create-options',
			'--dump-date',
			'--no-autocommit',
			'--routines',
			'--default-character-set=' . $this->charset,
			'--set-charset',
			'--triggers',
			'--host='.env('DB_HOST'),
			'--user='.env('DB_USER'),
			'--password='.escapeshellcmd(env('DB_PASSWORD')),
			'--result-file='.$file,
			env('DB_NAME'),
		]);
		$result = $process->run();
		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
		return $result;
	}

	/**
	 * Restore a database from a sql file
	 *
	 * @throws ProcessFailedException if the restore command fails
	 * @param string $path - path to the sql file
	 * @return int The exit status code
	 */
	public function restore($path)
	{
		$process = new Process(['mysql', env('DB_NAME'), '<', $path]);
		$result = $process->run();
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
		return $result;
	}
}
