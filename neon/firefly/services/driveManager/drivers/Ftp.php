<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 16:30
 * @package neon
 */

namespace neon\firefly\services\driveManager\drivers;

use \yii\base\Component;
use yii\base\InvalidConfigException;
use League\Flysystem\Adapter\Ftp as Adapter;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;

/**
 * A thin configurable class to produce the Local adapter
 * Class Local
 * @package neon\firefly\finder\drivers
 */
class Ftp extends Component implements IDriverConfig
{
	/**
	 * The Ftp host
	 * @var string
	 */
	public $host;
	/**
	 * @var string
	 */
	public $username;
	/**
	 * @var string
	 */
	public $password;

/*
| ----------------------------------------------------------------------------
| Optional Parameters
| ----------------------------------------------------------------------------
*/
	/**
	 * @var int
	 */
	public $port = 21;
	/**
	 * Optional filesystem root path for e.g. /path/to/root
	 * @var string
	 */
	public $root = '';
	/**
	 * @var bool
	 */
	public $passive = true;
	/**
	 * @var bool
	 */
	public $ssl = true;
	/**
	 * @var int
	 */
	public $timeout = 30;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->host === null) {
			throw new InvalidConfigException('The "host" property must be set.');
		}
		if ($this->username === null) {
			throw new InvalidConfigException('The "username" property must be set.');
		}
		if ($this->password === null) {
			throw new InvalidConfigException('The "password" property must be set.');
		}
		parent::init();
	}

	/**
	 * @return Adapter
	 */
	public function getAdapter()
	{
		return new Adapter([
			'host' => $this->host,
			'username' => $this->username,
			'password' => $this->password,
			/** optional config settings */
			'port' => $this->port,
			'root' => $this->root,
			'passive' => $this->passive,
			'ssl' => $this->ssl,
			'timeout' => $this->timeout
		]);
	}
}