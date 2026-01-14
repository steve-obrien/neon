<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/04/2020
 * @package neon
 */

namespace neon\core\web;


use neon\core\helpers\Arr;
use yii\base\ActionFilter;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\CookieCollection;

class FilterGlobalPassword extends ActionFilter
{
	/**
	 * @var string name of the temporary global password cookie
	 */
	public $cookieName = 'neon_pass';

	/**
	 * @var string The name of the post variable containing the password
	 */
	public $passwordParam = 'neon_global_pass';

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		// If the global password option is not set then return - and continue as normal
		if (setting('core', 'global_password_enabled', false) === 0)
			return true;
		// If the global password is set - still allow the following app controller paths to work.
		if (neon()->request->isRoute('/admin*') || neon()->request->isRoute('/user/*') || neon()->request->isRoute('/settings*')) {
			return true;
		}
		if ($this->passwordIsValid())
			return true;
		if ($this->shouldDisplayHoldingPage())
			return false;
		$this->displayGlobalPasswordPage();
		return false;
	}

	/**
	 * Determines if a holding page has been set and renders it.
	 *
	 * @return bool - true if a holding page has been defined and rendered
	 * @throws \yii\web\HttpException
	 */
	private function shouldDisplayHoldingPage()
	{
		// Ability to use a custom the holding page
		// If a holding niceId page is defined then render that page instead of the password page
		// you will have to use get variable ?neon_global_pass=*** to access the site as normal
		if (setting('core', 'global_password_holding_page', '') !== '') {
			$niceId = setting('core', 'global_password_holding_page', '');
			$page = neon()->cms->getPage();
			// check the page with the nice_id exists
			if (! $page->setById($niceId)) throw new \yii\web\HttpException(404, 'No page found');
			neon()->response->content = $page->render();
			//return false;
			return true;
		}
		return false;
	}

	/**
	 * Validates the global password
	 * If valid stores a cookie to validate subsequent requests
	 *
	 * @return bool - true if the password is correct
	 */
	private function passwordIsValid()
	{
		// Validate the global password recievied is correct and create a cookie so the password is not required for
		// future requests
		$suppliedPass = $this->getSuppliedPassword();
		if ($suppliedPass === setting('core', 'global_password')) {
			// The password is correct so create the cookie and return - enabling normal execution
			neon()->response->cookies->add(new Cookie(['name'=>$this->cookieName, 'value'=>$suppliedPass]));
			return true;
		}
		return false;
	}

	/**
	 * Renders the standard global password page
	 *
	 * @return void
	 */
	private function displayGlobalPasswordPage()
	{
		// No holding page niceId has been given so display a standard password screen
		neon()->response->content = neon()->view->render('@neon/core/views/global-password.php', [
			'passwordParam' => $this->passwordParam,
			'incorrectPassword' => ($this->getSuppliedPassword()!==null)
		]);
	}

	/**
	 * Get password supplied or null
	 *
	 * @return string|null
	 */
	private function getSuppliedPassword()
	{
		$cookie = neon()->request->cookies->get($this->cookieName);
		return neon()->request->get($this->passwordParam, ($cookie ? $cookie->value : null));
	}
}
