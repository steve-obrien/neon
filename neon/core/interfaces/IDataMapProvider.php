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
 * The set of methods that the form builder can call on applications
 */
interface IDataMapProvider
{
	/**
	 * get the application specific set of map types available to the form builder
	 * @return array  an array of map types key => label
	 *   where the key is used in @see getDataMap
	 */
	public function getDataMapTypes();

	/**
	 * get the map corresponding to the supplied map key
	 *
	 * @param string $key - defines the dataMapType a string identifying a class that can provide maps.
	 *   For example if the provider is dds then the key would allow you to select which class (table) you want.
	 *   This key would appear in the result of $this->getDataMapTypes
	 * @param string $query - a test string search on the map fields
	 * @param array|string $filters - an array of additional filters as 'fieldkey'=>'value'.
	 * @param array $fields - if this is set, the fields will be returned either
	 *   as an array of [key=>[fieldValues]]
	 * @param int $start - the row offset to start at
	 * @param int $length - the number of rows to return
	 * @return array an array of key value pairs
	 */
	public function getDataMap($key, $query='', $filters=[], $fields=[], $start = 0, $length = 100);

	/**
	 * Looks up maps from the map ids.
	 * This issues the request and returns a string key that can be used in `$this->getMapResults($key)` to
	 * access the results.
	 * This is useful for e.g. for displaying select boxes that already have selected value
	 * @see getMapResults
	 * @param string $key - defines the dataMapType a string identifying a class that can provide maps.
	 *   Dds does not require a key here so it will be ignored
	 * @param array $ids  an array of ids for the objects you want the reverse map on
	 * @param array $fields   if set then return these fields in the order provided.
	 *   If not set then return the default map field for the class.
	 * @return string
	 */
	public function makeMapLookupRequest($key, $ids, $fields=[]);

	/**
	 * Return results from a previously called makeMapLookupRequest request
	 * @see makeMapLookupRequest
	 * @param string $requestKey - the $requestKey result provided by $this->makeMapLookupRequest()
	 * @return array
	 */
	public function getMapResults($requestKey);
}