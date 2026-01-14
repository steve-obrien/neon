<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/09/2017
 * @package neon
 */

namespace neon\core\data;

class Pagination extends \yii\data\Pagination
{
	public function toArray()
	{
		return [
			'page' => $this->getPage(),
			'pageCount' => $this->getPageCount(),
			'pageSize' => $this->getPageSize(),
			'totalCount' => $this->totalCount
		];
	}
}