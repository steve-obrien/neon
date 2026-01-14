<?php

namespace neon\utilities\events;

use yii\base\Event;

/**
 * Class RegisterUtils
 * Event fired during utility registration enabling the registration of other utility classes
 * @package neon\utilities\events
 */
class RegisterUtils extends Event
{
	/**
	 * @var string[] List of registered util classes - must implement IUtility.
	 */
	public $utils = [];
}