<?php

namespace neon\core\view;

use neon\cms\components\Theme;
use neon\core\helpers\File;
use neon\core\view\ICanFlushTemplates;
use \Smarty;
use yii\helpers\FileHelper;

class SmartyRenderer extends \yii\smarty\ViewRenderer implements ICanFlushTemplates
{
	public $extensionClass = \neon\core\view\SmartyExtension::class;

	/**
	 * An array of directories within the theme folder to be registered by smarty
	 */
	public $themeTemplateDir = [];
	public $themePluginDirs = [];

	public function init()
	{
		parent::init();
		if (neon()->getApp('cms') === null)
			return;

		$themeAlias = neon()->cms->getThemeAlias();
		$themePath = neon()->getAlias($themeAlias);
		$this->smarty->addTemplateDir($themePath);
		$this->smarty->addTemplateDir("$themePath/widgets");
		$this->smarty->addPluginsDir("$themePath/plugins");

		// -------------------------
		// Register template plugins
		// -------------------------

		// We have to set cachable to false otherwise it only caches the id output not the full rendering
		$this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'widget', ['\neon\cms\components\Widget', 'factory'], false);

		$this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds', ['\neon\cms\components\Daedalus', 'dds'], false);
		$this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds_choice', ['\neon\cms\components\Daedalus', 'dds_choice'], false);
		$this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds_map', ['\neon\cms\components\Daedalus', 'dds_map'], false);

		// Wrap this tag around content to become editable
		// @deprecated
		$this->smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'static_block', ['\neon\cms\components\Widget', 'staticBlock']);

		// ensure all errors are thrown when in debug mode
		if (neon()->isDevMode())
			$this->smarty->error_reporting = E_ALL;
	}

	/**
	 * Renders a view file.
	 *
	 * This method is invoked by [[View]] whenever it tries to render a view.
	 * Child classes must implement this method to render the given view file.
	 *
	 * @param View $view the view object used for rendering the file.
	 * @param string $file the view file.
	 * @param array $params the parameters to be passed to the view file.
	 * @return string the rendering result
	 */
	public function render($view, $file, $params)
	{
		/* @var $template \Smarty_Internal_Template */
		$template = $this->smarty->createTemplate($file, null, null, empty($params) ? null : $params, false);

		// Make Yii params available as smarty config variables
		$template->config_vars = \Yii::$app->params;

		$template->assign('app', \Yii::$app);
		$template->assign('this', $view);

		$html = $template->fetch();
		return $html;
	}

	/**
	 * Handles unknown smarty tags
	 *
	 * @since PHP 5.3
	 * @param $name
	 * @param $arguments
	 * @return string
	 */
	public static function __callStatic($name, $args)
	{
		$bits = explode('___', $name);
		if (count($bits) === 2 && strpos($bits[0], 'registerFunction') === 0) {
			$params = $args[0];
			$params['type'] = $bits[1];
			return \neon\cms\components\Widget::factory($params, $args[1]);
		}
		if (count($bits) === 2 && strpos($bits[0], 'registerBlock') === 0) {
			list($params, $content, $template, $repeat) = $args;
			return self::component($params, $content, $bits[1], $template, $repeat);
		}
	}

	public static function component($params, $content, $tagName, $template, $repeat)
	{
		if ($repeat) return;
		$params['content'] = $content;
		$params['slot'] = $content;
		$params['type'] = $tagName;
		$t = neon()->cms->getThemeBasePath();
		$tpl = $template->smarty->createTemplate("$t/cmps/$tagName.tpl");
		$tpl->assign($params);
		return $tpl->fetch();
		return $template->render();
	}

	/**
	 * Render a smarty string
	 * @param string $string - the template string
	 * @param bool $saveCompile - whether to save the compiled result - the string md5 itself will be used as the compile key
	 * @return string
	 * @throws \SmartyException
	 */
	public function renderString($string, $saveCompile = true)
	{
		$smartyCmd = $saveCompile ? 'string' : 'eval';
		return $this->smarty->fetch("$smartyCmd:$string");
	}

	/**
	 * @inheritDoc
	 * @throws \yii\base\ErrorException
	 */
	public function flushTemplates()
	{
		$this->smarty->clearAllCache();
		$this->smarty->clearCompiledTemplate();
	}
}
