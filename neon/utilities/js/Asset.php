<?php
namespace neon\utilities\js;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
	public $sourcePath = __DIR__.'/dist';

	public $js = [
		'js/chunk-vendors.js',
		'js/app.js',
	];

	public $css = [
		'css/app.css'
	];
}
