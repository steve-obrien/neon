<?php

/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 17/03/2017
 * Time: 13:14
 */

namespace neon\core\view;

use \Neon;
use neon\core\helpers\Html;
use neon\core\helpers\Hash;
use neon\core\helpers\Page;
use neon\core\helpers\Str;
use neon\core\helpers\Url;
use neon\core\helpers\Arr;
use neon\core\validators\NumberValidator;
use neon\core\web\View;

use \Smarty;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class SmartySharedPlugins
 * This class provides smarty plugins that can be shared between the admin smarty renderer and cosmos
 * @package neon\core\view
 */
class SmartySharedPlugins
{
	/**
	 * Default set of images breakpoints to be availablle for srcsets generated automatically
	 * @todo NJ-20210625 - see if can determine 'ideal' default set of widths for images. Using the idea of
	 * constant wasted bytes between widths, should have more widths as the images get larger. Using performance
	 * budget (no image breakpoint more than e.g. 20K than the previous) epends on the actual image.
	 * @see https://cloudfour.com/thinks/responsive-images-101-part-9-image-breakpoints/
	 */
	const IMG_SRCSET_DEFAULT_WIDTHS = "400,800,1000,1400,2000,2600";

	/**
	 * Apply these plugin functions to a smarty object
	 * @param mixed $parent
	 * @param Smarty $smarty
	 * @throws \SmartyException
	 */
	public static function apply($parent, $smarty)
	{
		new self($parent, $smarty);
	}

	/**
	 * Maintain a list of tags that will be deprecated so we can aid upgrades and provide sensible error messages
	 * @return array
	 */
	public static function deprecatedTags()
	{
		return ['cosmos_head_script', 'cosmos_body_script'];
	}

