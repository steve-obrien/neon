<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;

/**
 * DisplayText fields are used to show text on the form in various guises
 * They do not result in any input from the user
 *
 * This field takes a 'text' parameter with the appropriate content for the
 * type
 *
 * @package neon\core\form
 */
class DisplayText extends Field
{
	/**
	 * The DDS data type set this to null as this is not an input
	 * @var string
	 */
	public $ddsDataType = null;

	/**
	 * @var String
	 */
	protected $_text = '';

	/**
	 * Set the text property
	 * @param string $value
	 * @return $this - chainable function
	 */
	public function setText($value)
	{
		$this->_text = $value;
		return $this;
	}

	/**
	 * Get the text property
	 * @return string
	 */
	public function getText()
	{
		return $this->_text;
	}

	/**
	 * @inheritdoc
	 */
	public function setLabel($value)
	{
		parent::setLabel($value);
		$this->_text = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return ['class', 'name', 'text'];
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		// no filters can be applied here - so we override and do nothing.
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return false;
	}

}