<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 17/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\firefly\assets;

use neon\core\assets\CoreAsset;
use neon\core\assets\JQueryUiAsset;
use yii\web\AssetBundle;

class BrowserAsset extends AssetBundle
{
	public $sourcePath = __DIR__.'/publish';

	public $js = [
		'vendor/plupload/js/plupload.full.min.js',
		'vendor/cropper/cropper.min.js',
		// 'https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.js',
		// 'https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.js',
		// 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.1/fabric.min.js',
		// 'https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.js',
		'firefly/firefly.js',
		'firefly/store/getters.js',
		'firefly/store/actions.js',
		'firefly/store/mutations.js',
		'firefly/store/store.js',

		// Firefly files to be built
		'firefly/firefly-form-file-list.js',
		'firefly/firefly-form-input-file-upload.js',
		'firefly/firefly-form-file-browser.js',
		'firefly/firefly-form-image.js',
		'firefly/firefly-picker-modal.js',

		'firefly/firefly-item-sidebar.js',
		'firefly/firefly-img.js',

		'firefly/firefly-item-name.js',
		'firefly/firefly-item.js',
		'firefly/firefly-item-parent.js',

		'firefly/editor/firefly-editor-modal.js',
		'firefly/editor/firefly-editor-image.js',
		'firefly/editor/firefly-editor-null.js',

		'firefly/unsplash/firefly-unsplash-modal.js',

		'firefly/firefly-media-inline.js',
		'firefly/firefly-media-uploader.js',
		'firefly/firefly-media-browser.js',
		'firefly/firefly-media-browser-breadcrumb.js',
		'firefly/firefly-item-breadcrumb.js',
		'firefly/firefly-media-browser-toolbar.js',
		'firefly/firefly-media-upload-button.js',
	];
	public $css = [
		'vendor/cropper/cropper.css',
		// 'https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.css',
		// 'https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.css',
		'css/browser.css'
	];

	public $depends = [
		CoreAsset::class,
		JQueryUiAsset::class
	];

}
