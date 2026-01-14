<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\helpers;

use neon\core\widgets\Js;
use neon\core\web\View;
use neon\core\helpers\Url;

/**
 * Class Page - a helper for use within views - generally helping with web page things - Js and Css etc
 * A simple wrapper preventing too many use statements in views - The goal is to simplify the number of objects you need
 * to remember to interact with the page rendering
 * The other functions have been consumed now by the View class and SmartyShared View classes making this a redundant class
 * The View class neon()->view for global web page helpers and smarty / template plugin functions / widgets are more
 * appropriate for html functions
 * @author Steve O'Brien - Newicon Ltd This whole class must die and just use neon()->view - which is essentially the same object
 * @package neon\core\helpers
 */
class Page
{
	/**
	 * Use this in templates to render javascript code snippets in other sections of the page - typically JQuery pos ready
	 * usage:
	 *
	 * ```
	 * <?php Page::jsBegin(); ?>
	 * <script> // it is safe to use script tags - but they will be stripped out by the function
	 *     alert('javascript snippet here')
	 * </script>
	 * <?php Page::jsEnd(); ?>
	 * ```
	 *
	 * @param string $position with values:
	 *   The position param specified where the javascript block will be rendered on the page it can have
	 *   the following values:
	 *
	 *   - "head"  : This means the javascript is rendered in the head section.
	 *   - "begin" : This means the javascript is rendered at the beginning of the body section.
	 *   - "end"   : This means the javascript is rendered at the end of the body section.
	 *   - "ready" : This means the JavaScript code block will be enclosed within `jQuery(document).ready()`.
	 *   - "load"  : This means the JavaScript code block will be enclosed within `jQuery(window).load()`.
	 *
	 * @throws \Exception if an invalid position is given
	 *
	 * @return void
	 */
	public static function jsBegin($position = 'ready')
	{
		$positions = [
			'head'  => View::POS_HEAD,
			'begin' => View::POS_BEGIN,
			'end'   => View::POS_END,
			'ready' => View::POS_READY,
			'load'  => View::POS_LOAD,
		];
		if (!array_key_exists($position, $positions)) {
			throw new \Exception('The javascript position specified must be one of ' . print_r(array_keys($positions)) . ', you specified "'.$position.'"');
		}
		Js::begin(['position' => $positions[$position]]);
	}

	/**
	 * End the script block
	 */
	public static function jsEnd()
	{
		Js::end();
	}

	/**
	 * @param $src
	 * @param $attributes
	 */
	public static function jsFile($src, $attributes)
	{
		neon()->view->registerJsFile($src, $attributes);
	}

	/**
	 * Alias of \neon\core\helpers\Url::to
	 * @see \neon\core\helpers\Url::to()
	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @param boolean|string $scheme the URI scheme to use in the generated URL:
	 *
	 * - `false` (default): generating a relative URL.
	 * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
	 * - string: generating an absolute URL with the specified scheme (either `http` or `https`).
	 *
	 * @return string the generated URL
	 * @throws InvalidParamException a relative route is given while there is no active controller
	 * @deprecated use `url()` instead
	 */
	public static function url($url = '', $scheme = false)
	{
		$url = is_string($url) ? "/$url" : $url;
		return Url::toRoute($url, $scheme);
	}

	/**
	 * @return \neon\admin\services\menu\Manager
	 */
	public static function getMenuManager()
	{
		return neon('admin')->menuManager;
	}

	/**
	 * Draw a user menu card
	 * @param string $name
	 * @param string $email
	 * @param string $image - url
	 * @return string
	 */
	public static function userMenuCard($name=null, $email=null, $image=null)
	{
		if ($name == null && $email == null && $image == null) {
			$u = neon()->user;
			list($name, $email, $image) = [$u->getName(), $u->getEmail(), $u->getImageUrl()];
		}
		return sprintf('
			<div class="media">
				<div class="media-left"><img src="%s" class="avatar media-object"></div>
	            <div class="user-info media-body"><h4 class="man">%s</h4> %s </div>
			</div>
		', $image, $name, $email);
	}

	/**
	 * Show the main admin menu
	 * @return string html for the admin menu
	 */
	public static function menuAdmin()
	{
		$userCard = Page::userMenuCard();
		// If a user is logged in then set up their log in user menu
		static::getMenuManager()->add('user', [
			'template' => '<a href="{url}" class="dropdown-toggle navbarUserDropdown !flex items-center" data-toggle="dropdown">{label}</a>',
			'options' => ['class'=>'dropdown navbarUserDropdown'],
			'label' => neon()->user->isGuest ? 'Guest' : '<img class="nav-avatar avatar" src="' . neon()->user->getImageUrl(30) . '"/> <span>' . neon()->user->getName() . '</span>',
			'url' => ['/user/account/index'],
			'linkOptions' => ['class' => 'navbarUserDropdown'],
			'encode' => false,
			'submenuTemplate' => "\n<ul class=\"dropdown-menu\">\n{items}\n</ul>\n",
			'items' => [
				//['label' => $userCard, 'url' => ['/user/account/index'], 'encode'=>false ],
				['label' => "<a>$userCard</a>", 'encode' => false], //
				['label' => '<li class="divider"></li>', 'encode' => false],

				//['label' => '<i class="fa fa-cog" aria-hidden="true"></i> My Account', 'url' => ['/user/account/index'], 'encode'=>false ],
				//'<li class="divider"></li>',
				['label' => '<i class="fa fa-sign-out" aria-hidden="true"></i> Log Out', 'url' => ['/user/account/logout'], 'encode'=>false ]
			]
		]);
		$adminItems = static::getMenuManager()->getMainAdminMenu();
		foreach($adminItems as $key => $items) {
			foreach($items as $key2 => $item) {
				if (isset($item['visible'])) {
					$adminItems[$key][$key2]['visible'] = value($item['visible']);
				}
			}
		}
		$userItems = static::getMenuManager()->get('user');
		$out = '<nav class="navbar navbar-inverse navbar-main container-fluid">';
		if ($adminItems) {
			$out .= \yii\widgets\Menu::widget([
				// this is the old code to use the yii/bootstrap extension
				// it becomes very hard to remove bootstrap if we use these.
				'options' => ['class' => 'navbar-nav navbar-left nav'],
				'items' => $adminItems['items']
			]);
		}
 		if ($userItems) {
		    $out .= \yii\widgets\Menu::widget([
			    // this is the old code to use the yii/bootstrap extension
			    // it becomes very hard to remove bootstrap if we use these.
				'options'=> ['class' => 'navbar-nav navbar-right nav'],
				'items'=> $userItems['items']
			]);
 		}
 		$out .= '</nav>';
		return $out;
	}
}
