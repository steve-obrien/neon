<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 17:29
 * @package neon
 */

namespace neon\firefly\services\driveManager\interfaces;

/**
 * Interface IDriverConfig
 * The IDriverConfig interface allows for drivers to be configured creating the "drives" concept
 * A drive is simply a configuration for a particular flysystem adapter
 * @package neon\firefly\services\driveManager\interfaces
 */
interface IDriverConfig
{
	/**
	 * Return the adapter
	 * @return \League\Flysystem\AdapterInterface
	 */
	public function getAdapter();
}