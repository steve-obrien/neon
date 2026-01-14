<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\grid\query\IQuery;
use neon\core\form\fields\Field;
use neon\core\form\fields\el\assets\ElAsset;
use neon\core\helpers\Html;
use neon\core\helpers\Arr;

class DateTimeRange extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'json';

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('neon-core-form-fields-el-datetimerange', '', [
			'id' => $this->id,
			'name' => $this->getInputName(),
		]);
	}

	/**
	 * Get the picker format
	 * @return string
	 */
	public function getFormat()
	{
		return neon()->formatter->datetimeFormat;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['format']);
	}

	/**
	 * @inheritDoc
	 */
	public function setValueFromDb($value)
	{
		$this->_value = json_decode($value);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if ($searchData !== '') {
			$dateFrom = Arr::get($searchData, 'from');
			$dateTo   = Arr::get($searchData, 'to');
			if ($dateFrom)
				$query->where($this->getDataKey(), '>=', $dateFrom);
			if ($dateTo)
				$query->where($this->getDataKey(), '<=', $dateTo);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		ElAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$value = parent::getValue();
		if (is_array($value))
			return $value;
		return ['from' => '', 'to' => ''];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Range Date Time', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order' => 370,
		];
	}
}