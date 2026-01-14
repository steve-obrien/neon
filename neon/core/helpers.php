<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 13/11/2016 17:38
 * @package neon
 */

/**
 * This file is autoloaded via the composer autoload_file.php
 */
use \neon\core\helpers\Str;
use \neon\core\helpers\Collection;
use \neon\core\helpers\Arr;
use \neon\core\Env;
use \Carbon\Carbon;
use neon\core\helpers\Url;

if (! function_exists('dp')) {
	/**
	 * Debug function. Prints all passed in arguments to the screen.
	 * arguments containing objects or arrays are output using print_r
	 * arguments containing simple types are output using var_export.
	 * **Note:** Strings will be shown in single quotes.
	 * **Note:** boolean's will be output as string true | false
	 * **Note:** if not in debug mode, will print to the error log
	 * @param mixed
	 * @return void
	 */
	function dp(...$dump)
	{
		echo dp_string(...$dump);
		$backtrace = backtrace_line_by_function('dp');
		if (neon()->debug)
			echo $backtrace;
		else
			\Neon::error($backtrace);
	}
}

if (! function_exists('backtrace_line_by_function')) {
	/**
	 * Tries to get the line and file that called the specified functions
	 * - will return the earliest match in execution order that it found.
	 * @see dp()
	 * @param string $functionToShow function name to find
	 * @return string
	 */
	function backtrace_line_by_function($functionToShow = 'dp')
	{
		$bt = debug_backtrace();
		$lineInfo = '';
		foreach($bt as $trace) {
			if (isset($trace['function']) && $trace['function'] === $functionToShow) {
				$lineInfo = '==> [' . (isset($trace['file']) ? $trace['file'] : ' unknown file '). ':' . (isset($trace['line']) ? $trace['line'] : ' unknown line ' ) . ']';
				break;
			}
		}
		return $lineInfo;
	}
}

if (! function_exists('dp_string')) {
	/**
	 * This generates a nice debug string and returns it. It will only
	 * print to the screen if you are in debug mode. Otherwise it prints
	 * to Neon::error log.
	 * @see dp()
	 * @param mixed $dump
	 * @return string
	 */
	function dp_string(...$dump)
	{
		if (php_sapi_name() == 'cli') {
			// use symfony dump function
			dump(...$dump);
			return;
		}
		// only allow printing of debug data to the screen if in debug mode
		$arguments = func_get_args();
		$str = '';
		foreach ($arguments as $arg) {
			$debug = (is_object($arg) || is_array($arg))
				? print_r($arg, true) : var_export($arg, true);
			$str .= "<pre>$debug</pre>";
		}

		if (neon()->debug)
			return $str;
		\Neon::error($str);
		return '';
	}
}

if (! function_exists('dd')) {
	/**
	 * This is the same as the dp (debug print) function but exits after
	 * **Note:** If not in debug will print to log error and not exit
	 * @see dp()
	 * @param mixed $dump
	 * @return void
	 */
	function dd(...$dump)
	{
		echo dp_string(...$dump);
		$backtrace = backtrace_line_by_function('dd');
		if (neon()->debug) {
			echo $backtrace;
			exit;
		}
		\Neon::error($backtrace);
	}
}

if (! function_exists('debug_message')) {
	/**
	 * This is the same as the dp_string (debug print) function however it returns the string
	 * The main difference is that it will only output a message if neon is in debug mode
	 * Therefore it is safe to leave in the source code on error pages
	 * @see dp()
	 * @param mixed $dump
	 * @return string
	 */
	function debug_message(...$dump)
	{
		// security feature to ensure debug is not displayed unless in debug mode
		if (!neon()->debug)
			return '';
		return dp_string(...$dump);
	}
}


if (! function_exists('neon')) {
	/**
	 * Shortcut method to ```\Neon::$app```
	 * @param string $name
	 * @return \neon\core\ApplicationWeb
	 */
	function neon($name = null)
	{
		return \Neon::app($name);
	}
}

if (! function_exists('setting')) {
	/**
	 * Get a setting value
	 *
	 * @param string $app the app responsible for this setting
	 * @param string $name the setting name
	 * @param mixed $default the default property to return if the setting does not exist
	 * @param boolean $refresh to force a refresh - will trigger a reload of the settings - this is only necessary if
	 * the setting has been set in the current session
	 * @return string
	 */
	function setting($app, $name, $default=null, $refresh=false)
	{
		return neon()->getSettingsManager()->get($app, $name, $default, $refresh);
	}
}

if (! function_exists('set_setting')) {
	/**
	 * @param string $app the app responsible for this setting
	 * @param string $name the setting name
	 * @param mixed $value the setting value
	 * @return boolean whether successfully saved
	 */
	function set_setting($app, $name, $value)
	{
		return neon()->getSettingsManager()->set($app, $name, $value);
	}
}

