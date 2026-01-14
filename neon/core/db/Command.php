<?php

namespace neon\core\db;

class Command extends \yii\db\Command
{
	/** @var bool|string  */
	public static $logRawQueries = false;

	/** @var resource */
	public static $sqlRawLogFileResource = null;

	/** @var bool */
	private static bool $wasFileResource;

	/**
	 * @param string $category
	 * @return array
	 */
	protected function logQuery($category)
	{
		if ((self::$sqlRawLogFileResource!==null && get_resource_type(self::$sqlRawLogFileResource)==='stream') &&
			(self::$logRawQueries===true ||
				(self::$logRawQueries==='execute' && $category==='yii\\db\\Command::execute') ||
				(self::$logRawQueries==='query' && $category==='yii\\db\\Command::query')
			)
		) {
			fwrite(self::$sqlRawLogFileResource, $this->getRawSql().";".PHP_EOL);
		}
		return parent::logQuery($category);
	}

	/**
	 * start logging queries to a file or other stream
	 * @param $fileOrStream
	 * @param bool|string $logRawQueries
	 * @throws \Exception
	 */
	public static function startLogRawQueriesToFile($fileOrStream, $logRawQueries=true)
	{
		if ($logRawQueries===false) return;
		if (is_string($fileOrStream)) {
			self::$sqlRawLogFileResource = fopen($fileOrStream, "w+");
			self::$wasFileResource = false;
		}
		elseif (get_resource_type($fileOrStream)==='stream') {
			self::$sqlRawLogFileResource = $fileOrStream;
			self::$wasFileResource = true;
		}
		else {
			throw new \Exception('parameter fileOrSteam must be a filename or resource of type steam');
		}
		if ($logRawQueries!==true && $logRawQueries!=='execute' && $logRawQueries!=='query') {
			throw new \Exception("parameter logRawQueries must boolean, 'execute' or 'query'");
		}
		self::$logRawQueries = $logRawQueries;
	}

	/**
	 * stop logging queries
	 */
	public static function stopLogRawQueries()
	{
		if (!self::$wasFileResource && get_resource_type(self::$sqlRawLogFileResource)==='stream') {
			// only auto-close if startLogRawQueriesToFile created the resource otherwise it's up to the user
			fclose(self::$sqlRawLogFileResource);
		}
		self::$sqlRawLogFileResource = null;
	}
}