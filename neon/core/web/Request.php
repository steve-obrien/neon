<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 27/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\web;

use neon\core\helpers\Arr;
use neon\core\helpers\Collection;
use neon\core\helpers\Str;
use neon\core\helpers\Url;
use SplFileInfo;
use \neon\core\web\UploadedFile;
use \neon\core\helpers\Html;
use yii\web\HttpException;

/**
 * Class Request
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\web
 */
class Request extends \yii\web\Request
{
	/**
	 * Retrieve a file from the request.
	 *
	 * @param  string  $name
	 * @return \neon\core\web\UploadedFile|null
	 */
	public function file($name)
	{
		return UploadedFile::getInstanceByName($name);
	}

	/**
	 * Get all files for a particular name - if a single field
	 * has multiple or nested files set up.
	 *
	 * @param $name
	 * @return \yii\web\UploadedFile[]
	 */
	public function files($name)
	{
		return UploadedFile::getInstancesByName($name);
	}

	/**
	 * Determine if the uploaded data contains a file.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function hasFile($name)
	{
		$file = UploadedFile::getInstanceByName($name);
		return ($file instanceof UploadedFile);
	}

	/**
	 * Get the host domain - typically this should match neon()->request->getHostInfo()
	 * used to compare if we are accessing a subdomain
	 * @return string
	 */
	public function getMasterDomain()
	{
		return neon()->masterDomain;
	}

	/**
	 * store the domain name for this request
	 * @var string
	 */
	private $_domain;

	/**
	 * Get the requested domain name
	 * @return string
	 */
	public function getDomain()
	{
		if ($this->_domain === null) {
			$this->_domain = str_replace(['http://', 'https://'], '', $this->getHostInfo());
		}
		return $this->_domain;
	}

	/**
	 * Return the http protocol string
	 * @return string
	 */
	public function getProtocol()
	{
		return neon()->request->getIsSecureConnection() ? 'https://' : 'http://';
	}

	/**
	 * Get the sub domain currently requested - the sub domain in the context of the hostDomain
	 * For e.g. if the hostDomain set is local.newicon.org and the current request is 'http://something.local.newicon.org'
	 * then this function will return "something"
	 *
	 * @return string
	 */
	public function getSubDomain()
	{
		$domain = $this->getDomain();
		// do we recognise the host? - look up the mapped domains here.
		$subDomain = trim($domain, '.');
		return $subDomain;
	}

	/**
	 * This function is similar to the parents class private function validateCsrfInternal
	 * We need to test custom tokens as Yii CSRF validation will always return true for GET requests
	 * When using the JWT web token we in fact want to validate CSRF on GET requests too as they should only
	 * ever originate from the current site.
	 *
	 * @param string $token - The token to check against
	 * @return bool
	 */
	public function isCsrfTokenValid($token)
	{
		$security = neon()->security;
		$requestCsrf = $this->getBodyParam($this->csrfParam, neon()->request->getCsrfTokenFromHeader());
		if ($requestCsrf !== null) {
			$requestCsrf = $security->unmaskToken($requestCsrf);
			// attempt to load from cookie if not specified in header
		} else if ($this->enableCsrfCookie) {
			$requestCsrf = $this->getCookies()->getValue($this->csrfParam);
		}
		return $security->unmaskToken($token) === $requestCsrf;
	}

	/**
	 * Checks if the request method is of specified type.
	 *
	 * @param string $method Uppercase request method (GET, POST etc)
	 * @return bool
	 */
	public function isMethod($method)
	{
		return $this->getMethod() === strtoupper($method);
	}

	/**
	 * Returns a sanitised form of the get parameter
	 * This will remove all tags and run through HTMLPurifier. If you need
	 * a more nuanced sanitisation then you can call getUnsafe, and run
	 * Html::sanitise with additional parameters
	 * @inheritdoc
	 */
	public function get($name = null, $defaultValue = null)
	{
		if ($this->_sanitisedQueryParams == null)
			$this->sanitiseBodyQueryParams();
		if ($name == null)
			return $this->_sanitisedQueryParams;
		return isset($this->_sanitisedQueryParams[$name]) ? $this->_sanitisedQueryParams[$name] : $defaultValue;
	}

	/**
	 * Returns the get parameter but throws an exception if it is not defined
	 *
	 * @param string $name - get param name
	 * @throws HttpException - 400 exceptionif the param is not specified or null
	 * @return mixed
	 */
	public function getRequired($name)
	{
		$value = $this->get($name);
		if ($value === null) {
			throw new HttpException(400, "'$name' request parameter is required");
		}
		return $value;
	}

	/**
	 * Returns the post parameter but throws an exception if it is not defined
	 *
	 * @param string $name - get param name
	 * @throws HttpException - 400 exceptionif the param is not specified or null
	 * @return mixed
	 */
	public function postRequired($name)
	{
		$value = $this->post($name);
		if ($value === null) {
			throw new HttpException(400, "'$name' post parameter is required");
		}
		return $value;
	}

	/**
	 * Validates a request based on provided rules
	 * Automatically throws http error for invalid rules.
	 *
	 * @see validateData() for detailed instructions
	 * @param array $data - key value pairs where the key is the string name of the request paramter and the value
	 *   is a default value to set.  If you wish to have required rule on a paramter then set its default value to null
	 * @param array $rules - a yii rules array
	 * @param int $errorCode - http error code to throw if validation fails
	 * @return \yii\base\DynamicModel
	 * @throws \yii\base\InvalidConfigException
	 * @throws HttpException - if validation fails
	 */
	public function validate($data, $rules, $errorCode=400)
	{
		$model = $this->validateData($data, $rules);
		if ($model->hasErrors()) {
			abort($errorCode, implode(' ', $model->getErrorSummary(false)));
		}
		return $model;
	}

