<?php

namespace neon\user\services;

use neon\user\services\apiTokenManager\JwtTokenAuth;
use neon\user\interfaces\iUser;
use \neon\user\models\User as UserIdentity;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package neon\user\web
 * Implements a simple user based model API that is safe to use.
 * Note the identity class is actually responsible for all the information
 *
 * @property \neon\user\models\User $identity The identity object associated with the currently logged-in
 * user. `null` is returned if the user is not logged in (not authenticated).
 * @property integer $rememberMeDuration - number of seconds to remember the session @see static::getRememberMeDuration()
 */
class User extends \yii\web\User implements iUser
{
	/**
	 * Number of seconds the session will remain valid if the remember me option is set
	 * Note this setting can be overridden by application setting of
	 * setting('user', 'rememberMeDuration')
	 * @var int
	 */
	private $_defaultRememberMeDuration = 0;

	/**
	 * @param $value
	 */
	public function setRememberMeDuration($value)
	{
		$this->_defaultRememberMeDuration = $value;
	}

	/**
	 * @return integer - number of seconds to remember the session
	 */
	public function getRememberMeDuration()
	{
		return setting('user', 'rememberMeDuration', $this->_defaultRememberMeDuration);
	}

	/**
	 * The number of seconds that the password reset token should be valid for
	 * @var int
	 */
	public $passwordResetTokenExpire = 3600;

	/**
	 * How hard to make the blowfish password hashing algorithm do its job
	 *
	 * @var int
	 */
	public static $passwordHashCost = 4;

	/**
	 * Generates password hash from a password
	 *
	 * @param string $password  password to hash
	 * @throws \Exception on bad password parameter or cost parameter.
	 * @return string  the returned hash
	 */
	public function generatePasswordHash($password)
	{
		return neon()->security->generatePasswordHash($password, self::$passwordHashCost);
	}

	/**
	 * @inheritdoc
	 */
	public function logout($destroySession = true)
	{
		$this->removeApiJwtToken();
		return parent::logout($destroySession);
	}

	/**
	 * Logs in a user.
	 *
	 * After logging in a user:
	 * - the user's identity information is obtainable from the [[identity]] property
	 *
	 * If [[enableSession]] is `true`:
	 * - the identity information will be stored in session and be available in the next requests
	 * - in case of `$duration == 0`: as long as the session remains active or till the user closes the browser
	 * - in case of `$duration > 0`: as long as the session remains active or as long as the cookie
	 *   remains valid by it's `$duration` in seconds when [[enableAutoLogin]] is set `true`.
	 *
	 * If [[enableSession]] is `false`:
	 * - the `$duration` parameter will be ignored
	 *
	 * @param IdentityInterface $identity the user identity (which should already be authenticated)
	 * @param int $duration number of seconds that the user can remain in logged-in status, defaults to `0`
	 * @return bool whether the user is logged in
	 */
	public function login(IdentityInterface $identity, $duration = 0)
	{
		if ($this->beforeLogin($identity, false, $duration)) {
			$this->switchIdentity($identity, $duration);
			$id = $identity->getId();
			$ip = neon()->getRequest()->getUserIP();
			if ($this->enableSession) {
				$log = "User '$id' logged in from $ip with duration $duration.";
			} else {
				$log = "User '$id' logged in from $ip. Session not enabled.";
			}

			if (neon()->getRequest()->enableCsrfValidation)
				$this->regenerateCsrfToken();

			\Yii::info($log, __METHOD__);
			$this->afterLogin($identity, false, $duration);
		}

		return !$this->getIsGuest();
	}

	/**
	 * The number of unsuccessful login attempts allowed before locking the account
	 * @return int
	 */
	public function getFailedLoginAttempts()
	{
		return setting('user', 'failedLoginAttempts', 20);
	}

	/**
	 * @inheritdoc
	 */
	public function getUuid()
	{
		return $this->noIdentity() ? null : $this->identity->uuid;
	}

