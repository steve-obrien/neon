<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\el\assets\ElAsset;
use neon\core\form\fields\Field;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\core\web\UploadedFile;
use \neon\core\grid\query\IQuery;
use neon\firefly\assets\BrowserAsset;

/**
 * Class File - upload multiple files
 * @package neon\core\form
 */
class FileMultiple extends File
{
	public $ddsDataType = 'file_ref_multi';

	/**
	 * Enable multiple file upload
	 * @return bool
	 */
	public function getMultiple()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		if (empty($this->value))
			return Html::encodeEntities($this->value);
		$display = [];
		foreach ($this->value as $file)
			$display[] = Html::a(neon()->firefly->getFileName($file), neon()->firefly->getUrl($file), ['target' => '_blank']);
		return implode(', <br>',$display);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Multi File Uploader', 'icon' => 'fa fa-copy', 'group' => 'Media', 'order' => 621
		];
	}
}