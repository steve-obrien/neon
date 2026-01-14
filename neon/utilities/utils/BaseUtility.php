<?php

use craft\base\Component;
use craft\base\UtilityInterface;
use neon\uutilities\utils\IUtility;


/**
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * @author Newicon <support@newicon.net>
 * @since 1.1.10
 */
abstract class BaseUtility implements IUtility
{
	// Static
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public static function iconPath()
	{
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public static function badgeCount(): int
	{
		// 0 = no badge
		return 0;
	}
}