if (! function_exists('url')) {
	/**
	 * Alias of \neon\core\helpers\Url::to
	 * @see \neon\core\helpers\Url::to()
	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @param boolean|string $absolute the URI scheme to use in the generated URL:
	 *
	 * - `false` (default): generating a relative URL.
	 * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
	 *
	 * @return string the generated URL
	 * @throws InvalidParamException a relative route is given while there is no active controller
	 */
	function url($url = '', $absolute = false)
	{
		if ($url === null)
			return '';
		$url = is_string($url) ? "/$url" : $url;
		return Url::toRoute($url, $absolute);
	}
}

if (! function_exists('url_base')) {
	/**
	 * Returns the base URL of the current request.
	 * @param boolean|string $scheme the URI scheme to use in the returned base URL:
	 *
	 * - `false`: returning the base URL without host info.
	 * - `true` (default): returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
	 * - string: returning an absolute base URL with the specified scheme (either `http` or `https`).
	 * @return string
	 */
	function url_base($scheme = true)
	{
		return \neon\core\helpers\Url::base($scheme);
	}
}

if (! function_exists('to_bytes')) {
	/**
	 * Returns the number of bytes as integer from a strings like `128K` | `128M` | `2G` | `1T` | `1P`
	 *
	 * @param  string  $string  A bytes string like 128M
	 * @return int
	 */
	function to_bytes($string) {
		return \neon\core\helpers\Str::toBytes($string);
	}
}

if (! function_exists('format_bytes')) {
	/**
	 * Output a formatted size like '128M' from bytes.
	 *
	 * @param  int  $size
	 * @param  int  $precision
	 * @return string
	 */
	function format_bytes($size, $precision = 2) {
		return \neon\core\helpers\Str::formatBytes($size, $precision);
	}
}

if (! function_exists('truthy')) {
	/**
	 * Php if statement handles truthy and falsey string conversions by default e.g. evaluating a '0' string to false
	 * however a string of 'false' is evaluated as true. This function resolves that difference. This should be used
	 * in caution in places where you are expecting a boolean.
	 * @param string $value - the value to test
	 * @return boolean
	 */
	function truthy($value)
	{
		if ($value && strtolower($value) !== "false")
			return true;
		return false;
	}
}

if (! function_exists('env')) {
	/**
	 * Gets the value of an environment variable.
	 * Interprets 'true', 'false', 'empty' and 'null' strings appropriately
	 *
	 * @param  string  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function env($key, $default = null)
	{
		$value = getenv($key);

		if ($value === false) {
			return $default;
		}
		switch (strtolower($value)) {
			case 'true': case 'on': case 'yes':
				return true;
			case 'false': case 'off': case 'no': case 'none':
				return false;
			case 'empty':
				return '';
			case 'null':
				return;
		}

		if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
			return substr($value, 1, -1);
		}

		return $value;
	}
}

if (! function_exists('neon_secret')) {
	/**
	 * Gets the value of NEON_SECRET creating it if missing
	 * @return string
	 */
	function neon_secret() {
		return Env::getNeonSecret();
	}
}

if (! function_exists('neon_encryption_key')) {
	/**
	 * Gets the value of NEON_SECRET creating it if missing
	 * @return string
	 */
	function neon_encryption_key() {
		return Env::getNeonEncryptionKey();
	}
}

if (! function_exists('value')) {
	/**
	 * Return the value of the given value.
	 * If the $value is a Closure, it will first be executed then its result returned
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (! function_exists('collect')) {
	/**
	 * Create a collection from the given value.
	 *
	 * @param  mixed  $value
	 * @return \neon\core\helpers\Collection
	 */
	function collect($value = null)
	{
		return new Collection($value);
	}
}

if (! function_exists('now')) {
	/**
	 * Create a new Carbon instance for the current time.
	 *
	 * @param  \DateTimeZone|string|null $tz
	 * @return \Carbon\Carbon
	 */
	function now($tz = null)
	{
		return Carbon::now($tz);
	}
}

if (! function_exists('profile_begin')) {
	/**
	 * @param string $name
	 */
	function profile_begin($name, $category='neon')
	{
		\Neon::beginProfile($name, $category);
	}
}

if (! function_exists('profile_end')) {
	/**
	 * @param string $name
	 */
	function profile_end($name=null, $category='neon')
	{
		\Neon::endProfile($name, $category);
	}
}

if (! function_exists('encrypt')) {
	/**
	 * @param string $data
	 * @return string - encrypted data
	 */
	function encrypt($data)
	{
		return Str::base64UrlEncode(neon()->security->encryptByKey($data, Env::getNeonEncryptionKey()));
	}
}

