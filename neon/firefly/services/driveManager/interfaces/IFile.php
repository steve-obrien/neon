<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/05/2017
 * @package neon
 */

namespace neon\firefly\services\driveManager\interfaces;

/**
 * Interface to support file objects Mirrors \SplFileObject implements minimum functions for firefly compatibility
 * An object implementing this interface can be passed directly to firefly drive manager put functions
 */
interface IFile
{
	/**
	 * Mirrors \SplFileObject
	 * Get the string absolute file path (including the file name)
	 * @return string
	 */
	public function getRealPath();

	/**
	 * Mirrors \SplFileObject
	 * Get the filename
	 * @return string
	 */
	public function getFilename();
}