	/**
	 * @return int|null|string
	 */
	public function getId()
	{
		return $this->noIdentity() ? null : $this->identity->id;
	}

	/**
	 * A simple way of always displaying a very basic information for the current user identity.
	 * For more complex and user friendly display methods use the User helper functions.
	 * This function should not be expanded as this is mainly responsible for authenticating and
	 * creating the identity class.
	 * @return string
	 */
	public function getName()
	{
		if ($this->getIsGuest() || $this->noIdentity()) {
			return 'Guest';
		}
		return $this->identity->getUsername();
	}

	/**
	 * @inheritdoc
	 */
	public function isSuper()
	{
		return $this->noIdentity() ? false : $this->identity->isSuper();
	}

	/**
	 * Whether we have an identity object
	 *
	 * @return bool
	 */
	public function hasIdentity()
	{
		return ($this->getIdentity() !== null);
	}

	/**
	 * Whether the user is logged in
	 * Alias of $this->hasIdentity()
	 *
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return $this->hasIdentity();
	}

	/**
	 * Returns true if no identity exists
	 *
	 * @return bool
	 */
	public function noIdentity()
	{
		return !$this->hasIdentity();
	}

	/**
	 * @inheritdoc
	 */
	public function getEmail()
	{
		return $this->noIdentity() ? false : $this->identity->getEmail();
	}

	/**
	 * @inheritdoc
	 */
	public function getUsername()
	{
		return $this->noIdentity() ? false : $this->identity->getUsername();
	}

	/**
	 * @inheritdoc
	 */
	public function getPasswordHash()
	{
		return $this->noIdentity() ? false : $this->identity->getPasswordHash();
	}

	/**
	 * Get the default url to load for a user without an image
	 *
	 * @param int $size
	 * @return string
	 */
	public function getGuestUserImageUrl($size=40)
	{
		return "//www.gravatar.com/avatar?d=mm&s=$size";
	}

	/**
	 * @inheritdoc
	 */
	public function getImageUrl($size=40)
	{
		return $this->noIdentity() ? $this->getGuestUserImageUrl($size) : $this->identity->getImageUrl($size);
	}

	/**
	 * @inheritdoc
	 */
	public function getRoles()
	{
		return $this->noIdentity() ? [] : $this->identity->getRoles();
	}

	/**
	 * @inheritdoc
	 */
	public function hasRole($role)
	{
		return $this->noIdentity() ? false : $this->identity->hasRole($role);
	}

	/**
	 * @alias hasRole
	 * @param $role
	 * @return bool
	 */
	public function is($role)
	{
		return $this->hasRole($role);
	}

	/**
	 * @inheritdoc
	 */
	public function hasRoles(array $roles)
	{
		return $this->noIdentity() ? false : $this->identity->hasRoles($roles);
	}

	/**
	 * @inheritdoc
	 */
	public function getHomeUrl($role=null)
	{
		return $this->noIdentity() ? null : $this->identity->getHomeUrl($role);
	}

	/**
	 * @inheritdoc
	 */
	public function getReturnUrl($defaultUrl = null)
	{
		// The return Url is the one used if the person wasn't already going to a particular
		// Url which required login. Here we check what roles the person is entitled to access
		// and send them to the first one of those, or if not found, then to the provided url
		// or the application configured one
		$appRoles = neon('user')->getRoles(true);
		$userRoles = $this->identity->getRoles();
		$siteHomeUrl = \Yii::$app->getUrlManager()->createAbsoluteUrl(\Yii::$app->getHomeUrl());

		foreach ($userRoles as $ur) {
			if (array_key_exists('homeUrl', $appRoles[$ur])) {
				$url = $appRoles[$ur]['homeUrl'];
				$returnUrl = parent::getReturnUrl(is_array($url) ? $url : [ $url ]);
				if ($returnUrl == $siteHomeUrl) {
					// if the session only has the home URL, use the user-specific home URL instead
					$returnUrl = \Yii::$app->getUrlManager()->createUrl($url);
				}
				return $returnUrl;
			}
		}

		// use the default or application set one
		if ($defaultUrl)
			return parent::getReturnUrl( is_array($defaultUrl) ? $defaultUrl : [ $defaultUrl ] );

		return parent::getReturnUrl();
	}

