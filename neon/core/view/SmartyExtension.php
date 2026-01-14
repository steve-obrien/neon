<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */


namespace neon\core\view;

use neon\core\helpers\Str;
use Yii;
use neon\core\helpers\Url;
use \Smarty;
use \neon\core\helpers\Arr;

/**
 * Description of SmartyExtension
 *
 * @author elita
 */
class SmartyExtension extends \yii\smarty\Extension
{
	/**
	 * @var \yii\smarty\ViewRenderer ViewRenderer
	 */
	protected $viewRenderer;
	/**
	 * @var Smarty
	 */
	protected $smarty;


	/**
	 * @param \yii\smarty\ViewRenderer $viewRenderer
	 * @param Smarty $smarty
	 */
	public function __construct($viewRenderer, $smarty)
	{
		$this->viewRenderer = $viewRenderer;
		$smarty = $this->smarty = $smarty;

		$smarty->registerPlugin('function', 'path', [$this, 'functionPath']);
		$smarty->registerPlugin('function', 'set', [$this, 'functionSet']);
		$smarty->registerPlugin('function', 'meta', [$this, 'functionMeta']);
		$smarty->registerPlugin('block', 'title', [$this, 'blockTitle']);
		$smarty->registerPlugin('block', 'description', [$this, 'blockDescription']);
		$smarty->registerPlugin('compiler', 'use', [$this, 'compilerUse']);
		$smarty->registerPlugin('modifier', 'void', [$this, 'modifierVoid']);
		$smarty->registerPlugin('function', 'registerJsFile', [$this, 'functionRegisterJsFile']);
		$smarty->registerPlugin('function', 'registerCssFile', [$this, 'functionRegisterCssFile']);

		// apply common shared plugins between cosmos and core.
		// this is a bit messy as ideally cosmos would use the same renderer as the admin. *.tpl views
		SmartySharedPlugins::apply($viewRenderer, $smarty);
		$this->smarty->registerDefaultPluginHandler([$this, 'pluginHandler']);
		// SmartySharedPlugins overrides the following functions
		$smarty->registerPlugin('function', 'url', [$this, 'functionUrl']);
	}

	/**
	 * Default Plugin Handler
	 *
	 * called when Smarty encounters an undefined tag during compilation
	 *
	 * @param string                     $name    name of the undefined tag
	 * @param string                     $type    tag type (e.g. Smarty::PLUGIN_FUNCTION, Smarty::PLUGIN_BLOCK,
	 *                                   Smarty::PLUGIN_COMPILER, Smarty::PLUGIN_MODIFIER, Smarty::PLUGIN_MODIFIERCOMPILER)
	 * @param Smarty_Internal_Template   $template    template object
	 * @param string                     &$callback    returned function name
	 * @param string                     &$script    optional returned script filepath if function is external
	 * @param bool                       &$cacheable    true by default, set to false if plugin is not cachable (Smarty >= 3.1.8)
	 * @return bool                      true if successfull
	 */
	public function pluginHandler($name, $type, $template, &$callback, &$script, &$cacheable)
	{
		// if already registered and type is block then we need to unregister the name as a function and change to a block this is because:
		// smarty first encounters an unknown opening tag it calls this function and adds the callback as type function
		// then it discovers the closing tag {/tag_name} this time it calls the function again
		// registerFunction___$name - this function is handled by \neon\cms\components\Renderer::__callStatic($name, $args)
		// this then can get the information on the $name from the function.
		switch ($type) {
			case Smarty::PLUGIN_FUNCTION:
				$callback = '\neon\cms\components\Renderer::registerFunction___' . $name;
				$cacheable = false;
				return true;
			default:
				return false;
		}
	}
}