if (! function_exists('decrypt')) {
	/**
	 * @param string $data
	 * @return string - encrypted data
	 */
	function decrypt($data)
	{
		return neon()->security->decryptByKey(Str::base64UrlDecode($data), Env::getNeonEncryptionKey());
	}
}

if (! function_exists('get_alias')) {
	/**
	 * @param string $path
	 * @return string - resolved path
	 */
	function get_alias($path)
	{
		return neon()->getAlias($path);
	}
}

if (! function_exists('on_url')) {
	/**
	 * This function is useful for checking if links should be active,
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
	 * @return boolean  - whether the request is on the current url
	 */
	function on_url(...$urls)
	{
		return \neon\core\helpers\Url::isUrl(...$urls);
	}
}
if (! function_exists('_t')) {
	/**
	 * Translates a message to the target language
	 * This function is a wrapper to the \Neon::t method
	 *
	 * You can add parameters to a translation message that will be substituted with the corresponding value after
	 * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
	 *
	 * ```php
	 * $username = 'Neon';
	 * echo t('Hello, {username}!', ['username' => $username]);
	 * ```
	 * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
	 * message formatter. See [[\yii\i18n\I18N::translate()]] for more details.
	 *
	 * @param string $text the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $category  if provided will save the text to that category. Defaults to 'neon'
	 * @return string the translated message.
	 */
	function _t($text, $params=[], $category='neon')
	{
		return \Neon::t($category, $text, $params);
	}
}
if (! function_exists('faker')) {
	/**
	 * Generate a faker object
	 * @return \Faker\Generator;
	 */
	function faker()
	{
		static $faker = null;
		if ($faker === null)
			$faker = \Faker\Factory::create();
		return $faker;
	}
}
if (! function_exists('user_is')) {
	/**
	 * @return boolean whether the user is/has a particular role
	 */
	function user_is($role)
	{
		return neon()->user->is($role);
	}
}

if (! function_exists('user_can')) {
	/**
	 * Whether the user can perform a permission
	 * @param $permission
	 * @return bool
	 */
	function user_can($permission)
	{
		return neon()->user->can($permission);
	}
}
if (! function_exists('uuid')) {
	/**
	 * Generate a universally unique id
	 * @param string $separator - default '-'
	 * @return bool
	 */
	function uuid($separator='-')
	{
		return \neon\core\helpers\Hash::uuid($separator);
	}
}
if (! function_exists('uuid64')) {
	/**
	 * Generate a universally unique id in base64
	 * @return bool
	 */
	function uuid64()
	{
		return \neon\core\helpers\Hash::uuid64();
	}
}
if (! function_exists('id')) {
	/**
	 * Generate a html friendly UUID64
	 * @return string
	 */
	function id()
	{
		return \neon\core\helpers\Hash::id();
	}
}
if (! function_exists('is_route')) {
	/**
	 * Test whether the current application is on a particular route
	 * This is url rewrite safe! However you must reference the module/controller/action and not the url directly
	 * - sometimes these are very similar but they can all be overriden
	 * For e.g. cms routes are on_route('/cms/render/page', ['nice_id'=>'home'])
	 *
	 * @param string $pattern
	 * @param array $params
	 * @return boolean - whether on the route
	 */
	function is_route($pattern, $params=[])
	{
		return neon()->request->isRoute($pattern, $params);
	}
}
if (! function_exists('abort')) {
	/**
	 * Throw a HttpException with the given data.
	 *
	 * @param  int  $code
	 * @param  string  $message
	 * @return void
	 *
	 * @throws \yii\web\HttpException
	 */
	function abort($code, $message = '')
	{
		throw new \yii\web\HttpException($code, $message);
	}
}

if (! function_exists('abort_if')) {
	/**
	 * Throw a HttpException with the given data if the given condition is true.
	 *
	 * @param  bool  $boolean
	 * @param  int  $code
	 * @param  string  $message
	 * @return void
	 *
	 * @throws \yii\web\HttpException
	 */
	function abort_if($boolean, $code, $message = '')
	{
		if ($boolean) {
			abort($code, $message);
		}
	}
}
if (! function_exists('request')) {
	/**
	 * Get an instance of the current request or an input item from the request.
	 *
	 * @param  array|string|null  $key
	 * @param  mixed  $default
	 * @return \neon\core\web\Request|string|array
	 */
	function request($key = null, $default = null)
	{
		if (is_null($key)) {
			return neon()->request;
		}

		$value = neon()->request->get($key);

		return is_null($value) ? value($default) : $value;
	}
}
if (! function_exists('object_get')) {
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param  object  $object
	 * @param  string|null  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') {
			return $object;
		}

		foreach (explode('.', $key) as $segment) {
			if (! is_object($object) || ! isset($object->{$segment})) {
				return value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

function asset($path)
{
	return neon()->cms->themeAssetUrl($path);
}