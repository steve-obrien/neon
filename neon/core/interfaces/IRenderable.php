<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/11/2016 15:47
 * @package neon
 */

namespace neon\core\interfaces;

interface IRenderable {

	/**
	 * @return string
	 */
	public function render();
}
