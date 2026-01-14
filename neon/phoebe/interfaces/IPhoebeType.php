<?php

namespace neon\phoebe\interfaces;

use neon\phoebe\interfaces\IPhoebeBase;

/**
 * Manage classes and objects defined under a particular Phoebe type
 * e.g. all database classes or application forms
 */
interface IPhoebeType extends IPhoebeBase
{

	/** -------------------------------------- **/
	/** ------------ Class Methods ----------- **/

	/**
	 * List all of the classes for this phoebeType. For example
	 * under the type 'database' get all of the tables, or under the type
	 * 'applicationForm' get the set of application forms available.
	 *
	 * @param array &$paginator  an array of 'start', 'length' and if start is 0
	 *   on return includes 'total'. If not set this defaults to 'start'=0,
	 *   'length'=100.
	 * @param array $filters  a set of field=>value pairs to filter down the results.
	 *   Filters are assumed to be either equal or for strings, like clauses.
	 *   Available fields are: label, description, module
	 * @param string|array $orderBy  a string of 'field [DIR]' or array of
	 *   'field' => 'SORT_DIR' where DIR is ASC or DESC
	 * @param boolean $includeDeleted  if set to true will return all
	 *   classes that have been soft deleted
	 *
	 * @return array of
	 *   ['class_type'] => [ 'class_type', 'module', 'label', 'description', ['deleted'] ]
	 */
	public function listClasses(&$paginator=[], $filters=[], $orderBy=[], $includeDeleted=false);

	/**
	 * Create an IPhoebeClass interface for a new class reference.
	 *
	 * @param string $classType  a new unique reference for the class
	 *   This will be canonicalised during the call to a standard format
	 * @param string $module  which module is creating this class (if applicable)
	 *   can be used as a way to sub-divide classes by a module within the system
	 * @return \neon\phoebe\interfaces\IPhoebeClass
	 * @throw  \RuntimeException  if the classRef already exists or something
	 *   else went wrong during creation
	 */
	public function addClass(&$classType, $module='');

	/**
	 * Get hold of an IPhoebeClass interface for a particular class reference.
	 *
	 * @param string $classType  an existing reference for the class
	 * @param int $version  if set get this version, if not get the latest version
	 * @param array|false $classOverrideCriteria  set these to determine which
	 *   class override applies to the object on creation. These can be 'selector'
	 *   and 'date'. If this is set to false, not override will be applied
	 * @return null | \neon\phoebe\interfaces\IPhoebeClass
	 *   returns null if the classType or version doesn't exist
	 */
	public function getClass($classType, $version=null, $classOverrideCriteria=[]);

	/**
	 * Get hold of a set of IPhoebeClasses for a set of classType. The latest version
	 * is returned in each case unless otherwise specified
	 *
	 * @param array $classTypes  a set of class types
	 * @return array of  ['classType'=> null | \neon\phoebe\interfaces\IPhoebeClass]
	 */
	public function getClasses($classTypes);

	/**
	 * Get the different versions available for this class
	 * @param string $classType  the class type you want the versions of
	 * @return array  array of different versions
	 */
	public function listClassVersions($classType);


	/** -------------------------------------- **/
	/** ---------- Instance Methods ---------- **/

	/**
	 * List the instances of a particular
	 * @param string $classType  the class type of the objects
	 * @param array &$paginator  an array of 'start', 'length' and if start is 0
	 *   on return includes 'total'. If not set this defaults to 'start'=0,
	 *   'length'=100.
	 * @param array $filters  a set of field=>value pairs to filter down the results.
	 *   Filters are assumed to be equals.
	 *   Available fields depend on the phoebe type but for basic are:
	 *     version, created, updated
	 * @param string|array $orderBy  a string of 'field [DIR]' or array of
	 *   'field' => 'SORT_DIR' where DIR is ASC or DESC. Defaults to reverse
	 *   order of creation
	 * @param array $additionalFields  set to additional fields that are required.
	 *   E.g. ['data', 'deleted']. If you include the deleted column, the query
	 *   will return all rows that have been soft deleted.
	 * @return array  an array of objects of this class
	 */
	public function listObjects($classType, &$paginator=[], $filters=[], $orderBy=[], $additionalFields=[]);

	/**
	 * Create and save a new IPhoebeObject of a particular class type
	 *
	 * @param string $classType  the class type
	 * @param array|false $classOverrideCriteria  set these to determine which
	 *   class override applies to the object on creation. These can be 'selector'
	 *   and 'date'. If this is set to false, not override will be applied
	 * @return \neon\phoebe\interfaces\IPhoebeObject
	 * @throw \RuntimeException if anything went wrong during the addition
	 */
	public function addObject($classType, $classOverrideCriteria=[]);

	/**
	 * Get hold of a phoebe object interface for managing an instance
	 * of a particular class ref
	 *
	 * @param string $objectId  the id of the phoebe object you want to manage
	 * @return null | \neon\phoebe\interfaces\IPhoebeObject
	 *   Returns null if an object of this phoebe type is not found
	 */
	public function getObject($objectId);
}
