<?php

namespace neon\daedalus\interfaces;

interface IDdsDataTypeManagement extends IDdsBase
{
	/** ---------- Utility Methods ---------- **/

	/**
	 * determine if a data type ref is unique within the system. Note the candidate
	 * will be canonicalised before checking to see if it is unique or not
	 * (otherwise it will give you a potentially incorrect result)
	 * @param string $candidate  the data type you want to check
	 * @return boolean  whether or not it is unique
	 */
	public function isDataTypeUnique($candidate);


	/** ---------- Data Type Methods ---------- **/

	/**
	 * get the available datatypes in the system
	 *
	 * @param type $includeDeleted  if true then include
	 *   any deleted datatyes
	 * @return an array data types
	 */
	public function listDataTypes($includeDeleted=false);

	/**
	 * Create a datatype.
	 *
	 * @param string &$dataTypeRef  a unique reference to this datatype e.g. datepast,
	 *   article etc.
	 *
	 * 	 DataTypeRef can be [a-z_0-9] only and will be adjusted accordingly
	 *
	 * @param string $label  a user facing label for the data type
	 * @param string $description  a description of what the data type means
	 * @param JSON $definition  any  definition placed on the datatype as
	 *   understood by the UI as JSON. These are not enforced at this level
	 *   e.g. {'size':50}, or {'date':'future'}
	 * @param string $storageRef  the storage type for this datatype
	 * @return string $dataTypeRef  the canonicalised dataTypeRef
	 */
	public function addDataType(&$dataTypeRef, $label, $description, $definition, $storageRef);

	/**
	 * get the details about a datatype
	 *
	 * @param  string  $dataTypeRef
	 * @return array of
	 *   ['data_type_ref', 'label', 'description', 'definition', 'storage_ref']
	 */
	public function getDataType($dataTypeRef);

	/**
	 * edit a datatype
	 *
	 * NB you cannot change the data_type_ref or storage_ref of a datatype - this
	 * is to protect code and data within the system.
	 *
	 * @param string $dataTypeRef
	 * @param array $changes  array of any of
	 *   ['label', 'description', 'definition']
	 */
	public function editDataType($dataTypeRef, $changes);

	/**
	 * soft delete a datatype.
	 * The data type will become unavailable in listings (unless $includeDeleted
	 * is set to true) but will not affect members that are already using that
	 * datatype.
	 * @param type $dataTypeRef
	 */
	public function deleteDataType($dataTypeRef);

	/**
	 * undelete a data type.
	 * @param type $dataTypeRef
	 */
	public function undeleteDataType($dataTypeRef);

	/**
	 * Destroy a datatype
	 *
	 * This will fail if any members exist using the datatype. They will need to
	 * be managed first before this can be done.
	 * @param type $dataTypeRef
	 */
	public function destroyDataType($dataTypeRef);

}