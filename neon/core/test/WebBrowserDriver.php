<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/04/2017
 * Time: 16:40
 */

namespace neon\core\test;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;

class WebBrowserDriver extends Extension
{
	// list events to listen to
	static public $events = [
		'module.init' => 'moduleInit',
	];
	private $resource;
	private $pipes;

	public $chromeVersion = 91;

	public function __construct($config, $options)
	{
		// Codeception has an option called silent, which suppresses the console output.
		// Unfortunately there is no builtin way to activate this mode for a single extension.
		// This is why the option will passed from the extension configuration ($config)
		// to the global configuration ($options);
		// Note: This must be done before calling the parent constructor.
		if (isset($config['silent']) && $config['silent']) {
			$options['silent'] = true;
		}
		parent::__construct($config, $options);
		// Set default path for ChromeDriver
		if (!isset($this->config['path'])) {
			$this->config['path'] = __DIR__ . '/bin/'.$this->chromeVersion.'/chromedriver-' . $this->getDriverSuffix();
		}
		// Set default WebDriver port
		if (!isset($this->config['port'])) {
			$this->config['port'] = 4444;
		}
		// Set default debug mode
		if (!isset($this->config['debug'])) {
			$this->config['debug'] = false;
		}
	}
	protected function getDriverSuffix()
	{
		switch (PHP_OS) {
			case 'Darwin':
				return 'mac';
			case 'WINNT':
				return 'win.exe';
			default:
				return 'linux';
		}
	}
	public function __destruct()
	{
		$this->stopServer();
	}
	/**
	 * Start ChromeDriver server
	 */
	private function startServer()
	{
		if ($this->resource !== null) {
			return;
		}
		$this->writeln(PHP_EOL);
		$this->writeln('Starting Chrome Driver Server');
		$command = $this->getCommand();
		if ($this->config['debug']) {
			$this->writeln(PHP_EOL);
			// Output the generated command
			$this->writeln('Generated Chrome Driver Command:');
			$this->writeln($command);
			$this->writeln(PHP_EOL);
		}
		$descriptorSpec = array(
			array('pipe', 'r'),
			array('file', $this->getLogDir() . 'ChromeDriver.output.txt', 'w'),
			array('file', $this->getLogDir() . 'ChromeDriver.errors.txt', 'a')
		);
		$this->resource = proc_open($command, $descriptorSpec, $this->pipes, null, null, array('bypass_shell' => true));
		if (!is_resource($this->resource) || !proc_get_status($this->resource)['running']) {
			proc_close($this->resource);
			throw new ExtensionException($this, 'Failed to start PhantomJS server.');
		}
		// Wait till the server is reachable before continuing
		$max_checks = 10;
		$checks = 0;
		$this->write('Waiting for the ChromeDriver server to be reachable');
		while (true) {
			if ($checks >= $max_checks) {
				throw new ExtensionException($this, 'ChromeDriver server never became reachable');
				break;
			}
			if ($fp = @fsockopen('127.0.0.1', $this->config['port'], $errCode, $errStr, 10)) {
				$this->writeln('');
				$this->writeln('ChromeDriver server now accessible');
				fclose($fp);
				break;
			}
			$this->write('.');
			$checks++;
			// Wait before checking again
			sleep(1);
		}
		// Clear progress line writing
		$this->writeln('');
	}
	/**
	 * Stop PhantomJS server
	 */
	private function stopServer()
	{
		if ($this->resource !== null) {
			$this->write('Stopping ChromeDriver Server');
			// Wait till the server has been stopped
			$max_checks = 10;
			for ($i = 0; $i < $max_checks; $i++) {
				// If we're on the last loop, and it's still not shut down, just
				// unset resource to allow the tests to finish
				if ($i == $max_checks - 1 && proc_get_status($this->resource)['running'] == true) {
					$this->writeln('');
					$this->writeln('Unable to properly shutdown PhantomJS server');
					unset($this->resource);
					break;
				}
				// Check if the process has stopped yet
				if (proc_get_status($this->resource)['running'] == false) {
					$this->writeln('');
					$this->writeln('ChromeDriver server stopped');
					unset($this->resource);
					break;
				}
				foreach ($this->pipes as $pipe) {
					if (is_resource($pipe)) {
						fclose($pipe);
					}
				}
				// Terminate the process
				// Note: Use of SIGINT adds dependency on PCTNL extension so we
				// use the integer value instead
				proc_terminate($this->resource, 2);
				$this->write('.');
				// Wait before checking again
				sleep(1);
			}
		}
	}
	/**
	 * getCommandParameters
	 *
	 * @return string
	 */
	private function getCommandParameters()
	{
		// Map our config options to PhantomJS options
		$mapping = array(
			'port' => '--port',
		);
		$params = array();
		foreach ($this->config as $configKey => $configValue) {
			if (!empty($mapping[$configKey])) {
				if (is_bool($configValue)) {
					// Make sure the value is true/false and not 1/0
					$configValue = ($configValue) ? 'true' : 'false';
				}
				$params[] = $mapping[$configKey] . '=' . $configValue;
			}
		}
		return implode(' ', $params);
	}
	/**
	 * Get PhantomJS command
	 */
	private function getCommand()
	{
		// Prefix command with exec on non Windows systems to ensure that we receive the correct pid.
		// See http://php.net/manual/en/function.proc-get-status.php#93382
		$commandPrefix = $this->isWindows() ? '' : 'exec ';
		return $commandPrefix . escapeshellarg(realpath($this->config['path'])) . ' ' . $this->getCommandParameters() . ' --url-base=/wd/hub';
	}
	/**
	 * Checks if the current machine is Windows.
	 *
	 * @return bool True if the machine is windows.
	 * @see http://stackoverflow.com/questions/5879043/php-script-detect-whether-running-under-linux-or-windows
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Module Init
	 */
	public function moduleInit(SuiteEvent $e)
	{
		// Check if PhantomJS should only be started for specific suites
		if (isset($this->config['suites'])) {
			if (is_string($this->config['suites'])) {
				$suites = [$this->config['suites']];
			} else {
				$suites = $this->config['suites'];
			}
			// If the current suites aren't in the desired array, return without starting PhantomJS
			if (!in_array($e->getSuite()->getBaseName(), $suites)
				&& !in_array($e->getSuite()->getName(), $suites)) {
				return;
			}
		}
		// Start the ChromeDriver server
		$this->startServer();
	}
}
