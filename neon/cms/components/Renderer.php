<?php

namespace neon\cms\components;

use \Neon;
use neon\cms\services\cmsEditor\Parser;
use neon\core\form\fields\Uuid64;
use neon\core\helpers\Arr;
use neon\core\helpers\Collection;
use neon\core\helpers\Hash;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\core\web\View;
use \Smarty;
use \neon\cms\components\Widget;
use \neon\cms\components\Daedalus;
use \neon\core\view\SmartySharedPlugins;

/**
 * @deprecated 3
 */
Class Renderer extends \neon\core\view\SmartyRenderer
{
	const RENDER_STATE_REGISTRATION = 'RENDER_STATE_REGISTRATION';
	const RENDER_STATE_WIDGETS = 'RENDER_STATE_WIDGETS';

	/**
	 * a cached copy of the page - use getPage() to get this
	 * @var \neon\cms\components\Page
	 */
	private $_page = null;

	/**
	 * Renderer constructor.
	 * @param \neon\cms\components\Page $page
	 * @param array $themeHierarchy
	 * @throws \SmartyException
	 */
	public function __construct($page=null, array $themeHierarchy=[])
	{
		parent::__construct();
		// $this->smarty->escape_html = true;
		$this->setPage($page);
		// Class Constructor.

		$this->smarty->setCompileDir(Neon::getAlias('@runtime/smarty/compile'));
		$this->smarty->setCacheDir(Neon::getAlias('@runtime/smarty/cache'));

		// needs to do the theme look up here.
		if (($theme = neon()->request->get('_theme')) && !neon()->user->isGuest) {
			// override either default or config settings via get
			neon()->cms->setThemeName($theme);
		}


		// add CMS core theme
		$cmsCoreThemeDir = Neon::getAlias('@neon/cms/theme/');
		if (is_string($cmsCoreThemeDir)) {
			$this->smarty->addTemplateDir($cmsCoreThemeDir);
			$this->smarty->addPluginsDir([$cmsCoreThemeDir.'plugins']);
		}
		$this->smarty->setCaching(!neon()->isDevMode());
		// -------------------------
		// Register template plugins
		// -------------------------

		// We have to set cachable to false otherwise it only caches the id output not the full rendering
		// $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'widget', ['\neon\cms\components\Widget', 'factory'], false);
		// $this->smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'editor', ['\neon\cms\components\Editor', 'editor'] , false);
		// $this->smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'drop_zone', ['\neon\cms\components\Editor', 'editor'] , false);
		// $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds', ['\neon\cms\components\Daedalus', 'dds'], false);
		// $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds_choice', ['\neon\cms\components\Daedalus', 'dds_choice'], false);
		// $this->smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'dds_map', ['\neon\cms\components\Daedalus', 'dds_map'], false);
		// Wrap this tag around content to become editable
		// $this->smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'static_block', ['\neon\cms\components\Widget', 'staticBlock']);

		$this->smarty->setCompileCheck(neon()->isDevMode());
		$this->smarty->force_compile = neon()->isDevMode();

		// ensure all errors are thrown when in debug mode
		if (neon()->isDevMode())
			$this->smarty->error_reporting = E_ALL;
	}

	/**
	 * Get smarty object
	 * @return Smarty
	 */
	public function getSmarty()
	{
		return $this->smarty;
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
	 * Render the main template - note this will not render widgets at this phase.
	 * Any rendered widgets will output their id tag instead of their widget content
	 * @param $template
	 * @return mixed
	 */
	public function renderTemplate($template)
	{
		// keep a store of the created templates in case of multiple rendering
		static $templates = [];

		\Neon::beginProfile('COBE::RENDER_TEMPLATE - '.$template, 'cobe');

		// assign any template information from the page
		$this->smarty->assign('page', $this->_page->getPageData());
		$this->smarty->assign('site', $this->_page->getSiteData());

		// an expensive-ish call so reduce need for it in repeated renders
		if (!isset($templates[$template]))
			$templates[$template] = $this->smarty->createTemplate($template);
		$tpl = $templates[$template];

		// reset any parameters for the new render
		$tpl->tpl_vars = $tpl->config_vars = [];
		$ret = $tpl->fetch();

		\Neon::endProfile('COBE::RENDER_TEMPLATE - '.$template, 'cobe');
		return $ret;
	}

	/**
	 * Render a smarty string
	 * @param string $string - the template string
	 * @param bool $saveCompile - whether to save the compiled result - the string md5 itself will be used as the compile key
	 * @return string
	 * @throws \SmartyException
	 */
	public function renderString($string, $saveCompile=true)
	{
		$smartyCmd = $saveCompile ? 'string' : 'eval';
		return $this->smarty->fetch("$smartyCmd:$string");
	}

	/**
	 * Get the cms page object
	 * @return \neon\cms\components\Page
	 */
	public function getPage()
	{
		if (!$this->_page) {
			$this->setPage(neon()->cms->getPage());
		}
		return $this->_page;
	}

	/**
	 * Set the cms page object
	 * @param \neon\cms\components\Page $page
	 */
	public function setPage($page)
	{
		$this->_page = $page;
		Widget::$page = $this->_page;
	}

}
