<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/02/2017 19:06
 * @package neon
 */

namespace neon\phoebe\builders\appforms\assets;

use neon\core\assets\CoreAsset;

class AppFormBuilderAsset extends \yii\web\AssetBundle
{
	public $sourcePath = __DIR__ . '/publish';

	public $js = [
		'manifest.js',
		'vendor.js',
		'app.js'
	];

	public $css = [
		'app.css',
	];

	public $depends = [
		CoreAsset::class
	];
}
