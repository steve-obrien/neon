<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/11/2016 15:47
 * @package neon
 */

namespace neon\core\interfaces;

/**
 * You can use this interface in conjunction with the neon\core\traits\PropertiesTrait
 * It essentially makes a class serializable
 * Interface IProperties
 * @package neon\core\interfaces
 */
interface IProperties extends
	IArrayable,
	IJsonable
{
	/**
	 * @return array of properties to serialise
	 */
	public function getProperties();

	/**
	 * Return the full class name of the object e.g. \my\awesome\Class
	 * @return string
	 */
	public function getClass();
}