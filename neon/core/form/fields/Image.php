<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\helpers\Url;
use \neon\firefly\assets\BrowserAsset;

/**
 * Class Image - upload a single image
 * @package neon\core\form
 */
class Image extends Field
{
	public $ddsDataType = 'image_ref';
	/**
	 * @param boolean
	 */
	public $crop = null;
	/**
	 * Note the width and height for crop is used to set the aspect ratio of the crop window
	 * So setting cropWidth = 1; and cropHeight = 1; would create a square crop window.
	 * Similar values like 16:9  or 4:3 also work.
	 * @param int
	 */
	public $cropWidth = null;
	/**
	 * @param int
	 */
	public $cropHeight = null;
	public $fireflyUrl = null;
	/**
	 * @var string - media browser folder start path
	 */
	public $startPath = '/';

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		BrowserAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function getFireflyUrl()
	{
		return url('/firefly');
	}

	public function setFireflyUrl($value)
	{
		// not currently supported.
	}

	/**
	 * Returns a string uuid64 on success | false on failure | null if no image was uploaded (the field may not be required)
	 * @inheritdoc
	 * @return false|string|null
	 */
	public function getData()
	{
		$name = $this->getInputName();
		if ( neon()->request->hasFile($name) ) {
			$this->value = neon()->request->file($name)->save();
		}
		return $this->value;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['crop', 'cropWidth', 'cropHeight', 'fireflyUrl', 'startPath']);
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		if ($context == 'grid') {
			$url = neon()->firefly->getUrl($this->value);
			return '<img width="50" height="50" class="img-responsive" src="'.$url.'&w=50&h=50" />';
		}
		$url = neon()->firefly->getUrl($this->value);
		return '<img class="img-responsive" src="'.$url.'" />';
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'neon\core\form\fields\filters\File',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return neon()->firefly->save(file_get_contents(faker()->image));
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Single Image Uploader', 'icon' => 'fa fa-image', 'group' => 'Media', 'order' => 610
		];
	}
}
