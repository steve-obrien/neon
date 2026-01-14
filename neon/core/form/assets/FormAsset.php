<?php
/**
 * @copyright Copyright (c) 2013-2015
 * @link http://2amigos.us
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
namespace neon\core\form\assets;

use yii\web\AssetBundle;
/**
 */
class FormAsset extends AssetBundle
{
	public $sourcePath = __DIR__;
	public $css = [
		'vendor/selectize/selectize.css',
		'form/form.css',
	];
	public $js = [
		'vendor/selectize/selectize.min.js',
		'vendor/marked/marked.min.js',
		'vendor/cleave/cleave.min.js',
		'vendor/sortable/sortable.min.js',
		'vendor/draggable/vuedraggable.min.js',
		'form/Builder.js',
		'form/Components.js',
		'form/Forms.js',
		'dist/core.js',
		'form/Fields.js',
		(YII_DEBUG ? 'form/Fields-el.js' : 'form/Fields-el.build.js'),
		'form/Store.js',
	];
	public $depends = [
		'neon\core\assets\CoreAsset',
		'yii\validators\ValidationAsset',
		'neon\core\form\fields\el\assets\ElAsset',
		// would like to remove dependency on bootstrap
	];
}
