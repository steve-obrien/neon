<?php

use neon\core\helpers\File;

if (! function_exists('neon_test_database_create')) {
	/**
	 * Creates the test database and user
	 * Note this does not run the neon installation and create the
	 * neon tables.
	 * To run the migrations for neon do: neon()->install();
	 */
	function neon_test_database_create()
	{
		$installDb  = env('INSTALL_TEST_DB', true);
		if ($installDb) {
			$db = env('DB_NAME');
			$host = env('DB_HOST', 'localhost');
			echo "\n================== Creating Test Database and neon user ==================\n";
			$cmds = [];
			$cmds[] = "DROP DATABASE IF EXISTS $db;";
			$cmds[] = "CREATE DATABASE IF NOT EXISTS $db;";
			$cmds[] = "CREATE USER 'neon'@'$host' IDENTIFIED BY 'neon';"; // may fail if already exists
			$cmds[] = "GRANT ALL PRIVILEGES ON $db.* TO 'neon'@'$host';";
			foreach ($cmds as $cmd) {
				neon_test_db_cmd($cmd);
			}
			echo "--------------------------------------------------------------------------\n";
			echo "Run: `export DB_USER='user'` to set the database user if not root \n";
			echo "Run: `export DB_PASSWORD='pwd'` to set the database password \n";
			echo "--------------------------------------------------------------------------\n";
			echo "Run: `export INSTALL_TEST_DB=false` to prevent re-installation during development \n";
			echo "==========================================================================\n\n";
			// once the test database is installed we want to change the db connection details
			// for all the following tests.
			// This is slight brain twister as the initial data base connection details need to
			// have full access
			\neon\core\Env::setEnvironmentVariable('DB_USER', 'neon', true);
			\neon\core\Env::setEnvironmentVariable('DB_PASSWORD', 'neon', true);
			// Install neon (run all neon migrations)
			ob_start();
			$migrator = new \neon\core\db\Migrator();
			$migrator->runAllMigrations();
			$result = ob_get_clean();
		} else {
			echo "\n=================== Skipping Test Db Install =============================\n";
			echo "Run: `export DB_USER='user'` to set the database user if not root \n";
			echo "Run: `export DB_PASSWORD='pwd'` to set the database password \n";
			echo "--------------------------------------------------------------------------\n";
			echo "Run: `export INSTALL_TEST_DB=true` to reinstall \n";
			echo "==========================================================================\n\n";
		}
	}
}

if (! function_exists('neon_test_db_cmd')) {
	function neon_test_db_cmd($cmd)
	{
		// A security risk here?  Mind you if you can run PHP your system is compromised anyway.
		// However a little protection - perhaps the environment MUST be in test mode for it to work
		list($user, $pwd, $host) = [env('DB_USER', 'root'), env('DB_PASSWORD', 'root'), env('DB_HOST', 'localhost')];
		$password = empty($pwd) ? '' : "--password=$pwd";
		$c = "mysql --host=$host --user=$user $password -e \"$cmd\"";
		echo "$c\n";
		# use the following line to see any warnings and errors
		#exec($c);
		# or use this one to suppress them (inc the one about the password)
		exec($c . " 2>/dev/null");
	}
}

if (! function_exists('neon_test_server_restart')) {
	/**
	 * Restart a test server
	 * @param $name
	 * @param string $docRoot
	 * @param int $port
	 * @throws Exception
	 */
	function neon_test_server_restart($name='test_server', $docRoot=DIR_TEST.'/_root/public', $port=8888)
	{
		neon_test_server_stop($name);
		echo "\nRestarting test server...\n";
		// clear the cache
		File::removeDirectory(DIR_TEST . '/_root/var');
		File::removeDirectory(DIR_TEST . '/_root/system');
		neon_test_server_start($name, $docRoot, $port, true);
	}
}

if (! function_exists('neon_test_server_start')) {
	/**
	 * Spin up the test server to run the acceptance tests against
	 * @param string $name
	 * @param string $docRoot
	 * @param int $port
	 * @return \Symfony\Component\Process\Process
	 * @throws Exception
	 */
	function neon_test_server_start($name='test_server', $docRoot=DIR_TEST.'/_root/public', $port=8888, $silent=false)
	{
		if (preg_match("/[^a-z0-9_]/", $name) !== 0) {
			throw new \Exception('The name must contain only [a-z0-9_] characters');
		}
		/**
		 * Starts a php web server that runs the neon application
		 * Tests are run against this server
		 */
		$address = "localhost:$port";
		$command = PHP_BINARY . " -S {$address} -t {$docRoot}";
		if (!$silent) {
			echo "\n|======================================================";
			echo "\n| Creating Test Server";
			echo "\n|======================================================";
			echo "\n| command: $command";
			echo "\n| - listening on  : $address";
			echo "\n| - document root : $docRoot";
			echo "\n|======================================================\n";
		}
		// on mac ulimit can be set really low number of concurrent files
		// this can break the end to end tests
		if (PHP_OS == 'Darwin') {
			exec('ulimit -n 10000');
		}
		$process = \neon\core\test\ProcessForker::run($name, $command);
		try {
			retry(10, function () use ($address) {
				$client = new GuzzleHttp\Client();
				$res = $client->request('GET', $address);
				echo ' - Server running with code: ' . $res->getStatusCode() . "\n";
			}, 1000);
		} catch(GuzzleHttp\Exception\ConnectException $e) {
			throw new \Exception('Unable to connect to the local test server');
		}
		return $process;
	}
}

if (! function_exists('retry')) {
	/**
	 * Retry an operation a given number of times.
	 *
	 * @param  int  $times
	 * @param  callable  $callback
	 * @param  int  $sleep in milliseconds
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	function retry($times, callable $callback, $sleep = 0)
	{
		$times--;
		beginning:
		try {
			return $callback();
		} catch (\Exception $e) {
			if (! $times) {
				throw $e;
			}
			$times--;
			if ($sleep) {
				usleep($sleep * 1000);
			}
			goto beginning;
		}
	}
}

if (! function_exists('neon_test_server_stop')) {
	/**
	 * Spin up the test server to run the acceptance tests against
	 * @param string $name
	 * @throws \Exception if name is not formatted correctly
	 */
	function neon_test_server_stop($name='test_server')
	{
		if (preg_match("/[^a-z0-9_]/", $name) !== 0) {
			throw new \Exception('The name must contain only [a-z0-9_] characters');
		}
		\neon\core\test\ProcessForker::stop($name);
		sleep(1);
	}
}

if (! function_exists('test_url')) {
	/**
	 * Return the test url
	 */
	function test_url()
	{
		return 'http://localhost:8888';
	}
}

