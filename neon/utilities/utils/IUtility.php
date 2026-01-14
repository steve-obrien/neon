<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/05/2017
 * @package neon
 */

namespace neon\utilities\utils;

interface IUtility
{
	/**
	 * Display name to output for this utility
	 *
	 * @return string
	 */
	public static function displayName(): string;

	/**
	 * Returns the utility’s unique identifier.
	 *
	 * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
	 *
	 * @return string
	 */
	public static function id(): string;

	/**
	 * Returns the path to the utility’s SVG icon.
	 *
	 * @return string|null
	 */
	public static function iconPath();

	/**
	 * Returns the number that should be shown in the utility’s nav item badge.
	 *
	 * If `0` is returned, no badge will be shown
	 *
	 * @return int
	 */
	public static function badgeCount(): int;

	/**
	 * Returns the utility's content HTML.
	 *
	 * @return string
	 */
	public static function contentHtml(): string;
}
