<?php

namespace neon\daedalus\interfaces;

/**
 * ----- IDdsObjectManagement Overview -----
 *
 * This interface is how you request data from Daedalus, add, update, delete
 * and destroy data objects. For general data queries (selects) you should use
 * the request and commit set of methods (detailed first) whereas for single
 * object CRUD and rarer use cases, use the object methods detailed later on.
 *
 * The request methods are optimised to run concurrently but are handled in a
 * set of requests, followed by a commit at which point all of the queued requests
 * are made and data sets returned. This can change how you would code your
 * data requests as you cannot expect an immediate response
 *
 * ------------------------------------------------
 * ----- Filters Used in IDdsObjectManagement -----
 *
 * Filters allow you to create reasonably complex where clauses to select
 * the required objects. The interface here is easy enough to handwrite
 * as well as more convenient for form based production.
 *
 * Concepts
 * ========
 *
 * Filters are converted into the where clause for a normal SQL query. A
 * reasonably complex clause could be
 *
 * WHERE ((field1 > 10 AND field1 <= 20) OR (field1 < 30)) AND field3 = 'x'.
 *
 * This statement can be broken down into clauses consisting of a field, an
 * operator and a value. Each clause in the above is
 *   field1 > 10
 *   field1 <= 20
 *   field1 < 30
 *   field3 = 'x'
 * This are then combined together using logic. These ideas are expanded
 * below. Queries that can't be broken down this way can't be defined
 * using this method.
 *
 * Detail
 * ======
 *
 * A data request contains a 'Filters' parameter.
 *
 * 'Filters' is a set of individual filter definitions. Each filter will be
 * ORed together in the final query. Each filter consists of separate clauses
 * and an optional logic defining how they are to be combined.
 *
 * In more detail ... (examples later)
 *
 * $filters  - these are an array of individual 'filter's [f1, f2] or if
 * there is a single filter only, then it can just be the filter.
 *
 * $filter - this is an array of query clauses [ $C1, $C2, ...] or if there
 * is only a single clause then it can just bs the clause.
 *
 * For multiple clauses an optional logic definition can also be provided.
 * Each clause is AND'ed together unless the logic definition specifies
 * otherwise.
 *
 * In more detail a filter is an array
 * [
 *   $clause1,
 *   $clause2,
 *   ...,
 *   'logic' => if the filter is anything other than ANDing components
 *   then give each component a key and the combination logic here
 *   as a string of e.g.
 *     "('key1' OR ('key2' AND 'key3'))",
 *   and the keys will be replaced with the appropriate clauses
 * ]
 *
 * $clause - each clause is an array defined as
 *   array (
 *     [0] => the member_ref of the item
 *     [1] => one of the following operators
 *       '=' equals or if passed an array an IN => value
 *       '!=' not equals or for array NOT IN => value
 *       '>' greater than,
 *       '<' less than,
 *       '>=' greater or equal to,
 *       '<=' less than or equal to,
 *       'in' value in following array
 *       'not in' value not in following array
 *       'like' value for a text search comparison
 *       'not like' value for a text search comparison
 *       'null' set if testing for null [value not required]
 *       'not null' set if testing for not null [value not required]
 *     [2] => the value if required
 *
 *     // and finally optionally
 *     [3] => key   key in logic string if provided in the filter
 *   )
 *
 * Examples
 * ========
 *
 * 1. Simple single clause
 * ------------------------
 * WHERE 'name' = 'Jim Kirk'
 *
 * Alternatives are:
 * $filters = [ 'name', '=', 'Jim Kirk' ]
 * $filters = [ [ 'name', '=', 'Jim Kirk'] ]
 * $filters = [ [ [ 'name', '=', 'Jim Kirk' ] ] ]
 *
 * 2. Simple multi clause single filter
 * --------------  --------------------
 * WHERE 'name' = 'Jim Kirk' AND 'date_of_birth' = '2233-03-22'
 *
 * Alternatives are:
 * $filters = [
 *   [ 'name', '=', 'Jim Kirk' ], [ 'date_of_birth', '=', '2233-03-22']
 * ]
 * or
 * $filters = [
 *   [ [ 'name', '=', 'Jim Kirk' ], [ 'date_of_birth', '=', '2233-03-22'] ]
 * ]
 *
 * 3. Multiple filters
 * -------------------
 * WHERE 'name' = 'Jim Kirk' OR 'name' = 'Spock'
 *
 * $filters = [
 *   [0] => [ [ 'name', '=', 'Jim Kirk' ] ],
 *   [1] => [ [ 'name', '=', 'Spock'    ] ]
 * ]
 *
 * or since this can be rewritten as
 * WHERE 'name' IN ('Jim Kirk', 'Spock')
 *
 * $filters = [ 'name', '=', ['Jim Kirk', 'Spock'] ]
 * or
 * $filters = [ 'name', 'in', ['Jim Kirk', 'Spock'] ]
 *
 *
 * 3. Filter requiring logic string
 * --------------------------------
 *
 * The example at the beginning was
 *
 * WHERE ((field1 > 10 AND field1 <= 20) OR (field1 < 30)) AND field3 = 'x'
 *
 * This requires a logic parameter to be added which maps to this. Each clause
 * needs to be defined by a key so this can be reduced to a logic string of
 *   "((key1 AND key2) OR (key3)) AND key4"
 *
 * $filters = [
 *   [0] => [
 *     [ 'field1', '>',  10,  'key1' ],
 *     [ 'field1', '<=', 20,  'key2' ],
 *     [ 'field1', '<',  30,  'key3' ],
 *     [ 'field3', '=',  'x', 'key4' ],
 *     'logic' => "((key1 AND key2) OR (key3)) AND key4"
 *   ]
 * ]
 *
 * The logic string is heavily constrained to prevent SQL injection. The only
 * characters allowed after the keys, AND, OR and NOT are ( and ). Anything else
 * will result in the request being rejected
 *
 * --------------------------------------
 * ---------- Data Conversions ----------
 *
 * On saving of and retrieval of data, some conversion of data takes place.
 * The following conversions are automatically performed
 *
 * 'boolean': this is converted from true/false to 1/0 on save and vice versa
 * 'json': this is converted from an array to JSON on save and vice versa
 * 'choice': this is converted from a key=>value pair to the key on save if the
 *   input was an array. On return it is converted back if the choice exists
 *   in the member choice field otherwise it is just the saved value.
 *
 *
 * ---------------------------------------
 * -------- Change Log Management --------
 *
 * If a class has a change log associated with it, Daedalus will record an
 * entry whenever an object is created, edited, deleted, undeleted and destroyed.
 * For single object methods e.g. addObject, Daedalus will return the change
 * log uuid as a final pass by reference parameter and these can be used for
 * higher level application functions such as audit or activity logs.
 *
 */
