<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @date 12/12/2015 16:13
 */
namespace neon\core\grid\column;

use neon\core\helpers\Html;
use \neon\core\grid\query\IQuery;

class Check extends Column
{

	public function renderHeaderCellContent()
	{
		return Html::checkbox('all', false, ['data-check-all'=>true, 'style' => 'margin:0;position:relative;padding-top:2px;']);
	}

	public function renderDataCellContent($model, $key, $index, $context='grid')
	{
		return Html::checkbox($this->getFieldName(), false, [
			'data-check-row'=>'',
			'style' => 'margin:0;position:relative;'
		]);
	}

	/**
	 * @param IQuery $query
	 */
	public function processSearch(IQuery $query)
	{

	}

	public function renderFilterCellContent()
	{
		return '&nbsp;';
	}

}