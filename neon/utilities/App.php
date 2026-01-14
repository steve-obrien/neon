<?php

namespace neon\utilities;

use neon\utilities\utils\DbInfo;
use neon\utilities\utils\PhpInfo;
use neon\utilities\utils\SystemReport;
use neon\utilities\utils\Updates;

/**
 * The neon core settings app class
 *
 * @property \neon\settings\services\SettingsManager  $settingsManager
 */
class App extends \neon\core\BaseApp
{
	/**
	 * @event RegisterUtilitiesEvent The event that is triggered when registering utilities
	 *
	 * Utility types must implement [[IUtility]]. [[\neon\utilities\utils\IUtility]] provides a base implementation.
	 * ---
	 * ```php
	 * use neon\utilities\events\RegisterUtils;
	 * use neon\utilities\App as Utility;
	 * use yii\base\Event;
	 *
	 * Event::on(Utility::class, Utility::RegisterUtils, function(Register $event) {
	 *     $event->utils[] = MyUtilityType::class;
	 * });
	 *
	 * // or
	 *
	 * neon()->getApp('utilities')->onRegisterUtils(function(Register $event) {
	 *     $event->utils[] = MyUtilityType::class;
	 * });
	 * ```
	 */
	const EVENT_REGISTER_UTILITIES = 'registerUtilities';

	/**
	 * Returns all available utility type classes.
	 *
	 * @return string[]
	 */
	public function getAllUtilities(): array
	{
		$utils = [
			Updates::class,
			SystemReport::class,
			PhpInfo::class,
			DbInfo::class,
			Cache::class,
			DeprecationErrors::class,
			DbBackup::class,
			Migrations::class,
			Errors::class
		];

		$event = new RegisterUtils(['utils' => $utils]);
		$this->trigger(self::EVENT_REGISTER_UTILITIES, $event);

		return $event->utils;
	}

	/**
	 * Returns all utility type classes that the user has permission to use.
	 *
	 * @return string[]
	 */
	public function getAuthorizedUtilities(): array
	{
		$utilityTypes = [];

		foreach ($this->getAllUtilities() as $class) {
			if ($this->checkAuthorization($class)) {
				$utilityTypes[] = $class;
			}
		}

		return $utilityTypes;
	}

	/**
	 * Returns whether the current user is authorized to use a given utility.
	 *
	 * @param string $class The utility class
	 * @return bool
	 */
	public function checkAuthorization(string $class): bool
	{
		/** @var string|IUtilitiy $class */
		return neon()->getUser()->checkPermission('utility:' . $class::id());
	}

	/**
	 * Returns a utility class by its ID
	 *
	 * @param string $id
	 * @return string|null
	 */
	public function getUtilityTypeById(string $id)
	{
		foreach ($this->getAllUtilityTypes() as $class) {
			/** @var UtilityInterface $class */
			if ($class::id() === $id) {
				return $class;
			}
		}

		return null;
	}

	/**
	 * Shorthand method to register an event
	 *
	 * @param Callable $handler
	 */
	public function onRegisterUtils($handler)
	{
		$this->on(self::EVENT_REGISTER_UTILITIES, $handler);
	}

	/**
	 * @inheritdoc
	 */
	public function getMenu()
	{
		return [
			'label' => $this->getName(),
			'order' => 2000,
			'url' => ['/utilities/index/index'],
			'visible' => neon()->user->is('neon-administrator'),
			'active' => is_route('/utilities*')
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Utilities';
	}

}