interface IDdsObjectManagement extends IDdsObjectMapManagement
{
	/** ---------- Requesting Object Sets ---------- **
	 *
	 * When retrieving data for general views, you should use the addRequest,
	 * commitRequest methods as this allows Daedalus to perform some efficiencies
	 * with regard to what is requested.
	 *
	 * Obtaining objects is a two step process - the first step is to place a
	 * request for an object. This can be done many times building up a series
	 * of requests. Once all the requests required have been placed then
	 * calling commit will fetch the objects from the database and return them
	 * in one go.
	 *
	 * The reason for doing this is simply to reduce the number of database
	 * calls that need to be made and is suitable for situations that might
	 * result in tens of hundreds of data requests.
	 *
	 * As far as possible write your code so that requests can be combined and
	 * committed in one go as this will significantly speed up the data requests.
	 *
	 * If (and only if) you need the results back directly and cannot use the idea
	 * of requests and commits, or its truly a single request then and only
	 * then use runSingleObjectRequest
	 */

	/**
	 * Method to make and get a single object request. Use this when you want to
	 * get an answer immediately irrespective of whether or not other requests
	 * are in progress.
	 *
	 *
	 * @see addObjectRequest and commitRequests for details of parameters and result sets
	 *
	 * @param string $classType
	 * @param array $filters
	 * @param array $order
	 * @param array $limit
	 * @param bool $includeDeleted  by default this method will not include soft deleted
	 *   data. If you do want to include soft deleted, set this to true
	 * @throws \InvalidArgumentException if $classType is empty
	 * @return array  a single result set as ['start', 'length', 'total', 'rows']
	 *   @see commitRequests for more details
	 */
	public function runSingleObjectRequest($classType, array $filters=[], array $order=[], array $limit=[], $includeDeleted=false);

	/**
	 * 
	 * @alias of runSingleObjectRequest but will cache results
	 */
	public function get($classType, array $filters=[], array $order=[], array $limit=[], $includeDeleted=false);
	
