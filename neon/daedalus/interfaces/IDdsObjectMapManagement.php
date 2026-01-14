<?php

namespace neon\daedalus\interfaces;

/**
 * ----- IDdsObjectMapManagement Overview -----
 *
 * This interface covers the object map management functions. The following
 * ideas are covered in this interface. Maps are useful for providing
 * choices to a user. A map is an array of [uuid=>string] pairs where the
 * uuid is the map key and the string is the map value.
 *
 * Object maps - these are arrays mapping object uuids to other fields on the
 * object table. For example, [uuid => name]. The class should have a default
 * map field which can be returned if no others are specified. To get the
 * maps available from a particular table @see getObjectMap.
 *
 * Map Lookups - these are where you know the uuids and want to get back the
 * maps corresponding to those uuids. For example, if you have a form and want
 * to populate it with the maps that were selected initially knowing only the
 * selected end uuids stored in the database.
 *
 * Map Chains - a map chain is a set of maps linked over a set of tables which
 * have at least a one to one relationship. For example you might have a set of
 * countries, counties, towns and streets. The country choice limits the option
 * of counties available. The choice of county limits the set of available towns
 * and towns limits streets.
 *
 * Creating a map chain going forwards from the country is straightforward and
 * can be done using @see getObjectMap where you lookup the counties given the
 * country id as a filter. Looking up a map chain going backwards from the street
 * depends on there being a one to many relationship down the chain, i.e. a
 * country can have many counties but the county would only belong in one country.
 * If this is true, and the link up the chain is stored on the lower item then it
 * is possible to recreate the map chain from the lowest item uuid using this
 * @see makeMapChainLoopupRequest. In this example that is the street. So long
 * as the street table contains its town uuid in it and the town contains the
 * county uuid and the county the country uuid, you can unambiguously recreate
 * the chain just from the street uuid.
 *
 * Map Requests - to keep map lookups as efficient as possible, map lookups
 * are first requested, and once all of the requests have been made, committed
 * and results retrieved. This means that, for example, a grid or form could
 * make multiple requests for data during their construction and then
 * retrieve the results once displaying without incurring potentially hundreds
 * of individual database calls.
 *
 * @see makeMapLookupRequest, makeMapChainLookupRequest, commitMapRequests
 * and getMapResults
 */
interface IDdsObjectMapManagement
{
	/**
	 * Get a map of uuids to default or other fields is specified
	 * This returns an exception if the class no longer exists or
	 * a default map field has not been defined for the class
	 *
	 * @param string $classType
	 * @param array $filters  a set of key=>value pairs for any filtering on the mapping.
	 *   Value can be an array or a single value. These are filtered using equality.
	 *   If the key is '_map_field_' then the class default map field will be substituted
	 *   and a LIKE rather than equality applied
	 * @param array $mapFields  if set then return these fields rather than the default in
	 *   the order provided. If a field is a link to another table, then the default map
	 *   field for that table will be used. Each of these fields must exist on the object map
	 * @param integer $start  the starting position
	 * @param integer $length  how many to return up to 1000
	 * @param boolean $includeDeleted  whether or not to include soft deleted objects
	 * @return array  [ uuid => map_field ] or if $fields is set [ uuid => [ map_fields ] ]
	 *
	 * @throw \InvalidArgumentException  if the class type does not exist
	 * @throw \RuntimeException  if the map field does not exist
	 */
	public function getObjectMap($classType, $filters=[], $mapFields=[], $start=0, $length=1000, $includeDeleted=false);


	/**
	 * Make a map lookup request. This is where you know the uuids and you want
	 * to get hold of the corresponding map. Use @see getMapLookupResults to get the result
	 *
	 * @param string|array $objectUuids  the uuid(s) of the object(s) you want the map for.
	 *   The objects should all belong to the same object class
	 * @param array $mapFields
	 *   If set then return these fields in the order provided.
	 *   If not set then return the default map field for the class. If a field is a
	 *   link to another table and $classType is provided, then the default map field for
	 *   that table will be used.
	 *   Each of these fields must exist on the objects mapped.
	 * @param string $classType  the Dds classType the objects belong to. If provided, links
	 *   in the mapFields will be linked to the map fields of the linked tables
	 * @return string $requestKey  returns a unique request key so the results can be
	 *   found after committing.
	 */
	public function makeMapLookupRequest($objectUuids, $mapFields=[], $classType=null);

	/**
	 * Get hold of the requested map lookup
	 *
	 * @param string $requestKey  the request key returned during the makeMapRequest call
	 * @return array  an array of the map lookup results as a normal map of uuid=>mao.
	 *   The map will be either an array or a string depending on whether or not mapFields
	 *   was passed in
	 */
	public function getMapLookupResults($requestKey);

	/**
	 * Make a map chain lookup request. See above for what a map chain is.
	 * This is where you know the end uuid and you want to get hold of
	 * the map chain all the way back through the related classes.
	 *
	 * The order of the chain is determined by @see $inReverse
	 *
	 * @param string|array $objectUuids  the uuid(s) of the object(s) you want the map for
	 * @param array $chainMembers  This is the set of member fields within the chain that
	 *   link *backwards* from the objectUuid to the beginning of the chain. Using the example
	 *   of country, county city and street, the database could be such that the street has
	 *   a cityId column, so the city can be found. Similarly the city has a countyId and
	 *   the county a countryId leading finally back to the country. The chain members
	 *   would then be ['cityId','countyId','countryId'].
	 * @param array $chainMapFields NOT IMPLEMENTED
	 *   If you are not using the default map fields for any of the objects in the chain
	 *   then provide the mapFields here. These are of the format
	 *   [ 'chainMember' => [$mapFields] ] e.g for the above could be
	 *   [ 'cityId' => [ city, long, lat ], ...] to return the (spurious case of)
	 *   longitude and latitude as well as the name of the city. To set fields for the
	 *   starting class which the objectUuids belong to, set these as [0]=>[ fields ]
	 *   TODO NJ 20190415 - Implement $chainMapFields
	 * @param boolean $inReverse  default false. This determines the order in which
	 *   results are provided in the chain. If this is false, then the first link will
	 *   be the one provided by the $objectUuid. If this is true then the first link
	 *   would be the last one in the $chainMembers list.
	 * @return string $requestKey  returns a unique request key so the results can be
	 *   found after committing. @see getMapChainLookupResult for the data structure
	 *   returned
	 */
	public function makeMapChainLookupRequest($objectUuids, $chainMembers=[], $chainMapFields=[], $inReverse=false);

	/**
	 * Commit the map requests stored so that all object chains are resolved
	 * together. This will be called automatically if @see getMapLookupResults
	 * or @see getMapChainLooupResults is called and there are pending map requests
	 */
	public function commitMapRequests();

	/**
	 * Get hold of the requested map chain lookups
	 *
	 * @param string $requestKey  the request key returned during the makeMapRequest call
	 * @return array  an array of the map lookup results.
	 *   When getting the results in @see getMap[Chain]LookupResults, these will be given as
	 *     [uuid1] => [ <-- a uuid passed in in $objectUuids
	 *       0=>[uuid=>map],  <-- the first map in the chain
	 *       1=>[uuid=>map],  <-- the second map in the chain
	 *       ...
	 *     ],
	 *     [uuid2]=>[
	 *       ...
	 *     ]
	 *   The order will depend on the inReverse setting
	 */
	public function getMapChainLookupResults($requestKey);

	/**
	 * Clear out all of the map requests
	 */
	public function clearMapRequests();
}