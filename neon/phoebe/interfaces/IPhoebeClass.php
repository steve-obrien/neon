<?php

namespace neon\phoebe\interfaces;

/**
 * Phoebe class interface.
 *
 * A phoebe class is the definition of a particular phoebe type. E.g. for
 * databases, a phoebe class would be the table form definition. For an
 * application form, it would be the set of form elements and Daedalus classes
 * associated with it.
 *
 * @see IPhoebeType::getIPhoebeClass for getting hold of an appropriate class interface
 *
 * This manages the creation, editing etc of particular phoebe class definitions
 * Class attributes include (@see neon\phoebe\services\adapters\common\models\PhoebeClass
 *
 * @property string $phoebe_type - the phoebe type e.g. 'daedalus'
 * @property string $class_type - the class type e.g. table name
 * @property string $module - a module to further classify the classes
 * @property integer $version - what version of class
 * @property integer $version_locked - whether or not objects are locked to
 *   their creation version
 * @property integer $volatile - whether or not objects always get their data from
 *   the database or from the phoebe saved representation
 * @property string $label - a user facing label for the class
 * @property string $description - a user facing description for the class
 * @property boolean $object_history - whether or not object_history is required
 *   note: some adapters will ignore this (e.g. daedalus)
 * @property datetime $created - when the object was created
 * @property datetime $updated - when the object was last updated
 * @property boolean $deleted - if the object has been deleted
 * @property array $definition - the definition of the class
 * @property string $serialised_definition - serialised version of the definition
 *
 * Different Phoebe Types (e.g. daedalus) may provide additional information or
 * ignore / override some of the settings
 */
interface IPhoebeClass extends IPhoebeBase
{
	/** ----------------------------------------- **/
	/** -------- Class Definition Methods ------- **/

	/**
	 * Get the class in a form definition format ready to be placed into a neon\core\form
	 * and displayed to a user
	 * @param array $fields  if available for the phoebeType, restrict the form to
	 *   the set of fields provided.
	 * @return array  a form definition
	 */
	public function getClassFormDefinition(array $fields=[]);

	/**
	 * Get the class definition in a suitable format to present to a class builder app
	 * @return array  a builder definition
	 */
	public function getClassBuilderDefinition();

	/**
	 * Get the definition of objects in a format ready to be placed into a neon\core\grid.
	 * The details of what columns are provided depends upon the phoebe type. For example
	 * Daedalus objects are managed instead using getClassFormDefinition so return nothing.
	 *
	 * Application forms cannot easily be drawn into a grid as they have sub-forms which
	 * can't be recursively represented. These would need to either define which fields
	 * should be shown in the grid or use the default one
	 *
	 * @param array $additionalColumns  add additional columns to the basic ones provided
	 *   For example, for default classes you can request the data and deleted columns
	 *   by passing in ['data', 'deleted']
	 * @return array  a grid definition
	 */
	public function getObjectGridDefinition($additionalColumns = []);

	/**
	 * Edit the details of a class
	 * @param array $changes  a set of key value pairs. Allowed values for the base
	 *   class are listed below. Additional parameters may be allowed by other adapters
	 *
	 *   'label' - a user facing label for the class
	 *   'description' - a user facing description for the class
	 *   'version' - the version of the class,
	 *   'version_locked' - if objects should be locked to the version used when
	 *      they were created (or the latest if not available)
	 *   'object_history' - whether or not history should be kept for objects of this class
	 *   'volatile' - whether or not the object get its data directly from Daedalus
	 *      or uses what's been saved in Phoebe
	 *   'definition' - the type specific definition of the class
	 *
	 * @return boolean | array   returns true if there were changes that needed saving,
	 *   false if there were none, and an array of errors if something went wrong. This
	 *   should help with questions of whether or not to create a migration
	 */
	public function editClass($changes);