	/**
	 * SmartySharedPlugins constructor.
	 * @param mixed $parent - should implement a parent page interface
	 * @param \Smarty $smarty
	 * @throws \SmartyException
	 */
	public function __construct($parent, $smarty)
	{
		// app plugin smarty directories
		$coreApps = neon()->coreApps;
		foreach ($coreApps as $key => $app) {
			$smarty->addPluginsDir(Neon::getAlias("@neon/$key/plugins/smarty"));
		}
		// plugins
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'firefly_url', [$this, 'firefly_url']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'firefly_image', [$this, 'firefly_image']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'image', [$this, 'image']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'image_src', [$this, 'image_src']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'image_alt', [$this, 'image_alt']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'image_srcset', [$this, 'image_srcset']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'setting', [$this, 'setting']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'time', [$this, 'time']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'csrf_element', [$this, 'csrf_element']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'csrf_meta_tags', [$this, 'csrf_meta_tags']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'registerAsset', [$this, 'registerAsset']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'jsFile', [$this, 'jsFile']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'cssFile', [$this, 'cssFile']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'hasPermission', [$this, 'hasPermission']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'style', [$this, 'style']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'uuid', [$this, 'uuid']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'asset', [$this, 'asset']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'assets', [$this, 'asset']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'is_logged_in', [$this, 'is_logged_in']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'is_debug', [$this, 'is_debug']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'page_url', [$this, 'page_url']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'on_page', [$this, 'on_page']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'canonical', [$this, 'canonical']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'googleCode', [$this, 'googleCode']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'protectHtmlEntities', [$this, 'protectHtmlEntities']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'favicon', [$this, 'favicon']);
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'trim', [$this, 'trim']);
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'dp', 'dp');
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'dd', 'dd');

		// modifiers
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'htmlAttributes', [$this, 'htmlAttributes']);
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'humanize', '\neon\core\helpers\Str::humanize');
		$smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'json', [$this, 'json']);

		// blocks
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'markdown', [$this, 'markdown']);
		// All the same - ways to register a block of js :
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'script', [$this, 'js']);
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'js', [$this, 'js']);
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'Js', [$this, 'js']);
		// Register a block of css
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'css', [$this, 'css']);

		// experimental
		$smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'cmp', [$this, 'cmp']);

		// $smarty->registerPlugin('function', 'url', [$this, 'url']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'abort', $this->smartyFunctionMapper('abort'));
		// $smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'setting', $this->smartyFunctionMapper('setting'));
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'env', $this->smartyFunctionMapper('env'));
		// deprecated tags
		$this->registerDeprecatedTags($smarty);
	}

	/**
	 * Maps a php global php function to smarty tag.
	 * PHP functions must be explicitly registered by newer smarty versions.
	 * 
	 * This converts the php function arguments to smarty attributes.
	 * 
	 * ```php
	 * abort($code, $message="Not found")
	 * 
	 * $smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'abort', $this->smartyFunctionMapper('abort'));
	 * ```
	 * 
	 * {abort code=404}
	 * 
	 * For other functions that return data there is an additional "assign" param, 
	 * when present the output will be assigned to the smarty template variable instead of returend.
	 * 
	 * 
	 * @param string $functionName the name/callable of the php global function to map
	 */
	public function smartyFunctionMapper($functionName) {

		return function ($params, $template) use ($functionName) {
			// Use Reflection to determine how to call the PHP function
			$function = new \ReflectionFunction($functionName);
			$args = [];
			foreach ($function->getParameters() as $param) {
				$paramName = $param->getName();
				if (isset($params[$paramName])) {
					$args[] = $params[$paramName];
				} elseif ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					throw new \InvalidArgumentException("Missing argument: $paramName");
				}
			}
			if (isset($params['assign'])) {
				$template->assign($params['assign'], $function->invokeArgs($args));
				return '';
			}
			// Call the function with the mapped arguments
			return $function->invokeArgs($args);
		};
	}

	/**
	 * tag: setting
	 *
	 * Get a setting value from the settings manager
	 *
	 * ```{setting app='aaa' name='nnn' [default='xxx' assign='yyy']}```
	 *
	 * @param array $params
	 *   app - required - the settings app area
	 *   name - required - the setting required
	 *   default - optional - a default value if it's not set
	 *   assign - optional - if set assigns to this variable else returns the value
	 * @param object $smarty
	 * @return string
	 */
	public function setting($params, $smarty)
	{
		$app = isset($params['app']) ? $params['app'] : null;
		$name = isset($params['name']) ? $params['name'] : null;
		$default = isset($params['default']) ? $params['default'] : null;
		$assign = isset($params['assign']) ? $params['assign'] : null;
		if ($app && $name) {
			$value = neon()->settings->manager->get($app, $name, $default);
			if ($assign) {
				$smarty->assign($assign, $value);
				return '';
			}
			return $value;
		} else {
			return "Usage: you must provide an 'app' and a 'name' parameter. You provided " . print_r($params, true);
		}
	}
	
	/**
	 * Output the standard gtag google code - if a google_analytics code is provided
	 * or a google tag manager code is provided (GTM-) for tag manager.
	 * It starts with UA for standard analytics.
	 * If Google tag manager is installed - you do not need the analytics code.
	 * https://developers.google.com/tag-manager/quickstart
	 * https://developers.google.com/gtagjs/devguide/snippet#google-analytics
	 */
	public function googleCode($params)
	{
		$id = setting('cms', 'google_code');
		if (empty($id)) return '';
		// if using google tag manager
		if (substr($id, 0, 3) === 'GTM') {
			$head = <<<HEADJS
				<!-- Google Tag Manager -->
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','$id');</script>
				<!-- End Google Tag Manager -->
			HEADJS;
			$body = <<<BODYJS
				<!-- Google Tag Manager (noscript) -->
				<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=$id" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<!-- End Google Tag Manager (noscript) -->
			BODYJS;
			neon()->view->registerHtml($head, View::POS_HEAD);
			neon()->view->registerHtml($body, View::POS_END);
			return '';
		}
		// else we use standard gtag
		// this can accept ID's like GA_MEASUREMENT_ID / AW-CONVERSION_ID / DC-FLOODIGHT_ID / UA-XXX
		neon()->view->registerHtml("<script async src=\"https://www.googletagmanager.com/gtag/js?id=$id\"></script>\n"
			. "<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);};gtag('js', new Date());gtag('config', '$id');</script>", View::POS_HEAD);
	}

	/**
	 * Generate the html meta tags for a favicon
	 */
	public function favicon()
	{
		return Html::favicon(setting('cms', 'favicon'));
	}

	public function trim($string)
	{
		return trim($string);
	}

	public function googleTagManager()
	{
		$id = setting('cms', 'google_analytics');
	}

	/**
	 * @param \Smarty $smarty
	 * @throws \SmartyException
	 */
	public function registerDeprecatedTags($smarty)
	{
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'cosmos_head_script', [$this, 'neonHead']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'cosmos_body_script', [$this, 'neonBodyEnd']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'neon_head', [$this, 'neonHead']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'neon_body_begin', [$this, 'neonBodyBegin']);
		$smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'neon_body_end', [$this, 'neonBodyEnd']);
	}

	/**
	 * Parse a block of content as markdown
	 *
	 * ```smarty
	 * {markdown} ***markdown content*** {/markdown}
	 * ```
	 *
	 * @param $params
	 * @param $content
	 * @return string
	 */
	public function markdown($params, $content)
	{
		$parser = new MarkdownNeon();
		return $parser->parse($content);
	}

	/**
	 * Gets the url for a canonical version of the current url
	 * This also uses the cms canonical setting otherwise uses getHostInfo
	 *
	 * @return string - the url
	 */
	public function canonical($params, $template)
	{
		return neon()->urlManager->getCanonical();
	}

	/**
	 * See if a user is logged in
	 * usage: {if {is_logged_in}} ...
	 * @return boolean
	 */
	public function is_logged_in($params, $smarty)
	{
		return neon()->user->isGuest === false;
	}

	/**
	 * If in debug mode
	 * @return string
	 */
	public function is_debug($params, $smarty)
	{
		return neon()->debug ? 'true' : 'false';
	}

	/**
	 * Will force copy assets when in dev mode
	 * This is specific to the cms module
	 * ```{asset path="/path/within/assets/theme/directory"}```
	 * ```{asset path="http://something"}``` - the path is output as is this is useful when either an internal or external image
	 * can be supplied to a component
	 *
	 * @param $params
	 * @return string
	 */
	public function asset($params)
	{
		$path = Arr::getRequired($params, 'path');
		return neon()->cms->themeAssetUrl($path);
	}

	/**
	 * @param $params
	 * @param $content
	 * @param $template
	 * @param $repeat
	 * @return string
	 */
	public function cmp($params, $content, $template, &$repeat)
	{
		// only output on the closing tag
		if (!$repeat) {
			$class = Arr::remove($params, 'class');
			$params['content'] = $content;
			if (class_exists($class)) {
				$widget = new $class();
				foreach ($params as $key => $val) {
					if ($widget->hasProperty($key))
						$widget->$key = $val;
				}
				return $widget->run();
			} else {
				return "<div class=\"neonWidgetError alert alert-danger\">Widget class '$class' not found in cmp plugin</div>";
			}
		}
	}

	/**
	 * Register a style configurable parameter
	 *
	 * @param array $params
	 * @param Smarty $smarty
	 */
	public function style($params, $smarty)
	{
		// possible vue style implementation
		//		Arr::props($params, [
		//			'name' => ['required' => true, 'type' => 'string'],
		//			'type' => ['required' => true, 'type' => 'string'],
		//			'options' => ['required' => false, 'type' => 'array', 'default' => []]
		//		]);

		$name = Arr::getRequired($params, 'name');
		$type = Arr::getRequired($params, 'type');
		$options = Arr::get($params, 'options', []);
		if (!is_array($options))
			throw new \InvalidArgumentException('"options" property must be an array "' . gettype($options) . '" given');
		$default = Arr::get($params, 'default', '');

		$value = neon()->view->styleManager->add($name, $default, $type, $options);
		$smarty->assign($name, $value);
	}

	/**
	 * Function to expand an array into html attributes
	 *
	 * @param array $params
	 * @param Object $smarty
	 *
	 * @return string
	 */
	public function htmlAttributes($params, $smarty)
	{
		dp($params);
	}

	/**
	 * Smarty template function to get absolute URL for using in links
	 *
	 * Usage is the following:
	 *
	 * {url route='blog/view' alias=$post.alias user=$user.id}
	 *
	 * where route is Yii route and the rest of parameters are passed as is.
	 *
	 * @param array $params
	 * @return string
	 */
	public function url($params)
	{
		if (!isset($params['route'])) {
			trigger_error("path: missing 'route' parameter");
		}
		array_unshift($params, $params['route']);
		unset($params['route']);
		return Url::toRoute($params, true);
	}

	/**
	 * tag: csrf_element
	 *
	 * Output a form element with the csrf key in it.
	 * This is a hidden field and allows the form to post to yii
	 *
	 * ```{csrf_element}```
	 *
	 * @param array $params ['id']
	 * @return string html
	 */
	public function csrf_element($params)
	{
		return '<input type="hidden" name="' . neon()->request->csrfParam . '" value="' . neon()->request->getCsrfToken() . '" />';
	}

	/**
	 * Output Csrf meta tags
	 * @param $params
	 * @param $smarty
	 * @return string
	 */
	public function csrf_meta_tags($params, $smarty)
	{
		return Html::csrfMetaTags();
	}

	/**
	 * tag: firefly_url
	 *
	 * Get a url for a file managed by firefly from it's id
	 *
	 * ```{firefly_url id="the file uuid"}```
	 *
	 * @param array $params ['id']
	 * @return string  the URL to the file
	 * @throws \InvalidArgumentException
	 */
	public function firefly_url($params)
	{
		if (isset($params['id']))
			return neon()->firefly->getUrl($params['id']);
	}

	/**
	 * tag: firefly_image
	 *
	 * Get a url for an image managed by firefly from it's id
	 *
	 * ```{firefly_url id="the file uuid" ...}```
	 *
	 * @param array $params
	 *   'id' - the image id
	 *   'w' - the image width
	 *   'h' - the image height
	 *   'q' - quality of compression (0-100)
	 *   'fit' - http://image.intervention.io/api/fit a string can be one of 'top-left|top|top-right|left|center|right|bottom-left|bottom|bottom-right';
	 *           note if not one of those values but is truthy then 'center' will be used for example if ?fit=1 
	 *           then center will be applied
	 *   'rotate' - angle to rotate image by
	 *   'flip' - flip image horizontally (h) or vertically (v)
	 *   'crop' - crop the image
	 *   'filter' - crop the image
	 *   'f' - image format to convert to 'webp' | 'jpg'
	 *
	 * @return string  the URL to the file
	 * @throws \InvalidArgumentException
	 */
	public function firefly_image($params)
	{
		$params = Arr::only($params, ['id', 'w', 'h', 'q', 'fit', 'rotate', 'crop', 'filter', 'f']);
		$params['q'] = Arr::get($params, 'q', '90');
		if (isset($params['id'])) {
			if (!Hash::isUuid64($params['id'])) {
				$params['id'] = $this->asset(['path' => $params['id']]);
			}
			return neon()->firefly->getImage(Arr::pull($params, 'id'), $params);
		}
	}

	/**
	 * tag: page_url
	 *
	 * Get a url for a page from it's id
	 * Usage:
	 *
	 * ```
	 *   {page_url} for the url of the current page
	 *   {page_url id="the page id"} if you know the page id
	 *   {page_url nice="the page nice id"} if you know the nice id
	 * ```
	 * Optional parameters:
	 *   full=true if you want the full URL include server
	 *   prefix=string  if you want to prefix the url with something
	 *     e.g. for alternative permissions on routing
	 *
	 * @param array $params
	 * @param object $smarty
	 * @throws \InvalidArgumentException if no id or nice parameter defined
	 * @return string  the page URL
	 */
	public function page_url($params, $smarty = null)
	{
		// get the nice_id by either 'id' or 'nice' - should deprecate usage of 'nice' in future versions
		$id = Arr::get($params, 'id', Arr::get($params, 'nice', false));
		if ($id === false)
			throw new \InvalidArgumentException('No id or nice parameter was specified');

		// this is a bit strange calling {page_url id=#} returns '#' - keeping for backwards compatibility
		if ($id === '#') return '#';

		// whether to make this url absolute defaults to false
		$absolute = Arr::get($params, 'full', false);

		// the unique route of the page is:
		$url = url(['/cms/render/page', 'nice_id' => $id], $absolute);

		if (isset($params['prefix']))
			$url = $params['prefix'] . $url;

		return $url;
	}

	/**
	 * Determine if we are on the current page.
	 * @see Url::isUrl
	 * Usage:
	 * ```
	 *   {on_page id='my-page-id'} for the url of the current page
	 *   {on_page id=['my-page', 'home', 'uuid']} if you know the page id
	 * ```
	 * @param $params
	 * @return boolean - if the current url matches one of the page urls
	 * @throws \yii\base\InvalidConfigException
	 */
	public function on_page($params)
	{
		$pages = Arr::getRequired($params, 'id');
		$allPages = is_array($pages) ? $pages : [$pages];
		$urls = [];
		foreach ($allPages as $page) $urls[] =  $this->page_url(['id' => $page]);
		return Url::isUrl($urls);
	}

	/**
	 * Produce a firefly url for the asset
	 * @param $params
	 * @param $params['src'] - the firefly id or local asset path
	 * @return string
	 */
	public function image_src($params)
	{
		$src = Arr::getRequired($params, 'src');
		if (!Hash::isUuid64($src))
			$params['src'] = $this->asset(['path' => $params['src']]);
		$params['f'] = Arr::get($params, 'f', 'webp');
		return neon()->firefly->getImage(Arr::pull($params, 'src'), $params);
	}

	/**
	 * Lookup an images src attribute
	 *
	 * @param $params - contains a 'src' parameter
	 * @return mixed|string
	 */
	public function image_alt($params)
	{
		$uuid = Arr::getRequired($params, 'src');
		// we can't lookup alt info for images loaded form disk atm.
		// a images loaded from disk still get pulled through fireflys imageManager - we could add these
		// to a firefly holding area - you could then decide whether to import into firefly at this point
		// the image would get a uuid - but it would also still need to be found by its local disk path.
		// Therefore firefly would need to be expanded with the concept of a duel lookup - this feels knarly.
		// probably makes sense for a seperate system to manage these files.
		if (!Hash::isUuid64($uuid))
			return '';
		return Arr::get(neon()->firefly->getMeta($uuid), 'alt', '');
	}

	/**
	 * tag: image
	 *
	 * Gets a string suitable for placing inside an image tag with responsive srcsets
	 *
	 * ```{image src="the file uuid" ...}```
	 *
	 * Examples:
	 * ```{image src='z08MMpEHcW27bd_diYLIMw'}```
	 * ```{image src='z08MMpEHcW27bd_diYLIMw' id='image-tag-id' alt='image alt text' style='object-fit:cover' class='imgsetclass'}```
	 * ```{image src='z08MMpEHcW27bd_diYLIMw' sizes='(max-width 600px) 100vw, 50vw'}```
	 * ```{image src='z08MMpEHcW27bd_diYLIMw' widths='300,500,900' sizes='(max-width 600px) 100vw, 50vw'}```
	 *
	 * @todo: consider supporting additional firefly parameters
	 * @param $params
	 *    'src'   - the image id
	 * // firefly transformation props (passed to @see $this->firefly_image())
	 *    (optional) 'q'           - quality parameter is passed through as a firefly q parameter @see firefly_image
	 *    (optional) 'w'           - width 
	 *    (optional) 'h'           - define a max height
	 *    (optional) 'fit'         - 0/1 'center|top|bottom|etc'
	 *    (optional) 'widths'      - comma separated list of widths in pixels as ints - if unspecified a sensible list of defaults is generated
	 *    (optional) 'src-width'   - width to use for the img src (if unspecified the the smallest from widths will be used) in pixels as int
	 *    (optional) 'srcset-name' - when using js based lazy loading you will often need to rename the srcset attribute
	 *    (optional) 'src-name'    - when using js based lazy loading you will often need to rename the src attribute,
	 *                    allowing js to to detect and create the correct attribute when necessary
	 *    + any other parameters passed in will be built into the resulting tag string (these are not validated)
	 * @return string the srcset string
	 * @throws \InvalidArgumentException
	 * @see https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
	 */
	public function image($params)
	{
		// If the src is empty then it will display a broken image which is good enough feedback.
		// And much better than an optuse smarty error.
		// if (!isset($params['firefly_id']) && !isset($params['src'])) {
		// throw new \InvalidArgumentException("'firefly_id' or 'src' is a required parameter");
		//}

		$id = Arr::pull($params, 'firefly_id', Arr::pull($params, 'src'));
		$srcWidth = Arr::pull($params, 'src-width', 200);

		$html = [];
		$params['loading'] = Arr::get($params, 'loading', 'lazy');
		if (!Hash::isUuid64($id)) {
			// if a local image asset path is provided then we just use that.
			$params[Arr::get($params, 'srcset-name', 'srcset')] = Arr::get($params, 'srcset', self::asset(['path' => $id]));
			$params[Arr::get($params, 'src-name', 'src')] = self::asset(['path' => $id]);
		} else {
			// if firefly id then regenerate the images so we get lots of nice srcset paths
			$params[Arr::get($params, 'srcset-name', 'srcset')] = Arr::get($params, 'srcset', $this->image_srcset(array_merge(['src' => $id, 'f'=>'webp'], $params)));
			$params[Arr::get($params, 'src-name', 'src')] = $this->firefly_image(array_merge(['id' => $id, 'w' => (int)$srcWidth, 'f'=>'webp'], array_merge($params, ['q'=>10])));
		}


		// alt
		$params['alt'] = Arr::get($params, 'alt', $this->image_alt(['src' => $id]));
		$params['sizes'] = Arr::get($params, 'sizes', '100vw');

		if (Str::endsWith($params['src'], '.mp4')) {
			return '<video ' . Html::renderTagAttributes($params) . '/>';
		}
		return '<img ' . Html::renderTagAttributes($params) . '/>';
	}

	/**
	 * Generates a srcset string
	 * 
	 * @param $params
	 *    'src' - the image id
	 *    'quality' - quality parameter is passed through as a firefly q parameter
	 *    'widths' - a set of widths to use for the image. If not provided the IMG_SRCSET_DEFAULT_WIDTHS will be used
	 *       This creates a srcset entry for each width provided. E.g '100,300,500'
	 * @return string
	 */
	public function image_srcset($params)
	{
		$src = Arr::getRequired($params, 'src');
		// if this is not a firefly id then assume it is a path loaded via asset
		if (!Hash::isUuid64($src)) $src = $this->asset(['path' => $src]);
		$sources = $this->generateSrcset($src, Arr::get($params, 'widths'), $params);
		$srcset = '';
		foreach ($sources as $width => $url) $srcset .= $url . ' ' . $width . 'w, ';
		return $srcset;
	}

	/**
	 * Generate a sensible default srcset attribute string
	 * @param string $id - the firefly image id
	 * @param null $widths
	 * @param int $imgOptions - 
	 * @return array
	 */
	public function generateSrcset($id, $widths = null, $imgOptions = [])
	{
		$widths = $widths ?: self::IMG_SRCSET_DEFAULT_WIDTHS;
		// validate and collate the widths
		$widths = array_map('trim', explode(',', $widths));
		$srcsetParts = [];
		foreach ($widths as $idx => $width) {
			if (!is_numeric($width)) throw new \InvalidArgumentException("One of the widths specified was not numeric '" . $width . "'");
			$img = Arr::defaults($imgOptions, ['id' => $id, 'q' => 90, 'w' => (int) $width]);
			$imageUrl = $this->firefly_image($img);
			$srcsetParts[$width] = $imageUrl;
		}
		return $srcsetParts;
	}

	/**
	 * tag: time
	 *
	 * Convert a timestamp into a YYYY-MM-DD HH:MM:SS string
	 *
	 * Usage:
	 * ```
	 *   {time} - returns the current time
	 *   {time stamp='123456'} - returns the timestamp formatted
	 * ```
	 *
	 * @param $params empty for now and integer timestamp otherwise
	 * @return string YYYY-MM-DD HH:MM:SS
	 */
	public function time($params)
	{
		$format = 'Y-m-d H:i:s';
		if (isset($params['stamp']))
			return date($format, (int)$params['stamp']);
		return date($format);
	}

	/**
	 * Output a pre tag of nicely formatted json - useful for debugging
	 *
	 * @param $params
	 * @return string
	 */
	public function json($params)
	{
		return '<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>';
	}

	/**
	 * Looks for a `position` or `pos` key in the params and returns the integer value for the string position
	 * ```
	 * $this->getAssetPosition(['pos' => 'end']) // gives: 3
	 * ```
	 * @see SmartySharedPlugins::registerAsset
	 * @see SmartySharedPlugins::js()
	 * @param array $params
	 * @param string $default - defaults to 'end'
	 * @return int
	 * @throws \Exception
	 */
	public function getAssetPosition($params, $default = 'end')
	{
		$position = Arr::get($params, 'pos', Arr::get($params, 'position', $default));
		$positions = [
			'head'  => View::POS_HEAD,
			'begin' => View::POS_BEGIN,
			'end'   => View::POS_END,
			'ready' => View::POS_READY,
			'load'  => View::POS_LOAD,
		];
		if (!array_key_exists($position, $positions)) {
			throw new \Exception('The javascript position specified must be one of ' . print_r(array_keys($positions)) . ', you specified "' . $position . '"');
		}
		return $positions[$position];
	}

	/**
	 * Smarty block function plugin
	 * Usage is the following:
	 *
	 * ```
	 * {Js} Javascript code here {/Js}
	 * {Js pos='end'} Javascript code here {/Js}
	 * {Js pos='ready'} jquery on ready code here {/Js}
	 * ```
	 *
	 * @param $params
	 *
	 *   [position|pos] with values:
	 *   The position param specified where the javascript block will be rendered on the page it can have
	 *   the following values:
	 *
	 *   - "head"  : This means the javascript is rendered in the head section.
	 *   - "begin" : This means the javascript is rendered at the beginning of the body section.
	 *   - "end"   : This means the javascript is rendered at the end of the body section.
	 *   - "ready" : This means the JavaScript code block will be enclosed within `jQuery(document).ready()`.
	 *   - "load"  : This means the JavaScript code block will be enclosed within `jQuery(window).load()`.
	 *
	 *
	 * @param $content
	 * @return void
	 * @throws \Exception
	 */
	public function js($params, $content)
	{
		if ($content == null) return;
		$key = Arr::get($params, 'key', null);
		$content = str_replace(['<script>', '</script>'], '', $content);
		neon()->view->registerJs($content, $this->getAssetPosition($params), $key);
	}

	/**
	 * Register a block of css code
	 *
	 * ```
	 * {css position='head'}
	 * .myStyle {color:'red'}
	 * {/css}
	 * ```
	 *
	 * @param $params
	 * [position|pos] string - the string position in the page where css should be output - defaults to 'head'
	 * [key] string - a key to uniquely identify this css block so it will not be rendered twice
	 * @param $content
	 * @throws \Exception
	 */
	public function css($params, $content)
	{
		if ($content == null) return;
		$key = Arr::get($params, 'key', null);
		$content = str_replace(['<style>', '</style>'], '', $content);
		neon()->view->registerCss($content, ['position' => $this->getAssetPosition($params, 'head')], $key);
	}

	/**
	 * Register Asset bundle
	 *
	 * ```
	 * {registerAsset name='\yii\web\JqueryAsset'} // jquery will be loaded before the end body tag
	 * {registerAsset path='\yii\web\JqueryAsset' pos='end'} // jquery will be loaded before the end body tag
	 * {registerAsset path='\yii\web\ThemeBootstrap' assignUrl='themeUrl'} // jquery will be loaded before the end body tag
	 *```
	 *
	 * @param array $params
	 * - [name|path] {string} = a asset bundle path to register for e.g. \yii\web\JqueryAsset
	 * - [pos|position] {string:head|begin|end|ready|load} = the position where the bundle should be output on the page defaults to 'end'
	 * @param Smarty $smarty
	 * @throws InvalidConfigException if the asset bundle does not exist or a circular dependency is detected
	 * @throws \Exception if incorrect asset position is given
	 * @return void
	 */
	public function registerAsset($params, $smarty)
	{
		// get the bundle - will look for `name` or `bundle` or `path` keys
		$class = Arr::get($params, 'name', Arr::get($params, 'path', null));
		if ($class === null) {
			trigger_error("registerAsset: missing 'name' or 'path' parameter");
		}

		// get the position - will looks for `pos` or `position` keys - defaults View::POS_END
		$position = Arr::get($params, 'pos', Arr::get($params, 'position', 'end'));

		$bundle = neon()->view->registerAssetBundle($class, $this->getAssetPosition($position));
		$assign = Arr::get($params, 'assignUrl', Arr::get($params, 'assign', false));
		$smarty->assign($assign, $bundle->baseUrl);
	}

	/**
	 * @param array $params
	 * - [src|url]    string - the js src attribute
	 * - [attributes] array  - additional html attributes for the script tag also supports the special `depends`
	 *                           attribute enabling this file to depend on an asset bundle
	 * - [depends]    string - asset bundle dependencies
	 * - [key]        string - key to uniquely identify this file - optional
	 */
	public static function jsFile($params)
	{
		$src = Arr::get($params, 'src', Arr::get($params, 'url', null));
		if ($src === null) {
			trigger_error("jsFile: missing 'url' or 'src' parameter");
		}
		$attributes = Arr::get($params, 'attributes', []);
		$attributes['depends'] = Arr::get($params, 'depends', null);
		$key = Arr::get($params, 'key', null);
		neon()->view->registerJsFile($src, $attributes, $key);
	}

	/**
	 * @param array $params
	 * - [src|url]    string - the js src attribute
	 * - [attributes] array  - additional html attributes for the script tag also supports the special `depends`
	 *                           attribute enabling this file to depend on an asset bundle
	 * - [depends]    string - asset bundle dependencies
	 * - [key]        string - key to uniquely identify this file - optional
	 */
	public static function cssFile($params)
	{
		$src = Arr::remove($params, 'src', Arr::remove($params, 'url', null));
		if ($src === null) {
			trigger_error("jsFile: missing 'url' or 'src' parameter");
		}
		$attributes = Arr::remove($params, 'attributes', []);
		$key = Arr::remove($params, 'key', null);
		neon()->view->registerCssFile($src, array_merge($params, $attributes), $key);
	}


	/**
	 * Generates a UUID
	 * return string $UUID
	 */
	public function uuid($params, $template)
	{
		return Hash::uuid64();
	}

	/**
	 * Outputs scripts at the head
	 * - this tag marks where the - 'head' - View::POS_HEAD - position is
	 * @deprecated 4 use {$this->head()} in templates
	 */
	public function neonHead()
	{
		return neon()->view->head();
	}

	/**
	 * Outputs scripts after the first body tag
	 * - this tag marks where the - 'begin' - View::POS_BEGIN - position is
	 * @deprecated 4 use {$this->beginBody()} in templates
	 */
	public function neonBodyBegin()
	{
		return neon()->view->beginBody();
	}

	/**
	 * Outputs scripts at the end body tag position
	 * - this tag marks where the - 'end' - View::POS_END - position is
	 *  @deprecated 4 use {$this->endBody()} in templates
	 */
	public function neonBodyEnd()
	{
		return neon()->view->endBody();
	}

	/**
	 * check if a user has permission to do something
	 * @param array $params
	 * - [permission] string - the permission to check
	 * - [assign] string  - if set then the result is set to this parameter.
	 *   if not set then it is set to the same name as the permission
	 * - additional params - any additional params will be passed through to the
	 *   permission test
	 * @param Smarty $smarty
	 */
	public function hasPermission($params, $smarty)
	{
		if (empty($params['permission']))
			trigger_error("permission: missing 'permission' parameter");
		$permission = $params['permission'];
		$assign = empty($params['assign']) ? $permission : $params['assign'];
		$canDo = neon()->user->can($permission, $params);
		$smarty->assign($assign, $canDo);
	}

	/**
	 * Protects any HtmlEntities that are interpreted into javascript by
	 * via HTML. E.g. &lt; will be conerted to < by the browser. So this further
	 * protects by converting it to &amp;lt; so it beomes &lt; in the browser
	 * @param array $params
	 * - [input] string - the input string to protect
	 * - [assign] string (optional) - if set, then assign it to this parameter. If
	 *   not set, then return the converted string
	 * @param Smarty $smarty
	 * @return string  if no assign parameter provided, return the result
	 */
	public function protectHtmlEntities($params, $smarty)
	{
		$input = $params['input'];
		$output = str_replace('&', '&amp;', $input);
		if (!empty($params['assign'])) {
			$smarty->assign($params['assign'], $output);
		} else {
			return $output;
		}
	}
}