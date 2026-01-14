<?php

namespace neon\daedalus\interfaces;

interface IDdsClassManagement extends IDdsBase
{

	/** ---------- Utility Methods ---------- **/

	/**
	 * Determine if a class type is unique within the system. Note the candidate
	 * will be canonicalised before checking to see if it is unique or not
	 * @param string $candidate  the class type you want to check
	 * @return boolean  whether or not it is unique
	 */
	public function isClassTypeUnique($candidate);

	/**
	 * Determine if a member reference is unique within a class. Note the candidate
	 * will be canonicalised before checking to see if it is unique or not
	 * @param string $classType  the class you're checking the reference on
	 * @param string $candidate  the member ref you want to check
	 * @return boolean  whether or not it is unique
	 * @throws \InvalidArgumentException  if the classType cannot be found
	 */
	public function isMemberRefUnique($classType, $candidate);


	/** ---------- Class Manipulations ---------- **/

	/**
	 * list all of the classes defined in Daedalus
	 *
	 * @param string $module  restrict this to all classes in a particular neon
	 *   module - e.g. restrict to 'cms' data only. This will still include any
	 *   classes that have not been restricted to a module. If null then show
	 *   all classes
	 * @param integer &$total  the total number available returned after the call
	 * @param boolean $includeDeleted  if set to true will return all
	 *   classes that have been soft deleted
	 * @param integer $start  the starting position for the list
	 * @param integer $length  the number you want returned.
	 * @param string|array $orderBy  a string of field [DIR] or array of
	 *   'field' => 'SORT_DIR' where DIR is ASC or DESC
	 *
	 * @return array of
	 *   []=>['label', 'class_type', 'description', 'module', 'change_log', 'count_total',
	 *     'count_current', 'count_deleted', ['deleted']] - deleted is set if you've
	 *   set $includeDeleted to true
	 */
	public function listClasses($module, &$total, $includeDeleted=false, $start=0, $length=100, $orderBy='label');

	/**
	 * Return the number of defined classes
	 *
	 * @param null $module - filter by module null (default) for all
	 * @param boolean $includeDeleted default false
	 * @return integer
	 */
	public function countClasses($module=null, $includeDeleted=false);

	/**
	 * create a new class
	 *
	 * @param string(50) &classType  a type for this class e.g. speaker
	 *   this is used to define the table name· Class types are unique
	 *   across the application. Once a class type has been specified
	 *   it cannot be changed as this will impact on application code.
	 *
	 *   ClassType can be [a-z_0-9] only and will be canonicalised accordingly
	 *
	 * @param string(50) $module  the neon module that is defining the
	 *   class - this is so we can filter different users of Daedalus
	 *   and the corresponding classes - e.g. CMS classes distinguished
	 *   from shopping classes. If it is available across modules then
	 *   set this to blank and it will appear in all listings regardless
	 *   of module setting
	 *
	 * @return string(50) $classType  the canonicalised type of the class
	 * @throw InvalidArgumentException  if the class type already exists
	 * @throw RunTimeException  if the class wouldn't save
	 */
	public function addClass(&$classType, $module);

	/**
	 * Get the details of a class
	 *
	 * NOTE: class definitions are now managed by Phoebe so will return blank
	 * values. @see IPhoebeType::getClass() to get hold of the definition of
	 * the whole class
	 *
	 * @param string(50) $classType  the type of the class
	 * @param boolean $includeMembers  if true then include the members
	 *   otherwise 	@see listMembers to get the members
	 * @return null | array of
	 *   class_type, label, description, module, deleted, count_total,
	 *   count_deleted, members
	 */
	public function getClass($classType, $includeMembers=false);

	/**
	 * Edit a class
	 * NOTE: you cannot change the class type as this will break code
	 * relying on its value. Also the definition is now stored on phoebe
	 * @param string(50) $classType  the type of the class
	 * @param array $changes  the changes to be made to the class from
	 *   label - string(300) - a label for the class
	 *   description - string(1000) - a description for the class
	 *   change_log - boolean - set to true to record object data changes in
	 *     the change log. Set to false to not record the objects
	 */
	public function editClass($classType, $changes);

	/**
	 * Delete a class - this marks it as deleted and it
	 * can be undeleted if required. The corresponding table for the class will
	 * be removed and it will not be returned in any listing unless the
	 * $includeDeleted is set to true.
	 *
	 * @param string(50) $classType  the type of the class
	 */
	public function deleteClass($classType);

	/**
	 * Undelete a class
	 *
	 * @param string(50) $classType  the type of the class
	 */
	public function undeleteClass($classType);

