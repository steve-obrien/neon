<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/04/2020
 * @package neon
 */

namespace neon\cms\form\fields;

use neon\cms\assets\CmsEditorAsset;
use \neon\core\form\fields\Field;
use neon\core\helpers\Str;

class PostStats extends Field
{
	public $ddsDataType = null;

	public $linkedField = '';
	/**
	 * @var int The average number of words an adult reads per minute
	 * used to calculate the time to read of the text value of the linkedField
	 */
	public $averageWordsPerMinute = 265;

	public function getProperties()
	{
		return [
			'name', 'linkedField'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		$view->registerAssetBundle(\neon\cms\assets\CmsFormAsset::class);
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$form = $this->getParentForm();
		if ($form===null) return;
		$field = $form->getField($this->linkedField);
		$value = $field->getValue();
		if (!is_string($value))
			return ['word_count'=>0, 'time_to_read'=>0];
		// calculate post reading time
		$wordCount = Str::countWords(strip_tags($value));
		$averageWordsPerMinute = 265;
		$minutesToRead = round($wordCount / $averageWordsPerMinute);
		return [
			'minutes_to_read' => $minutesToRead,
			'word_count' => $wordCount
		];
	}

	public function getComponentDetails()
	{
		return [
			'label' => 'Post Stats', 'icon' => 'fa fa-flag-checkered', 'group' => 'Data', 'order'=>820
		];
	}
}