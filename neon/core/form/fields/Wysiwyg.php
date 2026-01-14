<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\assets\CkeditorAsset;
use neon\core\helpers\Arr;
use neon\core\helpers\Str;
use neon\core\helpers\HtmlPurifier;

/**
 * Class Wysiwyg
 *
 * The wysiwyg components can be extended by setting
 * FILL IN SOMETHING ABOUT HOW TO EXTEND THE PLUGINS
 *
 * @package neon\core\form
 */
class Wysiwyg extends Textarea
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'textlong';

	public $type = 'text';

	/**
	 * If this is true users will have access to firefly's media browser
	 * - the media browser expects neon-administrator
	 * @var bool
	 */
	public $useMediaBrowser = false;

	/**
	 * @var string
	 */
	protected $allowableTags = "<p><a><em><strong><b><i><u><strike><cite><code><ul><ol><li><dl><dt><dd><img><video><source><iframe><table><tbody><tr><td><th><h1><h2><h3><h4><h5><h6><blockquote><hr><span><br><sup><sub><div><mark><pre>";

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		CkeditorAsset::register($view);
	}

	public function getFilterField()
	{
		return ['class' => 'text'];
	}

	public function getProperties()
	{
		$properties = parent::getProperties();
		$properties[] = 'configSets';
		$properties[] = 'useMediaBrowser';
		return $properties;
	}

	public function getConfigSets()
	{
		$defaultConfigSets = [
			'simple' => [
				'toolbar' =>  [
					[
						'name' => 'paragraph',
						'items' => [ 'Bold', 'Italic', 'Underline', 'Link', 'Unlink', 'EmojiPanel', 'Source' ]
					]
				]
			],
			'default' => [
				'toolbar' =>  [
					[ 'name' => 'paragraph', 'items' => [ 'Format', 'Bold', 'Italic', 'Underline', '-', 'Superscript', 'Subscript', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock','-', 'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'] ],
					[ 'name' => 'links', 'items' => [ 'Link', 'Unlink', 'Anchor', '-', 'EmojiPanel' ] ],
					[ 'name' => 'insert', 'items' => [
						'Image', ($this->useMediaBrowser ? 'Fireflyimage' : 'SimpleImageUpload'), 'html5video', '-', 'Table', 'SpecialChar' ] ],
					[ 'name' => 'document', 'items' => ['Maximize' ] ],
					[ 'name' => 'source', 'items' => [ 'RemoveFormat', '-', 'Source' ] ]
				],
				'extraPlugins' => [($this->useMediaBrowser ? 'fireflyimage' : 'simpleImageUpload') , 'html5video','widget','widgetselection','clipboard','lineutils'],
				'extraAllowedContent'=>'img[srcset,sizes,loading]; video[src,controls,autoplay,loop,muted,playsinline,type]; source[src,type]; iframe[!src,width,height,frameborder,allow,title,referrerpolicy,allowfullscreen]'
			]
		];

		$additionalConfigSets = Arr::get(neon('core')->classConfig, '\neon\core\form\fields\Wysiwyg.configSets', []);
		$fullConfig = array_merge_recursive($defaultConfigSets, $additionalConfigSets);
		// convert the extraPlugins into a comma separated list as required by ckeditor
		foreach ($fullConfig as $set=>$config) {
			if (isset($config['extraPlugins']))
				$fullConfig[$set]['extraPlugins'] = implode(',', $config['extraPlugins']);
		}
		return json_encode($fullConfig);
	}


	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		return $this->_value;
		return trim(HtmlPurifier::process($this->_value, function($config) {
			$def = $config->getHTMLDefinition(true);
			$def->addAttribute('img', 'srcset', 'Text');
			$def->addAttribute('img', 'sizes', 'Text');
			$def->addAttribute('img', 'loading', 'Text');
		}));
	}

	/**
	 * @inheritdoc
	 *
	 * Return the purified value but allow tags to be as normal
	 * otherwise will lose ability to edit wysiwyg fields properly
	 */
	public function getValueDisplay($context='')
	{
		if ($context==='grid') {
			return strip_tags(Str::truncate($this->getValue(), 200, '...', null, true));
		}
		return $this->getValue();
	}

	/**
	 * @return array
	 */
	public function getComponentDetails()
	{
		return ['label' => 'Rich Text', 'group' => 'Formatted Text',  'icon' => 'fa fa-font', 'order' => 220];
	}
}