	/**
	 * Removes the jwt identity cookie.
	 */
	protected function removeApiJwtToken()
	{
		neon()->getResponse()->getCookies()->remove(JwtTokenAuth::$cookieName);
	}

	/**
	 * @inheritDoc
	 */
	protected function beforeLogin($identity, $cookieBased, $duration)
	{
		if ($identity->status !== UserIdentity::STATUS_ACTIVE) {
			return false;
		}
		return parent::beforeLogin($identity, $cookieBased, $duration);
	}

	/**
	 * @inheritDoc
	 */
	protected function afterLogin($identity, $cookieBased, $duration)
	{
		$user = $this->identity;
		if ($user) {
			$user->logins = $user->logins + 1;
			$user->last_login = date('Y-m-d H:i:s');
			$user->save();
		}
		// Log the users IP here too $ip = Yii::$app->getRequest()->getUserIP();
		// TODO - auth table note the log in - note the time - note the browser and location.
		// can request details from https://ipinfo.io/

		// $ip = neon()->request->getUserIP();
		// Send an asynchronous request.
		// $client = new \GuzzleHttp\Client(['timeout'  => 3.0]);
		// $res = $client->request('GET', "https://ipinfo.io/$ip/json");

		parent::afterLogin($identity, $cookieBased, $duration);
	}

	/**
	 * returns the id of the user being impersonated or false if there isn't one
	 * @return string | false - the id string or false
	 */
	public function isImpersonating()
	{
		if (isset(neon()->session['impersonate_restore_id']) && neon()->session['impersonate_restore_id']) {
			return decrypt(neon()->session['impersonate_restore_id']);
		}
		return false;
	}

	/**
	 * Get the user active record of the super user to restore to
	 * @return UserIdentity | null
	 */
	public function getImpersonatingRestoreUser()
	{
		// theoretically we could call the UserIdentity::findIndentity method - which is the one responsible for
		// finding the correct user identity to log people in.
		// However - this method will ONLY return users with an active status - therefore here we just grab the user by id
		// this means you can impersonate users that are banned or suspended.
		// banned or suspended users will NOT be able to log in.
		// But it may be useful to impersonate them to debug their account or to set things up before unbanning - or un-suspending
		return UserIdentity::findOne(['uuid' => self::isImpersonating()]);
	}

	/**
	 * Impersonate a user given a user uuid
	 * @param string $userUuid
	 */
	public function impersonate($userUuid)
	{
		$userImpersonate = UserIdentity::findOne(['uuid' => (string) $userUuid]);
		if ($userImpersonate && neon()->user->isSuper()) {
			neon()->session['impersonate_restore_id'] = encrypt($this->getUuid());
			neon()->session['impersonate_restore_validation'] = $this->identity->getAuthKey(); // encryption handled for us
			neon()->user->login($userImpersonate);
		}
	}

	/**
	 * Restore to the original user and stop impersonating
	 * @return boolean whether the user was logged in successfully
	 */
	public function impersonateRestore()
	{
		$user = neon()->user->getImpersonatingRestoreUser();
		if ($user) {
			// check the validation session to ensure the validation key matches this user.
			// Protects against cases where the session could be hacked
			// You must know the users details (password and the id) and they must match to restore the user.
			if ($user->validateAuthKey(neon()->session['impersonate_restore_validation'])) {
				// remove restore session
				unset(neon()->session['impersonate_restore_id']);
				unset(neon()->session['impersonate_restore_validation']);
				return neon()->user->login($user);
			}
		}
		return false;
	}

}
