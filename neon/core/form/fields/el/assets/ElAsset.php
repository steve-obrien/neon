<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 20/01/2018
 * @package neon
 */

namespace neon\core\form\fields\el\assets;

use neon\core\web\View;
use yii\web\AssetBundle;

/**
 */
class ElAsset extends AssetBundle
{
	public $sourcePath = __DIR__.'/publish/';
	PUBLIC $css = [
		['element-ui-2.4.1/css/theme-chalk.min.css', 'concatenate'=>'false']
	];
	public $js = [
		['element-ui-2.4.1/element-ui.min.js'],
		['element-ui-2.4.1/locale/en.js'],
	];
	public $depends = [
		'neon\core\assets\CoreAsset'
	];

	public function init()
	{
		neon()->view->registerJs('ELEMENT.locale(ELEMENT.lang.en); Vue.use(ELEMENT);', View::POS_END, 'ElementUIRegister');
	}
}