	/**
	 * Add an object request to the list of pending requests. Database queries can
	 * be grouped together into a set of requests and then got all at once using the
	 * commitRequests method. This allows Daedalus to perform some efficiencies
	 *
	 * @param string $classType  the class type you're requesting
	 * @param array $filters  the filters on the query - see the beginning of this
	 *   interface for details
	 * @param array $order  the order to return the data set in as an array of
	 *   ['member_ref' => 'ASC'|'DESC|RAND|ASC_L|DESC_L']. These will be applied in the order
	 *   given. If RAND is set then the order by will be replaced by a random
	 *   selection and other parameters are ignored. If ASC_L or DESC_L is used then
	 *   the member_ref will be taken as a literal string and then orders ASC, DESC.
	 *   This is useful if, for example, you need to cast a member ref for the ordering
	 * @param array $limit  the pagination information for this query as
	 *   ['start'=>#, 'length'=>#]. If no limit is given an internal one will be applied
	 *   to prevent memory issues. If no start is given, the beginning will be assumed.
	 *   if 'total' is provided, then a total count will be set on the results. If the
	 *   total is true, then a total will be calculated, otherwise it will return the
	 *   value passed in. 
	 *   NOTE - you cannot set a length which is greater than the maximum length
	 *   which is 1000 rows. If you want more than that you must request in chunks.
	 * @param string $resultKey  if this is provided, this will be used as the
	 *   result key in the results. If it is not supplied then one will be chosen
	 *   for you.
	 * @param bool $includeDeleted  by default this method will not include soft deleted
	 *   data. If you do want to include soft deleted, set this to true
	 *
	 * @return resultKey  the key that will match this query in the resultSet
	 * @throw InvalidParameterException  - if any of the parameters were invalid
	 *   or the resultKey has already been used. These indicate bugs in the
	 *   calling code so better not to catch it but deal with the bugs
	 */
	public function addObjectRequest($classType, array $filters=[], array $order=[], array $limit=[], $resultKey=null, $includeDeleted=false);

	/**
	 * Return the set of object requests currently queued
	 * @return ['requests', 'boundValues']  the set of requests
	 *   and their bound values. The requests are arrays of
	 *   ['classType']['resultKey'] => object request
	 */
	public function getObjectRequests();

	/**
	 * Returns true if there are waiting object requests to be processed (committed) false if not
	 * @return boolean
	 */
	public function hasObjectRequests();

	/**
	 * Commit the series of requests and get the results back
	 *
	 * @return resultSet  the set of results keyed by the resultKey parameters
	 *   passed in or generated during the addObjectRequest
	 * $resultSet = [
	 *   [resultKey1] => results,
	 *   [resultKey2] => results
	 *   ...
	 * ]
	 * where results is an array of [
	 *   'start' => starting point for this set
	 *   'length' => number returned
	 *   'total' => mixed. If this is set 'truthy' and start is zero, then this
	 *     is calculated. If this is 'falsey' then the total is not calculated.
	 *     If start was > 0 then it returns whatever was passed in so it can be
	 *     used for simple pagination
	 *   'rows' => [] => [id, class_type, label, deleted, {data}], ...
	 * ]
	 */
	public function commitRequests();

 	/**
	 * Extract data from the last committed requests
	 * @param string $requestKey  the request key you want to extract for
	 * @return array  the requested data or [] if none
	 */
	public function getLastCommitData($requestKey);


	/** ---------- Basic Object Management ---------- **
	 *
	 * These provide more direct access to a single or set of data objects.
	 * These are mostly intended for administration management and application
	 * updates.
	 */

	/**
	 * List the available objects of a particular class type - this will
	 * be limited to prevent memory overloading.
	 *
	 * For filtered views of the available objects use the @see addObjectRequest and
	 * @see runSingleObjectRequests methods
	 *
	 * @param string $classType  the type of object required. String max length 50
	 * @param integer &$total  the total number available returned after the call
	 * @param boolean $includeDeleted  if true include any deleted objects
	 * @param boolean $inFull  if true include all of the data else just bare object
	 * @param integer $start  the starting position for the list
	 * @param integer $length  the number you want returned.
	 * @param array $order  order by provided fields - if null orders by created date in reverse
	 * @return array of
	 *   [ _uuid, _class_type, _deleted, [{$data}]]
	 *   where 'data' is set if $inFull is true
	 */
	public function listObjects($classType, &$total=null, $includeDeleted=false, $inFull=true, $start=0, $length=100, $order=null);

	/**
	 * Create a new object
	 *
	 * @param string $classType
	 * @param array $data  an array of 'member_ref'=>value pairs the details
	 *   of which depend on the class definition. If you pass in a valid UUID
	 *   as '_uuid'=>char(22) then this will be used. Otherwise one will be created.
	 * @param string $changeLogId  if the class has a change log, this will be set
	 *   to the change log entry uuid
	 * @return string   the uuid for the object
	 * @throws exception  if anything went wrong with creating the object
	 */
	public function addObject($classType, array $data, &$changeLogId=null);

