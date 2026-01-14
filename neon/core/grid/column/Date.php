<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Neill Jones <neill.jones@newicon.net>
 * @date 12/8/17
 */
namespace neon\core\grid\column;

use neon\core\grid\query\IQuery;

/**
 * Class Date
 * @package neon\core\grid\column
 * @deprecated - use a standard column and set its member to be a date field
 */
class Date extends Column
{
	/**
	 * Format for date picker
	 * @var string
	 */
	public $dateUserFormat = 'Y-m-d';

	/**
	 * @inheritdoc
	 */
	protected function getBaseFilterField()
	{
		return [
			'class' => 'daterange',
			'name' => $this->getKey(),
			'dateUserFormat' => $this->dateUserFormat,
			'dataKey' => $this->getDbField()
		];
	}
}