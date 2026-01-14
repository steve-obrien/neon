<?php
namespace neon\core\assets;

use yii\web\AssetBundle;
/**
 *
 */
class JQueryUiAsset extends AssetBundle
{
	public $sourcePath = __DIR__ . '/publish/vendor/jqueryui';

	public $js = [
		'jquery-ui.min.js'
	];
	public $css = [
		'jquery-ui.css',
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];

}
