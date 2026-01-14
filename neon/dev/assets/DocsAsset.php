<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 03/03/2019
 * @package neon
 */

namespace neon\dev\assets;

class DocsAsset extends \yii\web\AssetBundle
{
	public $sourcePath = __DIR__ . '/publish';

	public $css = ['docs.css'];
	public $js = ['vendor/highlight.js'];
}