<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\interfaces;

use neon\core\interfaces\IArrayable;
use neon\core\interfaces\IJsonable;

/**
 * The interface for the settings service
 */
interface ISettingsManager extends IArrayable, IJsonable
{
	/**
	 * Get a setting value
	 *
	 * @param string $app the string id of the app responsible for this setting
	 * @param string $key the name key of the setting
	 * @param mixed $default the default value to return if the settings does not exist defaults to null
	 * @param boolean $refresh whther to refresh the cache before fetching this setting defaults to false
	 * @return string
	 */
	public function get($app, $key, $default=null, $refresh=false);

	/**
	 * Set a setting value
	 *
	 * @param string $app the string id of the app responsible for this setting
	 * @param string $key the name key of the setting
	 * @param mixed $value the value of the setting
	 * @return boolean success or fail
	 */
	public function set($app, $key, $value);

	/**
	 * Load the settings from the database and cache for the session
	 *
	 * @alias of getAll
	 * @deprecated use $this->getAll()
	 * @param boolean $refresh if true forces reftech fo the the settings
	 * @return array in the format @see self::$_settings
	 */
	public function getSettings($refresh=false);

	/**
	 * Load the settings from the database and cache for the session
	 *
	 * @param boolean $refresh if true forces refresh values from the database
	 * @return array in the format @see self::$_settings
	 */
	public function getAll($refresh=false);

}
