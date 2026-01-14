<?php

namespace neon\core\test;
use \Symfony\Component\Process\Process;

/**
 * Class ProcessForker
 * This class is a very simple wrapper of process it allows management of processes via a friendly key identifier rather
 * then a pid.  It stores the pid of the process and also all processes its aware of in the $processes array
 * It acts a bit like a singleton container for processes
 * @package neon\core\test
 */
class ProcessForker
{
	/**
	 * @var Process[]
	 */
	protected static $processes = [];

	/**
	 * Run a new command in the background and return the PHP object representation
	 *
	 * @param string $key - a friendly string name for the process to identify it
	 * @param string $command - the command to run
	 * @return Process
	 */
	public static function run($key, $command)
	{
		// Check a process with this name is not already running. If so just return the existing process object.
		if (isset(static::$processes[$key])) {
			return static::$processes[$key];
		}
		static::$processes[$key] = new Process($command, null, null, null, null);
		static::start($key);
		return static::$processes[$key];
	}

	/**
	 * Calls stop on the process
	 *
	 * @throws \InvalidArgumentException - if no process with the key exists
	 * @param string $key - the friendly name of the process
	 * @return bool - whether it stopped successfully
	 */
	public static function stop($key)
	{
		$ret = static::getProcess($key)->stop();
		unset(static::$processes[$key]);
		return $ret;
	}

	/**
	 * Start the command
	 *
	 * @throws \InvalidArgumentException - if no process with the key exists
	 * @param string $key
	 * @return bool true if started successfully
	 */
	public static function start($key)
	{
		return static::getProcess($key)->start();
	}

	/**
	 * Get the result of ps -p [pid] for the process
	 *
	 * @throws \InvalidArgumentException - if no process with the key exists
	 * @param string $key
	 * @return bool
	 */
	public static function status($key)
	{
		return static::getProcess($key)->getStatus();
	}

	/**
	 * Get a process by its key
	 *
	 * @throws \InvalidArgumentException - if no process with the key exists
	 * @param string $key
	 * @return Process
	 */
	public static function getProcess($key)
	{
		if (isset(static::$processes[$key])) {
			return static::$processes[$key];
		}
		throw new \InvalidArgumentException("There is no process defined with key '$key'");
	}
}