	/**
	 * Soft delete a class. Makes all instances unavailable
	 * @return boolean | array  returns true if the class was successfully deleted and
	 *   an array of errors it it wasn't
	 */
	public function deleteClass();

	/**
	 * Undelete a softly deleted class
	 * @return boolean | array  returns true if the class was successfully undeleted and
	 *   an array of errors it it wasn't
	 */
	public function undeleteClass();

	/**
	 * Destroy a class (and all associated objects)
	 * @return int  the number of rows destroyed
	 */
	public function destroyClass();


	/** ----------------------------------------- **/
	/** -------- Applying Class Overrides ------- **/

	/**
	 * Apply an override definition to the class. The override must be valid for this class
	 * otherwise it will be ignored
	 * @param uuid $overrideUuid  a PhoebeClassOverride identifier
	 */
	public function applyClassOverrideByUuid($overrideUuid);

	/**
	 * Apply an override definition to the class.
	 * @param array $override  a set of definition overrides
	 */
	public function applyClassOverrideByDefinition($override);

	/**
	 * Get the set of overrides that will be applied to the class
	 */
	public function getOverrides();


	/** -------------------------------------------------- **/
	/** -------- Class Override Definition Methods ------- **/

	/**
	 * Get the set of overrides available for this class type and version
	 * @return array  array of available overrides as uuid => label
	 */
	public function listOverrides();

	/**
	 * Find an override. If no $filters are provided, then this will search
	 * for the currently applicable override if there is one. This is defined
	 * as the override most recently active from with no selector.
	 *
	 * @param array $filters  any or none of
	 *   'selector' - a selector which must be found on the override
	 *   'date' - a date used to select which override is active at that date
	 * @return null|array  null if not found or the override parameters
	 */
	public function findOverride($filters=[]);

	/**
	 * Get an override by its uuid. This will return soft deleted overrides.
	 * The override must belong to this class.
	 * @param uuid $uuid
	 * @return null|array  returns null if not found or the override parameters of
	 *   'uuid', 'label', 'selector', 'is_active',
	 *   'active_from', 'active_to', 'overrides'
	 */
	public function getOverride($uuid);

	/**
	 * Create a new class override based on the current class
	 * @param string $label  a user facing label to distinguish this override
	 * @param string $selector  optional selector to further classify the override
	 * @param datetime $activeFrom  when this override is active from - defaults to creation
	 * @param datetime $activeTo  when this override is active until. If null then
	 *   continues to be an override until a newer one exists
	 * @param boolean $isActive  whether or not the override is active
	 * @return uuid|errors  returns the uuid of the created override or the array of
	 *   errors if not possible
	 */
	public function addOverride($label, $selector='', $activeFrom=null, $activeTo=null, $isActive=true);

	/**
	 * Edit the properties of an override
	 * @param uuid $uuid  the override you want to make changes to
	 * @param array $changes  the set of changes you want to make. Allowed changes are
	 *   'label' - a user facing label to distinguish this override
	 *   'selector' - optional selector to further classify the override
	 *   'active_from' - when this override is active from - defaults to creation
	 *   'active_to' - when this override is active until or null for no end
	 *   'is_active' - whether or not the override is active
	 *   'overrides' - an array of override parameters that make sense to the phoebe type
	 * @return boolean | array   returns true if everything saved ok
	 *   and an array of errors if something went wrong.
	 */
	public function editOverride($uuid, $changes);

	/**
	 * Soft delete an override. Soft deleted overrides can be applied to objects
	 * that were saved while the override existed but not to any new objects
	 * @param uuid $uuid  the override you want to soft delete
	 */
	public function deleteOverride($uuid);

	/**
	 * Undelete a soft deleted override.
	 * @param uuid $uuid  the override you want to undelete
	 */
	public function undeleteOverride($uuid);

	/**
	 * Destroy an override. This will be removed from the database and any objects
	 * that were saved with this override applied will lose the override information
	 * @param uuid $uuid
	 */
	public function destroyOverride($uuid);

}