	/**
	 * Validates a request based on provided rules
	 * Returns the validated model allowing `hasErrors` and `getErrors()` calls to be made as appropriate
	 *
	 * @param array $data - key value pairs where the key is the string name of the request paramter and the value
	 *   is a default value to set.  If you wish to have required rule on a paramter then set its default value to null
	 * @param array $rules - a yii rules array
	 * @return \yii\base\DynamicModel
	 * @throws \yii\base\InvalidConfigException
	 * @example
	 * ```php
	 *
	 * $req = request()->validate(['id' => null, 'pageLength' => 10], [
	 *     ['id', 'required'],
	 *     ['id', 'string', 'length' => 22],
	 *     ['pageLength', 'number', 'min' => 0],
	 * ]);
	 * // properties are available on the returned object as the keys defined in $data
	 * // $req->id;
	 * // $req->pageLength;
	 * // $req->hasErrors();
	 * ```
	 */
	public function validateData($data, $rules, $type="get")
	{
		$validate = [];
		foreach($data as $key => $default)
			$validate[$key] = $this->$type($key, $default);
		return \yii\base\DynamicModel::validateData($validate, $rules);
	}

	/**
	 * Returns a sanitised form of the post parameter
	 * This will remove all tags and run through HTMLPurifier. If you need
	 * a more nuanced sanitisation then you can call postUnsafe, and run
	 * Html::sanitise with additional parameters
	 * @inheritdoc
	 */
	public function post($name = null, $defaultValue = null)
	{
		if ($this->_sanitisedBodyParams == null)
			$this->sanitiseBodyQueryParams();
		if ($name == null)
			return $this->_sanitisedBodyParams;
		return isset($this->_sanitisedBodyParams[$name]) ? $this->_sanitisedBodyParams[$name] : $defaultValue;
	}

	/**
	 * Sanitise both the posted body and query parameters
	 */
	private function sanitiseBodyQueryParams()
	{
		$this->_sanitisedQueryParams = Html::sanitise($this->getQueryParams());
		$this->_sanitisedBodyParams = Html::sanitise($this->getBodyParams());
	}
	private $_sanitisedQueryParams = null;
	private $_sanitisedBodyParams = null;


	/**
	 * Returns an unsanitised form of the get parameter. Use with caution
	 * @inheritdoc
	 */
	public function getUnsafe($name = null, $defaultValue = null)
	{
		return parent::get($name, $defaultValue);
	}

	/**
	 * Returns a unsanitised form of the post parameter. Use with caution
	 * @inheritdoc
	 */
	public function postUnsafe($name = null, $defaultValue = null)
	{
		return parent::post($name, $defaultValue);
	}

	/**
	 * @inheritdoc
	 */
	public function resolve()
	{
		profile_begin('request::resolve', 'request');
		$return = parent::resolve();
		profile_end('request::resolve', 'request');
		return $return;
	}

	/**
	 * Get the full URL for the request with the added query string parameters.
	 *
	 * @param  array  $query
	 * @return string
	 */
	public function getFullUrlWithQuery(array $query)
	{
		$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

		return count($this->getQueryParams()) > 0
			? $this->getUrl().$question.Arr::query(array_merge($this->getQueryParams(), $query))
			: $this->getFullUrl().$question.Arr::query($query);
	}

	/**
	 * Get the full URL for the request.
	 *
	 * @return string
	 */
	public function getFullUrl()
	{
		$query = $this->getQueryString();

		$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

		return $query ? $this->url().$question.$query : $this->getUrl();
	}

	/**
	 * Get the current path info for the request.
	 *
	 * @return string
	 */
	public function path()
	{
		$pattern = trim($this->getPathInfo(), '/');

		return $pattern == '' ? '/' : $pattern;
	}

	/**
	 * Get the current decoded path info for the request.
	 *
	 * @return string
	 */
	public function decodedPath()
	{
		return rawurldecode($this->path());
	}

	/**
	 * Determine if the current request URI matches a pattern.
	 *
	 * @param  mixed  ...$patterns
	 * @return bool
	 */
	public function isUrl(...$patterns)
	{
		$path = $this->decodedPath();

		foreach ($patterns as $pattern) {
			if (Str::is(ltrim($pattern, '/'), $path)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the current request matches a route.
	 * Note: - unlike *isUrl* this function is safe against url rewrites.
	 * For example a route '/myApp/myController/action' could be rewritten to 'my-custom-url'.
	 * To test if the request is being serviced by myController we can use
	 * `neon()->request->isRoute('/myApp/myController/*')`
	 * A future improvement to this function would be to match on route paramters by passing in a $params array
	 * This could be
	 *
	 * @param string $pattern a route in string format for e.g. /module/controller/*  | /module/controller/action
	 * @params array $params - checks if key and value exists as a subset of the current GET paramerters - only
	 * used is the $pattern matches the current route
	 * @return bool
	 */
	public function isRoute($pattern, $params=[])
	{
		$route = neon()->controller->getRoute();
		$match = Str::is(ltrim($pattern, '/'), $route);
		// we can return imidiately if the route does not match the pattern
		if (!$match) return false;

		// if no param search specified then return the match
		if (empty($params)) return $match;

		// Finally check if $params exist in request params
		return Arr::isSubset($params, neon()->request->getQueryParams());
	}
}
