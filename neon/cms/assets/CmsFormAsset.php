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
class CmsFormAsset extends AssetBundle
{
	public $sourcePath = __DIR__.'/publish';
	public $js = [
		[YII_DEBUG ? 'neon-cms-form-fields-slug.js' : 'neon-cms-form-fields-slug.build.js'],
		[YII_DEBUG ? 'neon-cms-form-fields-poststats.js' : 'neon-cms-form-fields-poststats.build.js'],
		[YII_DEBUG ? 'neon-cms-form-fields-imagecompare.js' : 'neon-cms-form-fields-imagecompare.js'],
	];
	public $depends = [
		'neon\core\form\assets\FormAsset',
	];
}
