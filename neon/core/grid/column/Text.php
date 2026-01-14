<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @date 12/12/2015 16:13
 */
namespace neon\core\grid\column;

use neon\core\grid\query\IQuery;

/**
 * Class Text
 * @package neon\core\grid\column - use a standard column and set its member to be a text field
 * @deprecated
 */
class Text extends Column
{
	/**
	 * @inheritdoc
	 */
	public function renderDataCellContent($model, $key, $index, $context='grid')
	{
		$out = $this->highlight($this->getCellData($model), $this->getSearch());
		return $out;
	}

	/**
	 * @inheritdoc
	 */
	protected function getBaseFilterField()
	{
		return [
			'name' => $this->getKey(),
			'dataKey' => $this->getDbField(),
			'class' => 'neon\core\form\fields\Text'
		];
	}
}