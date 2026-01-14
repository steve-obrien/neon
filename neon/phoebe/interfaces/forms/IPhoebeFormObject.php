<?php

namespace neon\phoebe\interfaces\forms;
use neon\phoebe\interfaces\IPhoebeObject;

/**
 * AppForm class interface.
 *
 * TODO - NJ - 20181217 - there is a confusion between appForms and Daedalus
 * methods related to forms here. There is something wrong with the class
 * hierarchy that needs to be separated out. Fix it. Maybe need AppForm as
 * well as Form???
 *
 * i.e. setDataSources applies to saving of forms for both Dds and AppForms
 * updateFromDds & initialiseFromDds only applies to appForms.
 */
interface IPhoebeFormObject extends IPhoebeObject
{
	/**
	 * Set data sources on a particular form object
	 * @param array $sources  an array of key=>uuid pairs where the key is as
	 *   defined on the application form and the uuid is the required objects
	 *   uuid.
	 */
	public function setDataSources($sources);

	/**
	 * Get the form object to update its data from any associated Daedalus tables
	 * @param array $class  the class definition
	 */
	public function updateFromDds();

	/**
	 * Initialise the form object from the supplied data objects
	 * @param array $dataObjects  an array of [key=>UUID, ...]  where the
	 *   key identifies the Daedalus class within the form definition and
	 *   the UUID is the Daedalus object's UUID that the form will be
	 *   initialised with.
	 */
	public function initialiseFromDds($dataObjects);
}
