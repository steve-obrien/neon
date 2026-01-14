<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\cms\assets;

use yii\web\AssetBundle;

/**
 */
class CmsEditorAsset extends AssetBundle
{
	public $sourcePath = __DIR__.'/publish';
	public $js = [ 'static-editor.js', 'dist/neon-editor.umd.js' ];
	public $css = [ 'editor.css', 'dist/neon-editor.css' ];
	public $depends = [
		'yii\web\YiiAsset',
		'neon\core\assets\JQueryUiAsset',
		'neon\core\form\assets\FormAsset',
		'neon\core\form\assets\CkeditorAsset'
	];
}
