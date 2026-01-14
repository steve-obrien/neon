<?php

namespace neon\phoebe\interfaces;

use neon\phoebe\interfaces\IPhoebeBase;

/**
 * Phoebe object interface
 *
 * This deals with creating and saving instances of particular objects
 * e.g. new rows in a database table, or new instances of some complex application
 * object.
 *
 * @see IPhoebeClass for the creation of new class types
 *
 * An object has the following available attributes most of which are record keeping
 * (@see neon\phoebe\adapters\models\PhoebeObject)
 *
 * Record Keeping
 * @property string $uuid  - an identifier for the object
 * @property string $phoebe_type  - the phoebe_type it belongs to (e.g. daedalus)
 * @property string $class_type - the class_type it belong to (e.g. table name)
 * @property integer $version - which class version it was created with
 * @property datetime $created - when it was created
 * @property datetime $updated - when it was last updated
 * @property boolean $deleted - whether or not it is deleted
 *
 * Data parameters
 * @property array $data - the specific data for the object type
 * @property string $serialised_data - serialised version of $data (use $data)
 */
interface IPhoebeObject extends IPhoebeBase
{
	/** -------------------------------------- **/
	/** ---------- Object Lifecycle ---------- **/

	/**
	 * Save changes in the 'data' for a object instance. Other fields are
	 * not available for changing through this method.
	 *
	 * Depending on the adapter, this will either replace the data or
	 * be appropriately merged with existing (e.g. for daedalus)
	 * Record keeping fields cannot be saved
	 *
	 * @param array $changes  the changes to be saved
	 * @return true | array  Returns true if successfully saved
	 *   or the model errors otherwise
	 */
	public function editObject($changes);

	/**
	 * Soft delete the object instance
	 */
	public function deleteObject();

	/**
	 * Undelete the object instance
	 * Returns true on success and an array of errors on failure
	 * @return boolean | array
	 */
	public function undeleteObject();

	/**
	 * Destroy the object instance
	 */
	public function destroyObject();

	/**
	 * Returns a changeLogUuid if relevant to this type of object
	 */
	public function getChangeLogUuid();


	/** ------------------------------------------ **/
	/** ---------- Object Class Methods ---------- **/

	/**
	 * This will return an IPhoebeClass object which this object is an instance
	 * of. This will include any overrides required for the class
	 * @return \neon\phoebe\interfaces\IPhoebeClass
	 */
	public function getIPhoebeClass();

	/**
	 * Set an override on the object based on some criteria. If the object
	 * already has an override set, this will be ignored unless $force is set
	 *
	 * @param uuid $overrideUuid  the uuid of the override to set on the object
	 * @param boolean $force  set to true to force the override in the case
	 *   there is one already set
	 * @return bool | array  returns true on success; false if there is already
	 *   an override and $force was not true, or the override does not apply to
	 *   this class of object; and array if there are any other errors
	 */
	public function setOverride($overrideUuid, $force=false);

	/**
	 * Get the class override used when creating the object
	 * @return  the override uuid
	 */
	public function getOverride();


	/** -------------------------------------- **/
	/** ----------- Object History ----------- **/

	/**
	 * List the object history items in reverse archived order.
	 *
	 * @param array &$paginator  an array of 'start', 'length' and if start is 0
	 *   on return includes 'total'. If not set this defaults to 'start'=0,
	 *   'length'=100.
	 * @return array  an array of history entries with
	 *   the historical uuid, and date archived
	 */
	public function listHistory(&$paginator=[]);

	/**
	 * Get the object's historical data from its historical uuid
	 * @param type $historicalUuid  the uuid of the historical entry
	 * @param null | array  the historical object or null if no such historical entry
	 *   exists for this object
	 */
	public function getHistoricalObject($historicalUuid);

	/**
	 * clear all of the history for this object
	 */
	public function clearHistory();
}
