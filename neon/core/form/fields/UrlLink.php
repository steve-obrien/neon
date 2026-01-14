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
 * Class UrlLink
 * @package neon\core\form
 */
class UrlLink extends Field
{
	/**
	 * set whether or not this is an absolute or relative URL
	 */
	public $absolute=true;

	/**
	 * Set some allowable tags for single text fields
	 * @var string
	 */
	protected $allowableTags = "<a>";

	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'url';

	public $urlOptions = [
		'_blank' => 'Open in a new tab',
		'_self' => 'In the same tab',
	];

	/**
	 * Get a displayable representation of the fields value
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		return '<a href="'.$this->getValue().'" target="_blank" noopener noreferrer>'.$this->getValue().'</a>' ;
	}

	/**
	 * Get the value suitable to be used as a URL
	 */
	public function getValue()
	{
		// sanitise the value properly by testing to see if it would work
		// within a URL after running through Html::purify.
		if ($this->_sanitisedValue === null) {
			$value = $this->getUnsafeValue();
			// disallow any quotes in the URL to prevent a class of attacks
			if (strpos($value ?? '', "'") === false && strpos($value ?? '','"') === false) {
				// prefix with the schema if not provided
				if ($value && $this->absolute && strpos($value, 'http')===false)
					$value = str_replace('///','//','https://'.$value);
				// now turn into a link and run through purify to strip
				// out any dodgy shenanigans
				$link = '<a href="'.$value.'">';
				$safeValue = $this->purifyInput($link);
				if (strpos($safeValue, 'href')) {
					$exploded = explode('"',$safeValue);
					$this->_sanitisedValue = $exploded[1];
				}
			} else
				$this->_sanitisedValue = '';
		}
		return $this->_sanitisedValue;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		// convert the database values to booleans
		$this->absolute = (bool)$this->absolute;
		$properties = parent::getProperties();
		$properties[] = 'absolute';
		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->url;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Url Link',
			'icon' => 'fa fa-link',
			'group' => 'Text',
			'order' => 180,
		];
	}
}