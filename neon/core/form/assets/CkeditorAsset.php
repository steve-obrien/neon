<?php
/**
 * @copyright Copyright (c) 2013-2015 2amigOS! Consulting Group LLC
 * @link http://2amigos.us
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
namespace neon\core\form\assets;

use yii\web\AssetBundle;
/**
 * CKEditorWidgetAsset
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 * @package dosamigos\ckeditor
 */
class CkeditorAsset extends AssetBundle
{
	public $js = [
		['ckeditor.js', 'concatenate' => 'false'],
		'adapters/jquery.js',
	];

	public $depends = [
		'yii\web\YiiAsset',
		'yii\web\JqueryAsset',
		'neon\firefly\assets\BrowserAsset'
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/vendor/ckeditor';
		parent::init();
	}
}
