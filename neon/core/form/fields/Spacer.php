<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class Spacer extends Heading
{
	public $height = 25;

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return ['class', 'name', 'height'];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Spacer',
			'group' => "Form Only",
			'icon' => 'fa fa-square-o',
			'order' => 920
		];
	}
}