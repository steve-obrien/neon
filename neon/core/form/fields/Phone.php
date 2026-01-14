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
 * Class Phone numbers
 * @package neon\core\form
 */
class Phone extends Field
{
	public $ddsDataType = 'textshort';

	public function getFilterField()
	{
		return ['class' => \neon\core\form\fields\Text::class];
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->phoneNumber;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Phone',
			'icon' => 'fa fa-phone',
			'group' => 'Text',
			'order' => 150,
		];
	}
}