	/**
	 * Add multiple objects in bulk
	 *
	 * @param string $classType
	 * @param array $data  a collection of object arrays 'member_ref'=>value pairs the details
	 *   of which depend on the class definition. If the _uuid parameter is set and is valid
	 *   then this will be used. Otherwise uuids will be created.
	 * @param int $chunkSize the number of rows the function will bulk insert. If this is set to 1000
	 *   and you pass 1500 rows then this function will do two bulk insert commands - one with 1000 rows and one with 500
	 *   Note: Depending on the size of your row data you can optimise how many rows are bulk inserted.
	 *   The MySQL Global variable MAX_ALLOWED_PACKET and BULK_INSERT_BUFFER_SIZE must be large enough
	 *   for one batch (rows of the number defined by chunkSize). MAX_ALLOWED_PACKET refers to the total size
	 *   of the raw SQL text sent to MySQL server.
	 */
	public function addObjects($classType, array $data, $chunkSize=1000);

	/**
	 * Get an object's details
	 *
	 * To get details of multiple objects use the @see addObjectRequest and
	 * @see runSingleObjectRequests methods
	 *
	 * @param string $uuid  the uuid of the object you want details of
	 * @return array
	 *   [_uuid, _class_type, _deleted, _created, _updated, {data}]
	 *   where data is an array of 'member_ref'=>value pairs the details of
	 *   which depend upon the class definition
	 */
	public function getObject($uuid);

	/**
	 * Edit an object
	 *
	 * @param string $uuid  the uuid of the object you want to edit
	 * @param array $changes  an array of 'member_ref'=>value
	 *   pairs for the values that you want to change
	 * @param string $changeLogId  if the class has a change log, this will be set
	 *   to the change log entry uuid
	 * @return bool|array  returns true on successful save and
	 *   an array of errors if it couldn't save the object
	 */
	public function editObject($uuid, array $changes, &$changeLogId=null);

	/**
	 * Edit a series of objects with the *same* changes
	 *
	 * The use case for this method is limited - generally updates should not be
	 * applied equally across multiple objects as they are not the same. However
	 * there are some cases when this makes sense, such as setting the state of
	 * multiple objects to the same - e.g. a series of applications marked as
	 * unsuccessful.
	 *
	 * It is not possible (currently) to update links using this method as this
	 * is more likely to be a programmatic error rather than a realistic use case.
	 *
	 * @param string $classType  the class type of the object you want to edit
	 * @param array $uuids  the uuids of the objects you want to edit
	 * @param array $changes  an array of 'member_ref'=>value
	 *   pairs for the values that you want to change
	 * @return bool  whether or not the query was successful. It will fail if either
	 *   uuids or changes are empty, if no changes could be applied or the query died
	 */
	public function editObjects($classType, array $uuids, array $changes);

	/**
	 * Soft delete an object - soft deleted objects will not appear in the view or
	 * in the listing unless $includeDeleted is set to true. They remain in the
	 * database.
	 *
	 * @param string $uuid  the uuid of the object you want to delete
	 * @param string $changeLogId  if the class has a change log, this will be set
	 *   to the change log entry uuid
	 */
	public function deleteObject($uuid, &$changeLogId=null);

	/**
	 * Soft delete a set of objects.
	 *
	 * @param array $uuids  the objects you want to delete
	 */
	public function deleteObjects(array $uuids);

	/**
	 * Undelete an object marked as deleted
	 *
	 * @param string $uuid  the uuid of the object you want to undelete
	 * @param string $changeLogId  if the class has a change log, this will be set
	 *   to the change log entry uuid
	 */
	public function undeleteObject($uuid, &$changeLogId=null);

	/**
	 * Destroy an object - this removes the data from the database can
	 * cannot be undone.
	 *
	 * @param string $uuid  the uuid of the object you want to destroy
	 * @param string $changeLogId  if the class has a change log, this will be set
	 *   to the change log entry uuid
	 */
	public function destroyObject($uuid, &$changeLogId=null);

	/**
	 * Destroy a set of objects. This cannot be undone
	 *
	 * @param array $uuids  the objects you want to delete
	 */
	public function destroyObjects(array $uuids);

	/**
	 * Get the number of objects in a class
	 * @param string $classType
	 * @return array
	 *   'total' => total number of objects
	 *   'active' => active number of objects
	 *   'deleted' => deleted number of objects
	 */
	public function getObjectCounts($classType);

	/**
	 * Realculate the object count for a class. Because people may use other methods to
	 * add / remove items from the database, the counts can get out of sync. Use this
	 * to recalculate the object count for a particular class.
	 */
	public function recalculateObjectCount($classType);

}
