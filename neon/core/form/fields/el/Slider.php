<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\form\fields\el\assets\ElAsset;
use neon\core\form\fields\Real;
use neon\core\form\fields\Text;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;

class Slider extends Real
{
	public $height = 100;
	public $vertical = false;
	public $min = 0;
	public $max = 10;

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['decimal', 'height', 'vertical', 'min', 'max'] );
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
	public function getFilterField()
	{
		return ['class' => 'real', 'decimal'=>1];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Slider', 'icon' => 'fa fa-registered"', 'group' => 'Number', 'order' => 550
		];
	}
}