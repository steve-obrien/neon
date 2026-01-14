<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\themes\neon;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Assets extends AssetBundle
{
	public $css = [
		'fa/css/font-awesome.min.css',
		'theme.css',
		'https://fonts.googleapis.com/css?family=Nunito+Sans:200,200i,300,300i,400,400i,600,600i,700,700i,800,900',
	];
	public $sourcePath = __DIR__ . '/assets';
	public $depends = [
		'\neon\core\assets\CoreAsset',
		'\yii\bootstrap\BootstrapAsset',
		'\yii\bootstrap\BootstrapPluginAsset'
	];
	public function init()
	{
		$info = neon()->assetManager->publish($this->sourcePath, $this->publishOptions);
		neon()->view->registerJs('var NEON_THEME_URL = "' . $info[1] . '";', View::POS_HEAD, 'neonThemeData');
	}
}
