<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 25/09/2018
 * Time: 10:31
 */

namespace neon\core\web;

use neon\core\helpers\Console;
use neon\core\helpers\File;

class AssetManager extends \yii\web\AssetManager
{
	public $publishOptions = [];

	protected function publishDirectory($src, $options)
	{
		$this->publishOptions = $options;
		return parent::publishDirectory($src, $options);
	}

	/**
	 * Generate a CRC32 hash for the directory path. Collisions are higher
	 * than MD5 but generates a much smaller hash string.
	 *
	 * @param string $path string to be hashed.
	 * @return string hashed string.
	 */
	protected function hash($path)
	{
		if (isset($this->publishOptions['path'])){
			// if a path is specified then use this instead of a hash
			return $this->publishOptions['path'];
		}
		return parent::hash($path);
	}

	/**
	 * Remove all published asset directories
	 *
	 * @throws \yii\base\ErrorException
	 * @return int number of directories removed
	 */
	public function flushAssets()
	{
		$directoriesRemoved = 0;
		$assetsDir = neon()->getAlias(neon()->assetManager->basePath);
		foreach (File::findDirectories($assetsDir, ['recursive'=>false]) as $directory) {
			File::removeDirectory($directory);
			$directoriesRemoved++;
		}
		return $directoriesRemoved;
	}
}
