<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\grid\query\IQuery;

/**
 * Class Text
 * @package neon\core\form
 */
class HtmlRaw extends Textarea
{
	const TEXT_TRUNCATE_LENGTH=250;

	/**
	 * Set some allowable tags for single text fields
	 * @var string
	 */
	protected $allowableTags = "<br><em><i><u><b><s><mark><strong>";

	/**
	 * The DDS data type to store the value of the field
	 * text data type can be up to 20000 characters long
	 * @var string
	 */
	public $ddsDataType = 'text';

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		$text = parent::getValueDisplay();
		if (strlen($text) > self::TEXT_TRUNCATE_LENGTH)
			return substr($text, 0, self::TEXT_TRUNCATE_LENGTH).'...';
		return $text;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'text'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if ($searchData !== '') {
			$query->where($this->getDataKey(), 'like', $searchData);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->realText(mt_rand(10, 20000));
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Raw Html',
			'icon' => 'fa fa-paragraph',
			'group' => 'Formatted Text',
			'order' => 210
		];
	}

	/**
	 * Because this is using raw html, we need to work with the unsanitised value
	 * @inheritdoc
	 */
	public function getValue()
	{
		return parent::getUnsafeValue();
	}

}