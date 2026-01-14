<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 25/09/2018
 * Time: 15:11
 */

namespace neon\core\web;


use neon\core\helpers\Arr;
use neon\core\helpers\Str;
use themedefault\ThemeAssets;
use yii\base\Component;
use \neon\core\ApplicationWeb;

class StyleManager extends Component
{
	protected $_styles = [];
	protected $_fields = [];
	protected $_definition = [];

	public function init()
	{
		parent::init();
		neon()->on(ApplicationWeb::EVENT_AFTER_NEON_BOOTSTRAP, [$this, 'loadStyles']);
	}

	public function loadStylesFromConfig()
	{

	}

	public function DiscoverStylesFromBundle($bundle)
	{
		$bundle = new ThemeAssets();
		$view = new View();
		$view->registerAssetBundle($bundle);
		$view->resolveBundles();
		$view->cssConcatenateFiles($view->cssFiles, $key);
		$view->cssProcess = true;
		// parse cssFile for smarty tags
		$view->getCssByKey($key);

	}

	public function getForm()
	{
		$f = new \neon\core\form\Form('style');
		foreach($this->getDefinition() as $field) {
			$f->add($field);
		}
		$f->load($this->getStyles());
		return $f;
	}

	public function loadStyles($key='default')
	{
		return setting('cms', 'styles');
	}

	/**
	 * @param string $name - the name of the css variable
	 * @param string $default - the default value for the variable
	 * @param array|string $type - a form field config to enable editing the property
	 * @param array $options
	 * @return string - the current value for the style if the style variable with he name $name already exists
	 */
	public function add($name, $default, $type, $options=[])
	{
		// check if a style property already exists
		$styleValue = $this->getStyleValue($name, $default);
		$options['name'] = $name;
		if (!isset($options['class']))
			$options['class'] = $type;
		$options['label'] = Arr::get($options, 'label', Str::humanize($name));
		$this->set($name, $styleValue, $options);
		return $styleValue;
	}

	public function getStyleValue($name, $default='')
	{
		$styles = $this->getStyles();
		return Arr::get($styles, $name, $default);
	}

	public function set($name, $value, $field=null, $key='default')
	{
		$styles = $this->getStyles();
		Arr::setValue($styles, $name, $value);
		set_setting('cms', 'styles', $styles);
		if ($field !== null) {
			$definition = $this->getDefinition();
			Arr::setValue($definition, $name, $field);
			set_setting('cms', 'styles_definition', $definition);
		}
	}

	public function save()
	{
		// only save if it has changed
		//if (md5(setting('cms', 'styles_definition')) !== md5($this->_definition)) {
			set_setting('cms', 'styles_definition', $this->_definition);
		//}
		///if (md5(setting('cms', 'styles')) !== md5($this->_styles)) {
			set_setting('cms', 'styles', $this->_styles);
		//}
	}

	/**
	 * @return array
	 */
	public function getStyles()
	{
		return ['color' => '#000'];
//		return setting('cms', 'styles', []);
	}

	/**
	 * @return array
	 */
	public function getDefinition()
	{
		return setting('cms', 'styles_definition', []);
	}
}