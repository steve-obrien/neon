<?php

namespace neon\daedalus\interfaces;

use neon\core\helpers\Iterator;


/**
 * ----- IDdsChangeLogManagement Overview -----
 *
 * This provides an interface for object change management
 * for those classes that have change management set
 *
 * A change log entry consists of the following data
 *   'log_uuid'  a unique identifier for the log entry
 *   'object_uuid'  the identifier for the object the entry is about
 *   'class_type'  the type of the object
 *   'change_key'  a general key to describe the change (one of
 *     'ADD', 'EDIT', 'DELETE', 'UNDELETE', 'DESTROY', 'RESTORE', 'COMMENT'
 *   'description'  a description of the changes made
 *   'who'  the identifier of who made the change
 *   'when'  when the change was made
 *   'before'  the changed data before the update (if applicable)
 *   'after' the changed data after the update (if applicable)
 *
 * A change log history entry consists of the following data
 *   'when' => date of the change
 *   'change_key' => type of the change
 *   'description'  a description of the changes made
 *   'object' => the object at that point in time
 *     For DESTROY changes this will be empty
 *     For ADD, EDIT, DELETE, UNDELETE and RESTORE changes data this will be
 *     the object data at that time.
 *     For COMMENT, this will be whatever the object was before the comment
 */
interface IDdsChangeLogManagement
{
	/**
	 * List which classes have a change log
	 *
	 * @return array  an array of the classes that have a change log
	 */
	public function listClassesWithChangeLog();

	/**
	 * Determines whether or not the classType has a change log
	 * @param string $classType
	 */
	public function hasChangeLog($classType);

	/**
	 * Set whether or not a class has a change log
	 * @param string $classType  the class you want to set the change log on
	 * @param boolean $to  what you want to set it to
	 */
	public function setChangeLog($classType, $to);

	/**
	 * List the change log across the system
	 *
	 * @param datetime $fromDate  if set then start from this point in time. If not
	 *   set start from the beginning of today
	 * @param \neon\core\helpers\Iterator $iterator  Iterate over the results.
	 * @return array  the change log data
	 */
	public function listChangeLog($fromDate=null, Iterator $iterator=null);

	/**
	 * List an object's change log, if the object's class has been set up to store changes
	 *
	 * @param uuid $uuid  the uuid of the object you want the changes for
	 * @param \neon\core\helpers\Iterator &$iterator  Iterate over the results
	 * @return If the object's class is set to have a change log, this will return the
	 *   change log for this object. Otherwise it will return a blank array.
	 */
	public function listObjectChangeLog($uuid, $fromDate=null, Iterator $iterator=null);

	/**
	 * List the history of an object, if the object's class has been set up to store changes.
	 * Each entry will be the full data for that object at that point in time.
	 *
	 * @param uuid $uuid  the uuid of the object you want the changes for
	 * @param \neon\core\helpers\Iterator &$iterator  Iterate over the results
	 * @return If the object's class is set to have a change log, this will return the
	 *   history for this object. Otherwise it will return a blank array.
	 */
	public function listObjectHistory($uuid, $fromDate=null, Iterator $iterator=null);

	/**
	 * Clear change log to a certain date and optionally for particular class types
	 *
	 * @param date $toDate  the date up until you want to clear the log
	 * @param array $classList  a set of class types you want to restrict the
	 *   operation too. How this is restricted depends on $preserveClassList.
	 *   If this is empty then all log items are cleared
	 * @param boolean $clearClassList  if true then the $classList is the set
	 *   of log items to be cleared and all others are preserved. If false,
	 *   then the $classList is the set of log items to be preserved and all
	 *   others are cleared.
	 */
	public function clearChangeLog($toDate, $classList=[], $clearClassList=true);

	/**
	 * Clear an object's change log
	 *
	 * @param string $uuid  the object you want to clear the change log for
	 * @param date $toDate  the date up until you want to clear the log.
	 */
	public function clearObjectChangeLog($uuid, $toDate);

	/**
	 * Add a change log entry
	 * This method is intended only to be used by Daedalus during it's internal operations
	 *
	 * @param string $objectUuid  the object that has changed
	 * @param string|array $class  Either the class type of the changed object
	 *   or the dds_class entry for the class type
	 * @param string $changeKey  the key describing the change. This is one of
	 *   'ADD', 'EDIT', 'DELETE', 'UNDELETE', 'DESTROY'
	 *   These are automatically generated through changes to data through Daedalus.
	 * @param array $before  the set of field=>value pairs before the change was made
	 *   This can be empty, if the item was created or deleted for example
	 * @param array $after  the set of field=>value pairs after the change was made
	 *   This can be empty, if the item was deleted or destroyed for example.
	 * @return string  the uuid of the created log entry
	 */
	public function addLogEntry($objectUuid, $class, $changeKey, $before=[], $after=[]);

	/**
	 * Get the log entry
	 * @param string $logUuid  the log entry you want to look at
	 * @return null | array  null if not found, otherwise the entry
	 */
	public function getLogEntry($logUuid);

	/**
	 * Add a comment into the change log. This is to allow the application to provide
	 * additional change log information when changes were made. E.g. it could provide
	 * extra context to a particular entry.
	 *
	 * Comments are restricted to Daedalus tables where the change log has been
	 * turned on. The object needs to exist in the table too. Otherwise the call is ignored.
	 *
	 * For more general comments or comments across multiple objects
	 * @see addGeneralComment
	 *
	 * @param string $objectUuid  the uuid of the main object that the comment refers to
	 * @param string $comment  the comment to be added
	 * @return string  the uuid of the created log entry
	 */
	public function addComment($objectUuid, $comment);

	/**
	 * Add a general comment to the change log. This is not restricted to a particular
	 * part of neon and can be used for adding comments on e.g. application forms. It
	 * can also be associated with a set of Daedalus or other objects. The comment is added
	 * regardless of whether or not the associated objects have a change log or not.
	 *
	 * @param string $module  the module in the system e.g. phoebe
	 * @param string $classType  the classType or some reference within that module
	 * @param string $comment  the comment you want to add
	 * @param uui $objectUuid  the principal object the comment is associated with if applicable
	 * @param array $associateObjectUuids  any associated objects that are related to the principal one.
	 * @return string  the uuid of the created log entry
	 */
	public function addGeneralComment($module, $classType, $comment, $objectUuid=null, array $associateObjectUuids=[]);

	/**
	 * Get an object's value at a particular log point.
	 *
	 * @param uuid $objectUuid  the uuid of the object you want to revert
	 * @param uuid $logRestoreUuid  the uuid of the log entry you want to restore to.
	 * @throws \InvalidArgumentException  if the $objectUuid doesn't match that
	 *   of the log entry point's object uuid
	 * @throws \RuntimeException  if the restore point is more than Iterator::MAX_LENGTH
	 *   items away from the current change log point.
	 */
	public function getObjectAtLogPoint($objectUuid, $logRestoreUuid);

	/**
	 * Restore an object to that at a particular log point. If the particular
	 * point is the destruction of an object, it will be destroyed. Otherwise it will
	 * be restored and if soft deleted, undeleted.
	 *
	 * @param uuid $objectUuid  the uuid of the object you want to revert
	 * @param uuid $logRestoreUuid  the uuid of the log entry you want to restore to.
	 * @throws \InvalidArgumentException  if the $objectUuid doesn't match that
	 *   of the log entry point's object uuid
	 * @throws \RuntimeException  if the restore point is more than Iterator::MAX_LENGTH
	 *   items away from the current change log point.
	 */
	public function restoreObjectToLogPoint($objectUuid, $logRestoreUuid);
}
