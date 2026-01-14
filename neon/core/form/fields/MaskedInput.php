<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Masked Input
 * @package neon\core\form
 */
class MaskedInput extends Field
{

	/**
	 * Set some allowable tags for single text fields
	 * @var string
	 */
	protected $allowableTags = "<br><em><i><u><b><s><mark><strong>";

	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'textshort';

	public $delimiter = '';

	public $prefix = '';

	public $blocks = [];

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
	 * @return array
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['delimiter', 'prefix', 'blocks']);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Masked Input', 'icon' => 'fa fa-font', 'group' => 'Text', 'order' => 180
		];
	}
}