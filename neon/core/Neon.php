<?php

require  NEON_VENDOR . '/yiisoft/yii2/BaseYii.php';

/**
 * The Neon class extends YiiBase and is a convenience class for setting up a Yii application
 *
 * The Neon class mainly loads in the configuration files, merges them, then launches a new \neon\Application
 * This also holds the $container and the $app static instance of the currently running application
 * @author Steve OBrien <steve@newicon.net>
 *
 */
class Neon extends \yii\BaseYii
{
	/**
	 * Returns a string representing the current version of the Neon framework.
	 * @return string the version of Neon framework
	 */
	public static function getVersion()
	{
		return trim(file_get_contents(__DIR__ . '/../../version'));
	}

	/**
	 * Returns a string representing the current version of the Yii framework.
	 * @return string the version of Yii framework
	 */
	public static function getYiiVersion()
	{
		return parent::getVersion();
	}

	/**
	 * Creates a new object using the given configuration.
	 *
	 * You may view this method as an enhanced version of the `new` operator.
	 * The method supports creating an object based on a class name, a configuration array or
	 * an anonymous function.
	 *
	 * Below are some usage examples:
	 *
	 * ```php
	 * // create an object using a class name
	 * $object = \Neon::createObject('yii\db\Connection');
	 *
	 * // create an object using a configuration array
	 * $object = \Neon::createObject([
	 *     'class' => 'yii\db\Connection',
	 *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
	 *     'username' => 'root',
	 *     'password' => '',
	 *     'charset' => 'utf8',
	 * ]);
	 *
	 * **Note** also supports '__class' instead of 'class'
	 *
	 * // create an object with two constructor parameters
	 * $object = \Neon::createObject('MyClass', [$param1, $param2]);
	 * ```
	 *
	 * Using [[\yii\di\Container|dependency injection container]], this method can also identify
	 * dependent objects, instantiate them and inject them into the newly created object.
	 *
	 * @param string|array|callable $type the object type. This can be specified in one of the following forms:
	 *
	 * - a string: representing the class name of the object to be created
	 * - a configuration array: the array must contain a `class` element which is treated as the object class,
	 *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
	 * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
	 *   The callable should return a new instance of the object being created.
	 *
	 * @param array $params the constructor parameters
	 * @return object the created object
	 * @throws InvalidConfigException if the configuration is invalid.
	 * @see \yii\di\Container
	 */
	public static function createObject($type, array $params = [])
	{
		if (is_array($type) && isset($type['component'])) {
			$class = $type['component'];
			unset($type['component']);
			return static::$container->get($class, $params, $type);
		}
		if (is_string($type)) {
			// neon uses leading slashes inconsistently
			$type = ltrim($type, "\\");
		}
		return parent::createObject($type, $params);
	}

	/**
	 * Specify to use one class in favour of another (this only works when neon/yii is used to build instances)
	 * @param $forClass string
	 * @param $useClass string
	 */
	public static function setClassPreference($forClass, $useClass)
	{
		\Yii::$container->set($forClass, $useClass);
	}

	/**
	 * A convenient alias of Neon::$app->getApp();
	 * Load the app module by the string id
	 * @param string $name the id name of the app module if no name specified then it will
	 * return the parent Neon::$app object
	 * @return  \neon\core\ApplicationWeb|\neon\core\BaseApp
	 */
	public static function app($name = null)
	{
		if ($name === null) {
			return static::$app;
		}
		return static::$app->getApp($name);
	}

	/**
	 * Returns a HTML hyperlink that can be displayed on your Web page showing "Powered by Neon" information.
	 * @return string an HTML hyperlink that can be displayed on your Web page showing "Powered by the Hub"
	 */
	public static function powered()
	{
		return 'Powered by <a href="' . self::neonLink() . '" rel="external" target="_blank">Neon</a>';
	}

	/**
	 * Get the link to the neon framework website
	 * @return string
	 */
	public static function neonLink()
	{
		return 'http://neon.newicon.net';
	}

	/**
	 * @var array store profileTickeBomb start times
	 */
	protected static $_profileTickerBomb = [];

	/**
	 * Similar to Neon::beginProfile but explodes! It will raise an error if the functions it is profiling
	 * take longer than the time in seconds specified by $maxTime on self::endProfileTickerBomb()
	 * @param string $token a string token to uniquely identify this profile
	 * @param string $category a category string to group this profile
	 */
	public static function beginProfileTickerBomb($token, $category = 'application')
	{
		if (neon()->debug) {
			self::beginProfile($token, $category);
			self::$_profileTickerBomb[$token] = microtime(true);
		}
	}

	/**
	 * Similar to Neon::endProfile but raises an error log if the time the work this is profiling takes
	 * longer than specified.  This is useful to alert to errors in apps where they must perform
	 * a function within a maximum time limit in order to keep an eye on performance.
	 * @param string $token a string token to uniquely identify this profile
	 * @param float|integer $maxTime the maximum time in seconds, if the work this is profiling takes longer than
	 * this time then an error is raised.
	 * @param string $category a category string to group this profile
	 * @param string $error the error message to show if the maxTime has been exceeded
	 */
	public static function endProfileTickerBomb($token, $category = 'application', $maxTime = 1, $error = null)
	{
		if (neon()->debug) {
			$start = self::$_profileTickerBomb[$token];
			$stop = microtime(true);
			$bootstrapTime = $stop - $start;
			if ($bootstrapTime > $maxTime) {
				$error = $error ? $error : 'The profile "' . $token . '" took longer than ' . $maxTime . ' seconds';
				\Neon::error($error, $category);
			}
			self::endProfile($token, $category);
		}
	}

	private static $_logStack = [];

	public static function beginProfile($token, $category = 'application')
	{
		if (neon()->debug) {
			self::$_logStack[] = [$token, $category];
			parent::beginProfile($token, $category);
		}
	}

	public static function endProfile($token=null, $category = 'application')
	{
		if (neon()->debug) {
			if ($token === null)
				list($token, $category) = array_pop(self::$_logStack);
			parent::endProfile($token, $category);
		}
	}
}
