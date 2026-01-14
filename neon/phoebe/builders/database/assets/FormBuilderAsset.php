<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/02/2017 19:06
 * @package neon
 */

namespace neon\phoebe\builders\database\assets;


class FormBuilderAsset extends \yii\web\AssetBundle
{
	public $sourcePath = __DIR__ . '/publish';

	public $js = [
		'store.js',

		// Form builder components
		(YII_DEBUG ? 'form-builder-widget-default.js' : 'form-builder-widget-default.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-default.js' : 'form-builder-field-form-default.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-dynamic-map.js' : 'form-builder-field-form-dynamic-map.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-element-delete.js' : 'form-builder-field-form-element-delete.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-element-list-items.js' : 'form-builder-field-form-element-list-items.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-element-name.js' : 'form-builder-field-form-element-name.build.js'),
		(YII_DEBUG ? 'form-builder-field-form-element-decimal.js' : 'form-builder-field-form-element-decimal.build.js'),
		(YII_DEBUG ? 'form-builder-form-fields.js' : 'form-builder-form-fields.build.js'),
		(YII_DEBUG ? 'form-builder-form-header.js' : 'form-builder-form-header.build.js'),
		(YII_DEBUG ? 'form-builder-field-buttons.js' : 'form-builder-field-buttons.build.js'),
		(YII_DEBUG ? 'form-builder-edit-property.js' : 'form-builder-edit-property.build.js'),
		// -------------------------

		// CDNJS :: Sortable (https://cdnjs.com/)
		'vendor/sortable.min.js',
		'vendor/draggable.min.js',
	];

	public $css = [
		'forms.css',
	];

	public $depends = [
		'neon\core\assets\JQueryUiAsset',
		'neon\core\form\assets\FormAsset'
	];

}
