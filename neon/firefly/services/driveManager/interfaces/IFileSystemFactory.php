<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 15:18
 * @package neon/firefly
 */
namespace neon\firefly\services\driveManager\interfaces;

/**
 * Interface IFileSystemFactory
 * @package neon\firefly\driveManager
 */
interface IFileSystemFactory
{
	/**
	 * Get a filesystem implementation.
	 *
	 * @param  string  $name the name of the drive
	 * @return \neon\firefly\services\driveManager\interfaces\IFileSystem
	 */
	public function drive($name = null);
}