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
 * Class File - upload a single file
 * @package neon\core\form
 */
class File extends Field
{
	public $ddsDataType = 'file_ref';

	/**
	 * Note whether or not we have saved the file
	 * @var boolean
	 */
	private $_fileSaved = false;

	/**
	 * @var string media browser folder start path
	 */
	public $startPath = '/';

	/**
	 * The urls to post upload to, get meta information or download from if not firefly
	 * You can reuse the firefly actions for these by adding the actions to your
	 * controller. E.g.
	 *
	 * ```php
	 * // in your controller:
	 * public function actions()
	 * {
	 *     return [
	 *       'upload' => 'neon\firefly\services\fileManager\actions\UploadAction',
	 *       'meta' => 'neon\firefly\services\fileManager\actions\MetaAction',
	 *       'download' => 'neon\firefly\services\fileManager\actions\DownloadAction'
	 *     ]
	 * }
	 * ```
	 * @var string
	 */
	public $urlUpload = '/firefly/api/file/upload';
	public $urlDownload = '/firefly/api/file/download';
	public $urlMeta = '/firefly/api/file/meta';


	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('firefly-form-input-file-upload', '', [
			'v-bind' => $this->toJson()
		]);
	}

	/**
	 * Whether the component allows multiple uploads
	 * @return bool
	 */
	public function getMultiple()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['startPath', 'urlUpload', 'urlDownload', 'urlMeta', 'multiple']);
	}


	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		BrowserAsset::register($view);
		ElAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		if (empty($this->value))
			return Html::encodeEntities($this->value);
		return Html::a(neon()->firefly->getFileName($this->value), neon()->firefly->getUrl($this->value), ['target' => '_blank']);
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
			'label' => 'Single File Uploader', 'icon' => 'fa fa-file', 'group' => 'Media', 'order' => 620
		];
	}
}