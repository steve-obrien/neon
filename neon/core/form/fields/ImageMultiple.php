<?php


namespace neon\core\form\fields;


use neon\core\helpers\Html;
use neon\firefly\assets\BrowserAsset;

class ImageMultiple extends Field
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'json';

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'neon\\core\\form\\fields\\Text'];
	}

	public function setValue($value)
	{
		if (is_array($value))
			$this->_value = $value;
		else if (is_string($value))
			$this->_value = json_decode($value);
		else
			$this->_value = null;
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		BrowserAsset::register($view);
	}

	public function getData()
	{
		if (is_string($this->_value)) {
			return json_decode($this->_value);
		}
		return $this->_value;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Multi Image Uploader',
			'icon' => 'fa fa-image',
			'group' => 'Media',
			'order' => 611
		];
	}

	public function getValueDisplay($context='')
	{
		$json = $this->getValue();
		if (empty($json))
			return neon()->formatter->asJson($this->getValue());
		if ($context==='grid') {
			$out = '';
			foreach($json as $image) {
				$url = neon()->firefly->getImage($image, ['w' => '150', 'h' => '150']);
				$out .= "<img src='$url' style='max-width:50px; max-height:50px;' />";
			}
			return $out;
		}
		return neon()->formatter->asJson($this->getValue());
	}
}
