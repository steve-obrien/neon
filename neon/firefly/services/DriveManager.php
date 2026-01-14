<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 20/11/2016 18:34
 * @package neon
 */

namespace neon\firefly\services;

use \yii\base\Component;
use \neon\core\helpers\Arr;
use \League\Flysystem\FilesystemAdapter; // Updated import
use \neon\firefly\services\driveManager\Drive;
use \neon\firefly\services\driveManager\interfaces\IFileSystem;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;
use \neon\firefly\services\driveManager\interfaces\IFileSystemFactory;


/**
 * Class FileSystem
 * The FileSystem class is simply a factory for building IFileSystem objects
 *
 *
 * @package neon\firefly
 */
class DriveManager extends Component implements IFileSystemFactory
{
	/**
	 * Default Filesystem drive Driver
	 * @var string
	 */
	public $default = 'media';

	/**
	 * Default Cloud Filesystem drive Driver
	 * @var string
	 */
	public $cloud = '';

	/**
	 * The array of drives in the format:
	 *
	 * ```php
	 * [
	 *     'my_drive_name' => [
	 *          'driver' => (string) current out of the box driver mapping include 'local' | 'db' | 's3'
	 *                      you can also specify a class string \neon\my\custom\dropbox\driver the class must implement
	 *                      \neon\firefly\services\driveManager\interfaces\IDriverConfig. You can also map friendlier driver
	 *                      names by overriding the $this->driverMap property e.g. 'dropbox' => 'neon\my\custom\dropbox\driver'
	 *                      Then you can use the friendlier string instead of the full class path
	 *          'visibility' => (string) the default visibility one of 'public' | 'private'
	 *
	 *          ... => additional configuration options mapping to public properties or public setter functions of the
	 *                 specific adapter class can be added here - these are simply passed on.  For example the local adapter
	 *                 has a permissions property and a root path property, the amazon s3 driver has setting like api key,
	 *                 bucket name and secret key etc.
	 *     ]
	 * ]
	 * ```
	 *
	 * A typical example of a useful configuration might be:
	 *
	 * ```php
	 * [
	 *     'media' => [
	 *          'driver' => 'local',
	 *          'root' => '@root/var/storage/media',
	 *          'visibility' => 'public',
	 *          'permissions' => [
	 *              'file' => [
	 *                  'public' => 0664,
	 *                  'private' => 0600,
	 *              ],
	 *              'dir' => [
	 *                  'public' => 0775,
	 *                  'private' => 0700,
	 *              ],
	 *          ],
	 *      ],
	 *
	 *      'backup' => [
	 *          'driver' => 'local',
	 *          'root' => '/var/backups'
	 *      ],
	 *
	 *      'cloud' => [
	 *          'driver' => 's3',
	 *          'key' => 'fwqef123432'
	 *      ]
	 * ]
	 * ```
	 *
	 * @var array
	 * @var array[]
	 */
	public $drives = [

		'media' => [
			'driver' => 'local',
			'root' => '@root/var/storage/media',
		]

	];

	/**
	 * Local cache of configured drive drivers
	 * @var array
	 */
	protected $_drives = [];

	protected $_flysystem = [];

	/**
	 * Map friendly driver names to their class implementations
	 * @var array
	 */
	public $driverMap = [
		'db' => '\neon\firefly\services\driveManager\drivers\Db',
		'local' => '\neon\firefly\services\driveManager\drivers\Local',
		's3' => '\neon\firefly\services\driveManager\drivers\S3',
	];

	/**
	 * Get a filesystem instance.
	 *
	 * @param  string  $name
	 * @return IFileSystem
	 */
	public function drive($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();
		if (!isset($this->drives[$name])) {
			throw new \InvalidArgumentException("No drive with the name of '$name' exists. Make sure you configure all drives under the drives property of the firefly Drive Manager");
		}
		return $this->drives[$name] = $this->_get($name);
	}

	/**
	 * Get a default cloud filesystem instance.
	 *
	 * @return IFileSystem
	 */
	public function cloud()
	{
		$name = $this->getDefaultCloudDriver();
		return $this->_drives[$name] = $this->_get($name);
	}

	/**
	 * Load a Filesystem for a drive from session cache
	 *
	 * @param $name
	 * @return IFileSystem
	 */
	protected function _get($name)
	{
		return isset($this->_drives[$name]) ? $this->_drives[$name] : $this->_make($name);
	}

	/**
	 * Make the filesystem object for the specified drive and driver
	 *
	 * @param  string  $name
	 * @return IFileSystem
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function _make($name)
	{
		$config = $this->getConfig($name);
		// look up the driver:
		$driver = Arr::get($config, 'driver');
		if (Arr::get($this->driverMap, $driver)) {
			$driver = $this->driverMap[$driver];
		}
		unset($config['driver']);

		// get the adapter driver for the drive (e.g. S3 | local)
		$configObj = (new $driver($config));
		if ( ! $configObj instanceof IDriverConfig) {
			throw new \InvalidArgumentException('The driver specified is not a valid implementation of \neon\firefly\services\fileSystem\interfaces\IDriverConfig');
		}
		$driverObj = $configObj->getAdapter();
		if ( ! $driverObj instanceof FilesystemAdapter) { // Updated interface check
			throw new \InvalidArgumentException('The driver config object ('.$driver.') does not provide a valid driver implementing \League\Flysystem\FilesystemAdapter');
		}
		// get the generic Filesystem adapter
		$this->_drives[$name] = new Drive($driverObj, $config, $name);
		return $this->_drives[$name];
	}

	/**
	 * Get the filesystem connection configuration.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		return $this->drives[$name];
	}

	/**
	 * Get the default driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->default;
	}

	/**
	 * Get the default cloud driver name.
	 *
	 * @return string
	 */
	public function getDefaultCloudDriver()
	{
		return $this->cloud;
	}

	/**
	 * Pass dynamic methods call onto the default drive.
	 *
	 * @param  string  $name
	 * @param  array   $params
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $params)
	{
		return call_user_func_array([$this->drive(), $name], $params);
	}
}
