<?php

namespace neon\core\helpers;

use neon\core\helpers\Arr;
use yii\base\InvalidConfigException;

class Url extends \yii\helpers\Url
{
	/**
	 * This returns the previous page referrer to go back.
	 * If there is not a referring page, then it will default to return the url specified
	 *
	 * @param mixed $url
	 * @see self::to()
	 * @return string url
	 */
	public static function goBack($url = '')
	{
		$back = neon()->request->referrer;
		if ($back) {
			return $back;
		}
		return self::to($url);
	}

	/**
	 * Apply the base url to the passed in $url
	 *
	 * @param string $url
	 * @param bool $scheme - whether to add the http / https scheme
	 * @return string
	 */
	public static function baseUrl($url, $scheme=false)
	{
		return self::base($scheme) . '/' . ltrim($url, '/');
	}

	/**
	 * @deprecated!  THIS IS CMS SPECIFIC! OOH ERR
	 * @param type $pageId
	 * @param type $scheme
	 * @return type
	 */
	public static function getPageUrl($pageId, $scheme=false)
	{
		return self::base($scheme) . neon('cms')->page->getUrlOfPage($pageId);
	}

	/**
	 * Matches a route to see if it is the current route.
	 * the specificity of the match is based on the passed in route for example:
	 * - if $route is: 'user' then this function will return true for all controllers and actions within the user module.
	 * - if $route is: 'user/admin' this will return true for all actions under the user module's admin controller.
	 * - if $route is: 'user/admin/account' this will only return true for the account action inside the admin controller of the user modules.
	 *
	 * @param string $route ModuleId / ControllerId / ActionId
	 * @return boolean
	 * @deprected use neon()->request->isRoute(); which copes with unlimited nesting and wilcard patterns as well as param matching
	 */
	public static function isRoute($route, $currentUrl=false)
	{
		// deprecated('\neon\core\helpers\Url::isRoute', 'Please use neon()->request->isRoute() which copes with unlimited nesting and wilcard patterns as well as param matching');
		$r = explode('/', trim($route, '/'));
		$currentUrl ? $currentUrl : \Neon::$app->controller->getRoute();
		$currentRoute = explode('/', trim($currentUrl,'/'));

		$m = Arr::getValue($currentRoute, 0); // module
		$c = Arr::getValue($currentRoute, 1); // controller
		$a = Arr::getValue($currentRoute, 2); // action

		switch(count($r)){
			case 1:
				// match just module
				return $r[0] == $m;
			case 2:
				// match the module and the controller
				return ($r[0] == $m && $r[1] == $c);
			case 3:
				// match the module / controller /action
				return ($r[0] == $m && $r[1] == $c && $r[2] == $a) ;
		}
		return false;
	}

	/**
	 * Matches the url path (not including the web base url (the result of Url::base() ))
	 * must have an exact match to return true
	 * ***NOTE*** This does not account for url rewrites! It looks at the current url only and matches against the string
	 * For partial matches use neon()->request->is('my/path*');
	 * For example a web url of ```http://newicon.net/thing/neon/code``` with a Url::base of http://newicon.net/thing
	 * will match exactly if a $url parameter of ```/neon/code``` is passed in.
	 *
	 * Note: can accept multiple urls - will return true if any one urls matches the current url
	 * @example
	 * ```php
	 * // current url === /two
	 * isUrl('/one', '/two');
	 * // => true
	 * ```
	 *
	 * @param string|array $urls,... accepts multiple parameters representing urls to check - will return true if any match
	 * @throws InvalidConfigException if the path info cannot be determined due to unexpected server configuration
	 * @return boolean
	 */
	public static function isUrl(...$urls)
	{
		// if an array is passed in it will get nested one level because of the spread operator
		if (is_array($urls[0])) $urls = $urls[0];
		$currentUrl = neon()->request->getPathInfo();
		foreach($urls as $url) {
			$url = str_replace(Url::base(), '', $url);
			if ($currentUrl !== '' && ltrim($url, '/') === $currentUrl) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a url is absolute starts with // or :// or starts with 'data:'
	 *
	 * @param $url
	 * @return bool
	 */
	public static function isAbsolute($url)
	{
		$absolute = !static::isRelative($url);
		$isDataUrl = (substr($url, 0, 5) === 'data:');
		return $isDataUrl || $absolute;
	}

	/**
	 * Returns the canonical URL of the currently requested page.
	 *
	 * The canonical URL is constructed using the current controller's [[\yii\web\Controller::route]] and
	 * [[\yii\web\Controller::actionParams]]. You may use the following code in the layout view to add a link tag
	 * about canonical URL:
	 *
	 * ```php
	 * $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::canonical()]);
	 * ```
	 *
	 * @return string the canonical URL of the currently requested page
	 */
	public static function canonical()
	{
		return neon()->urlManager->getCanonical();
	}
}