	/**
	 * Destroy a class - this will remove it if all of the data
	 * has already been removed from the database. Otherwise it
	 * will fail.
	 *
	 * @param string(50) $classType  the type of the class
	 */
	public function destroyClass($classType);


	/** ---------- Member Manipulations(!!) ---------- **/

	/**
	 * List the members associated with a class
	 *
	 * @param string(50) $classType  the type of the class
	 * @param boolean $includeDeleted  whether or not to include deleted members
	 * @param string $keyBy  if set then return the rows with the
	 *   key set to the column named
	 *
	 * @return array of
	 *   'keyBy' => [
	 *      id, class_type, member_ref, data_type_ref, label, description,
	 *      definition, choices, 'link_class', [deleted]
	 *   ]
	 *   where deleted is given if $includeDeleted is true and keyBy is @see $keyBy
	 *   Empty array is returned if there are no results
	 */
	public function listMembers($classType, $includeDeleted=false, $keyBy='member_ref');

	/**
	 * Create a new member for a class
	 *
	 * @param string(50) $classType  the class this is created one
	 * @param string(50) &$memberRef  a reference for this member - this will
	 *   become the field name for the member in any data. It is unique to the
	 *   class but not across the application
	 *
	 *   memberRef can be [a-z_0-9] only and will be adjusted accordingly
	 *
	 * @param string(50) $dataTypeRef  the data type for the member
	 * @param string(300) $label  the user facing label for the member (e.g. Date of Birth)
	 * @param string(1000) $description  a description for the member
	 * @param array $additional  This is for any additional information that should
	 *   be stored for a particular datatype and depends on that datatype. Defined values
	 *   are for the following datatypes:
	 *     'choices' => array - provide the key value pairs here as 'key'=>'value' then when accessing an
	 *       object the value will be returned as ['key'=>the key, 'value'=>the value]
	 *     'link_class' => string - the classType of the linked data
	 *
	 * @return string  the canonicalised memberRef
	 * @throw InvalidArgumentException  if the member already exists or the class doesn't
	 * @throw RunTimeException  if the member could not be added
	 */
	public function addMember($classType, &$memberRef, $dataTypeRef, $label, $description, $additional=null);

	/**
	 * Get the details of a member
	 *
	 * @param string $classType the class type of the class the member belongs to
	 * @param string $memberRef the unique reference of the member within this class
	 * @param array $fields  if set then only return those fields
	 * @return null|array of or subset of [
	 *   id, class_type, member_ref, data_type_ref, label, description,
	 *   choices, link_class, deleted]
	 */
	public function getMember($classType, $memberRef, $fields=[]);

	/**
	 * Edit a member's details
	 *
	 * note - you cannot edit a members memberRef as this will affect app code
	 * relying on its value
	 *
	 * @param string $classType the class type of the class the member belongs to
	 * @param string $memberRef the unique reference of the member within this class
	 *
	 * @param array $changes  an array of changes to be made from
	 *   [label, description, additional]
	 */
	public function editMember($classType, $memberRef, $changes);

	/**
	 * Delete a member  this soft deletes the member and it can be restored
	 * if required @see undeleteMember. The member will disappear from the
	 * view table and from any lists unless $includeDeleted is true
	 *
	 * @param string $classType the class type of the class the member belongs to
	 * @param string $memberRef the unique reference of the member within this class
	 * @return boolean whether the member record successfully soft deleted the record
	 */
	public function deleteMember($classType, $memberRef);

	/**
	 * Undelete a member
	 *
	 * @param string $classType the class type of the class the member belongs to
	 * @param string $memberRef the unique reference of the member within this class
	 * @return boolean whether the member record successfully undeleted the record
	 */
	public function undeleteMember($classType, $memberRef);

	/**
	 * Destroy a member  this permanently removes the member. All data in that field
	 * will be destroyed
	 *
	 * @param string $classType the class type of the class the member belongs to
	 * @param string $memberRef the unique reference of the member within this class
	 *
	 * @return boolean whether the member record successfully destroyed the record
	 */
	public function destroyMember($classType, $memberRef);


	/** ---------- Class Map Manipulations(!!) ---------- **/

	/**
	 * List all of the classes with a map field defined in Daedalus
	 * of class type to class label
	 * @return array  [key => label]
	 */
	public function getMappableClasses();

	/**
	 * Get the map field for the provided class
	 * @param string $class
	 * @return string  the field
	 */
	public function getClassMapField($class);

	/**
	 * Set this member as the map field for the class
	 * @param string $classType  the class type of the class the member belongs to
	 * @param string $memberRef  the member reference key within this class
	 */
	public function setMemberAsMapField($classType, $memberRef);

}
