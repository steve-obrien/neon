<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 17:20
 * @package neon
 */

namespace neon\firefly\services\driveManager\drivers;

use \yii\base\Component;
use yii\base\InvalidConfigException;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;

// Amazon dependancies
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Provides the Amazon S3 adapter
 * ----------------------------------------------------------------------------
 *  Note this requires league/flysystem-aws-s3-v3
 * `composer require league/flysystem-aws-s3-v3`
 * ----------------------------------------------------------------------------
 *
 * @depends league/flysystem-aws-s3-v2
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class S3 extends Component implements IDriverConfig
{
	/**
	 * @var string
	 */
	public $key;
	/**
	 * @var string
	 */
	public $secret;
	/**
	 * @var string
	 */
	public $region = 's3-eu-west-1';
	/**
	 * @var string
	 */
	public $bucket;
	/**
	 * @var string|null
	 */
	public $prefix;
	/**
	 * @var string 'latest'|'version'
	 */
	public $version = 'latest';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->key === null) {
			throw new InvalidConfigException('The "key" property must be set.');
		}
		if ($this->secret === null) {
			throw new InvalidConfigException('The "secret" property must be set.');
		}
		if ($this->bucket === null) {
			throw new InvalidConfigException('The "bucket" property must be set.');
		}
		parent::init();
	}

	/**
	 * @return AwsS3Adapter
	 */
	public function getAdapter()
	{
		$client = S3Client::factory([
			'credentials' => [
				'key' => $this->key,
				'secret' => $this->secret,
			],
			'region' => $this->region,
			'version' => $this->version,
		]);
		return new AwsS3Adapter($client, $this->bucket, $this->prefix);
	}
}
