<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/01/2018
 * @package neon
 */
namespace neon\core\interfaces;

/**
 * Interface IComponent - This interface is responsible for giving meta data for a component which can be used to
 * display on a builder
 * @package neon\core\interfaces
 */
interface IComponent
{
	/**
	 * @return array|boolean
	 * IComponent enables adding meta data about the component array keys include:
	 * 'group'
	 * 'order'
	 * 'icon' - can be a string class name or svg string
	 *
	 * Boolean false can also be returned to indicate this component should not be used on any builders
	 */
	public function getComponentDetails();
}