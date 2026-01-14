<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields\el;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class Color extends Field
{

	/**
	 * Set some allowable tags for single text fields
	 * @var string
	 */
	protected $allowableTags = null;

	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'textshort';

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('neon-core-form-fields-el-datetime', '', ['v-bind' => $this->toJson()]);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if (!empty($searchData)) {
			$query->where($this->getDataKey(), 'like', $searchData);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Colour', 'group' => 'Text', 'icon' => 'fa fa-paint-brush', 'order' => 171,
		];
	}
}