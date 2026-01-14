<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\core\web\UploadedFile;
use \neon\core\grid\query\IQuery;
use neon\firefly\assets\BrowserAsset;

/**
 * Class File - upload a single file
 * @package neon\core\form
 */
class FileBrowser extends Field
{
	public $ddsDataType = 'file_ref';

	/**
	 * @var string media browser folder start path
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
	public function getFieldHtml()
	{
		return "<div id=\"{$this->getId()}\">" .
			"<firefly-form-file-browser value=\"{$this->value}\" name=\"{$this->getInputName()}\"></firefly-form-file-browser>" .
		"</div>";
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['startPath']);
	}

	/**
	 * Returns a string uuid64 on success | false on failure | null if no image was uploaded (the field may not be required)
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
	public function getValueDisplay($context='')
	{
		return Html::a(neon()->firefly->getFileName($this->value), neon()->firefly->getUrl($this->value), ['target' => '_blank', 'rel' => 'noopener noreferrer']);
	}

	/**
	 * @return array
	 */
	public function getFilterField()
	{
		return [
			'class' => \neon\core\form\fields\filters\File::class,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'File Browser', 'icon' => 'fa fa-file', 'group' => 'Media', 'order' => 622
		];
	}
}