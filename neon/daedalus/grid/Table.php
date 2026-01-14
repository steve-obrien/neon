<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/04/2020
 * @package neon
 */

namespace neon\daedalus\grid;


use neon\core\grid\DdsGrid;

class Table extends DdsGrid
{
	public function init()
	{
		parent::init();
		$this->setPageSize(30);
	}
}
