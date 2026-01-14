<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\helpers\Html;

/**
 * Class Text
 * @package neon\core\form
 */
class Ide extends Field
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'textlong';

	/**
	 * Array (js object) of editor config options
	 * @see https://github.com/ajaxorg/ace/wiki/Configuring-Ace
	 * @var array
	 */
	public $editorOptions = [];

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ["editorOptions"]);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'neon\\core\\form\\fields\\Text'];
	}
	
	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		// return sanitised data only
		return $this->getUnsafeValue();
	}

	/**
	 * Register scripts
	 * @param \neon\core\web\View $view
	 */
	public function registerScripts($view)
	{
		$view->registerJsFile("//cdnjs.cloudflare.com/ajax/libs/ace/1.2.8/ace.js", ['concatenate' => 'false']);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'IDE',
			'icon' => 'fa fa-paragraph',
			'group' => 'Formatted Text',
			'order' => 240
		];
	}
}
