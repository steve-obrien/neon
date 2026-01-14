<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Newicon <theteam@newicon.net>
 * @date 12/12/2015 16:13
 */
namespace neon\core\grid\column;

use neon\core\grid\query\IQuery;

/**
 * Class IntColumn
 * @deprecated Use a standard column and set the member to be an integer field
 */
class IntColumn extends Column
{

	protected function getBaseFilterField()
	{
		return [
			'class' => 'integer',
			'name' => $this->getKey(),
			'dataKey' => $this->getDbField()
		];
	}

}