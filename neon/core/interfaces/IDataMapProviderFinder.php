<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 - Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Neill Jones <neill.jones@newicon.net> 01/12/2016
 * @package neon
 */

namespace neon\core\interfaces;

/**
 * The IDataMapProviderFinder finds IDataMapProviders within neon.
 * This performs two main jobs - can return a DataMapProvider object when given a provider name,
 * And can list all providers and their maps that exist within neon.
 */
interface IDataMapProviderFinder
{
	/**
	 * Form Builder Maps
	 * =================
	 *
	 * A form builder map allows the form builder to create a 'map' widget which
	 * will then get the application data when used on a form.
	 *
	 * A map is a set of key value pairs and can be used for example in choices.
	 * Each available map has a ???
	 * It is up to the App to know how to provide these.
	 *
	 */

	/**
	 * This method returns all discoverable providers
	 * @return array of provider names and their maps in the following format
	 * ```php
	 * [
	 *     'dds' => [
	 *         'key' => 'dds',
	 *         'name' => 'dds',
	 *         'maps' => [
	 *             'people' => 'People',
	 *             'emails' => 'Email addresses'
	 *         ],
	 *     ],
	 *     'cms' => [
	 *         'key' => 'cms',
	 *         'name' => 'Cobe CMS'
	 *         'maps' => [
	 *             'pages' => 'Pages',
	 *         ]
	 *      ],
	 *      // ...
	 * ]
	 */
	public function getDataMapProviders();

	/**
	 * Returns the object implementing the IDataMapProvider interface enabling calls
	 * to getDataMap for e.g. `neon()->getDataMapProvider('dds')`
	 * @param string $name
	 * @return IDataMapProvider
	 */
	public function getDataMapProvider($name);
}