<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use \neon\core\helpers\Arr;
use neon\core\interfaces\IDataMapProvider;

/**
 * This select box will look up a list of items to populate itself
 *
 * It is similar to SelectDynamic except that it create a data_type of link which means it
 * allows multiple links to be created if required
 *
 * It relies upon the `dataMapProvider` and `dataMapKey` properties to make a request that will return its items
 * The items should be of form 'uuid64' as key and whatever value
 *
 * *NOTE* The data map provider should be accessible from `neon()->$provider`
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\form\fields
 */
class Link extends SelectDynamic
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'link_multi';

	/**
	 * A link represents an array of object uuid's
	 * an empty array is the default empty state
	 * @var array
	 */
	protected $_value = [];

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['items']);
	}

	/**
	 * @return string
	 */
	protected function extractLinkClass()
	{
		return $this->dataMapKey;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label'=>'Link Many', 'name'=>'linkmany', 'icon' => 'fa fa-link', 'group' => 'Table Links', 'order'=> 420,
		];
	}
}