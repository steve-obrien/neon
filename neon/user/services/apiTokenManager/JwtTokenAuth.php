<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/05/2017
 * @package neon
 */

namespace neon\user\services\apiTokenManager;

use \yii\filters\auth\AuthMethod;
use neon\core\web\Response;
use \neon\core\web\Request;
use neon\core\Env;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \yii\web\Cookie;
use \Exception;
use yii\web\IdentityInterface;
use yii\web\UnauthorizedHttpException;


/**
 * Validates the user based on a generated jwt cookie - this is generated from a logged in user
 * It enables us to authenticate if we have logged in - but have not expressed an api token
 * - this is intended for use with rest style stateless API's so that logged in users can interact with the same
 * API as might be exposed / access via an api_token
 * Class TokenAuth
 * @package neon\user\auth
 */
class JwtTokenAuth extends AuthMethod
{

	public static $cookieName = 'neon_api_token';

	/**
	 * Get the secret key to encrypt the JWT cookie with
	 * @return string
	 */
	public static function secret()
	{
		return Env::getNeonSecret();
	}

	/**
	 * Find the user identity - indicates successful authentication
	 *
	 * @param \yii\web\User $user
	 * @param Request $request
	 * @param \yii\web\Response $response
	 * @throws UnauthorizedHttpException
	 * @return null|IdentityInterface
	 */
	public function authenticate($user, $request, $response)
	{
		// As there is no cookie or cookie string we return null allowing other authentication methods
		// to attempt authentication
		$tokenString = $this->getJwtStringFromCookie($request);
		if ($tokenString === null)
			return null;
		// decode the cookie and perform csrf validation
		// this only checks that the cookie data is good
		$jwt = $this->decodeAndValidateJwtToken($tokenString, $request);
		// find the user identity and validate the user based on auth key
		// this is similar to the session 'remember me' cookie
		// note: we need to call the loginByAccessToken token of the base \yii\web\User class
		// this will do some essential set up but also call the findAndValidateUserIdentity
		// of the identity interface - this will come back to findAndValidateUserIdentity of this class
		// @see self::findAndValidateUserIdentity()
		// this is similar to the stock yii classes yii\filters\auth\HttpBearerAuth and yii\filters\auth\QueryParamAuth;
		$identity = $user->loginByAccessToken($jwt, get_class($this));
		if ($identity === null)
			$this->handleFailure($response);

		return $identity;
	}

	/**
	 * Finds a user identity and validates the auth key
	 * This leverages the identity interface and the authentication methods baked in to it
	 * @see IdentityInterface
	 *
	 * @param array $jwtToken
	 * @return null|IdentityInterface
	 */
	public static function findAndValidateUserIdentity(array $jwtToken)
	{
		// Check the user identity is valid
		/* @var $class IdentityInterface */
		$class = neon()->user->identityClass;
		// $jwtToken['token']
		// load userId from cache
		// $userId = neon()->cacheToken->get($jwtToken['token'], null);
		$identity = $class::findIdentity($jwtToken['user']);
		if ($identity && $identity->validateAuthKey($jwtToken['auth'])) {
			return $identity;
		}
		return null;
	}

	/**
	 * Decode the jwt string and perform validation checks.
	 * Return the validated token array data if successful or false
	 * This decodes and decrypts the cookie data - it also performs csrf validation
	 *
	 * @param string $cookieString
	 * @param Request $request
	 * @return array|false false if the token is not valid and if valid, an array of token valid values
	 * @throws UnauthorizedHttpException - if csrf validation fails or JWT token could not decode
	 */
	public function decodeAndValidateJwtToken($cookieString, Request $request)
	{
		try {
			$jwt = (array) JWT::decode($cookieString, new Key(static::secret(), 'HS256'));
		} catch (Exception $e) {
			throw new UnauthorizedHttpException('Your request was made with invalid credentials.' . neon()->debug ? ' Can not decode JWT token' : '');
		}
		// We will compare the CSRF token in the decoded API token against the CSRF header
		// sent with the request. If the two don't match then this request is sent from
		// an invalid source and we won't authenticate the request for further handling.
		if (!$this->validCsrf($jwt, $request)) {
			throw new UnauthorizedHttpException('Your request was made with invalid credentials.' . neon()->debug ? ' CSRF validation failed' : '');
		}

		return $jwt;
	}
	/**
	 * Get the jwt (Json Web Token) string from the cookie
	 *
	 * @param Request $request
	 * @return string - the jwt raw cookie string
	 */
	public function getJwtStringFromCookie(Request $request)
	{
		if (!$request->cookies->has(static::$cookieName))
			return null;
		$cookie = $request->cookies->get(static::$cookieName)->value;
		return $cookie;
	}

	/**
	 * Validate the jwt cookies csrf property
	 *
	 * @param array $jwtToken
	 * @param Request $request
	 * @return bool
	 */
	public function validCsrf($jwtToken, Request $request)
	{
		return isset($jwtToken['csrf']) && $request->isCsrfTokenValid($jwtToken['csrf']);
	}

/**
 * Functions involving the creation of this authentication cookie
 * --------------------------------------------------------------
 */

	/**
	 * Generate a jwt token string
	 *
	 * @return string
	 */
	public static function generateJwtToken()
	{
		$token = [
			'user' => neon()->user->getUuid(),
			'auth' => neon()->user->getIdentity()->getAuthKey(),
			'csrf' => neon()->request->getCsrfToken(),
		];
		$jwt = JWT::encode($token, static::secret(), 'HS256');
		return $jwt;
	}

	/**
	 * Create a jwt token cookie
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public static function createCookie(Request $request, Response $response)
	{
		// does it exist?
		// if we are a get request and we have a logged in user and we are not ajax then...
		if (static::shouldReceiveFreshToken($request, $response)) {
			// create our jwt token
			\Neon::beginProfile('JWT: generateJwtToken');
			$token = static::generateJwtToken();
			\Neon::endProfile('JWT: generateJwtToken');
			$response->cookies->add(new Cookie([
				'name' => static::$cookieName,
				'value' => $token,
				'httpOnly' => true,
			]));
		}
	}

	/**
	 * Test if the request should send the user a fresh token cookie
	 *
	 * @param Request $request
	 * @param Response $response
	 * @return boolean
	 */
	public static function shouldReceiveFreshToken(Request $request, Response $response)
	{
		return static::requestShouldReceiveFreshToken($request)
			&& static::responseShouldReceiveFreshToken($response);
	}

	/**
	 * Determine if the request should receive a fresh token.
	 *
	 * @param  Request  $request
	 * @return bool
	 */
	protected static function requestShouldReceiveFreshToken(Request $request)
	{
		return $request->isMethod('GET') && neon()->user->isLoggedIn() && ! $request->getIsAjax();
	}

	/**
	 * Determine if the response should receive a fresh token.
	 *
	 * @param Response $response
	 * @return bool
	 */
	protected static function responseShouldReceiveFreshToken(Response $response)
	{
		// if the cookie already exists do not create another one
		return ! isset($response->cookies[static::$cookieName]);
	}
}