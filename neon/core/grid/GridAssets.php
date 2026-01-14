<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @date 12/12/2015 17:33
 * @author Steve O'Brien <steve.obrien@newicon.net>
 */

namespace neon\core\grid;

use \yii\web\AssetBundle;

/**
 * Class GridAsset
 * @package neon\grid
 */
class GridAssets extends AssetBundle
{
	public $sourcePath = __DIR__ . '/assets';

	public $js = [
		(YII_DEBUG ? 'neon-grid.js' : 'neon-grid.build.js'),
		'jquery.resizableColumns.min.js'
	];

	public $css = [
		'neon-grid.css'
	];

	public $depends = [
		'\neon\core\form\assets\FormAsset',
	];
}
