<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 17/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\core\assets;

use neon\core\helpers\Url;
use neon\core\web\View;
use yii\web\AssetBundle;

/**
 * Core Assets loads javascript core framework assets for the admin area app functionality.
 *
 * @package neon\core\assets
 */
class CoreAsset extends AssetBundle
{
	public $sourcePath = __DIR__ . '/publish';

	public $js = [
		(YII_DEBUG ? 'vendor/vue.js' : 'vendor/vue.min.js'),
		(YII_DEBUG ? 'vendor/vuex.js' : 'vendor/vuex.min.js'),
		'vendor/lodash.min.js',
		(YII_DEBUG ? 'neon.js' : 'neon.build.js'),
		(YII_DEBUG ? 'modal/index.js' : 'modal/index.build.js'),
		(YII_DEBUG ? 'modal/neon-modal-resizer.js' : 'modal/neon-modal-resizer.build.js'),
		(YII_DEBUG ? 'modal/neon-modal.js' : 'modal/neon-modal.build.js'),
		(YII_DEBUG ? 'modal/neon-modal-manager.js' : 'modal/neon-modal-manager.build.js'),
	];

	public $css = [
		'modal/modal.css',
	];

	public $depends = [
		'\yii\web\YiiAsset', // yii adds $.ajaxPrefilter for CSRF token
		'\yii\web\JqueryAsset',
	];

	public function init()
	{
		// Pass system data to neon js
		$neonData = [
			'host' => Url::base(true),
			'base' => Url::base(),
			'debug' => env('NEON_DEBUG'),
			'csrfParam' => neon()->request->csrfParam,
			'csrfToken' => neon()->request->csrfToken,
			'urlSuffix' => !empty(neon()->urlManager->suffix) ? neon()->urlManager->suffix : '',
		];
		neon()->view->registerJs('window.NEON_DATA = '.json_encode($neonData), View::POS_HEAD, 'neonData');
	}
}