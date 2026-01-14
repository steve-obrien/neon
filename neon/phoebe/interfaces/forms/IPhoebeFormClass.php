<?php

namespace neon\phoebe\interfaces\forms;
use neon\phoebe\interfaces\IPhoebeClass;

/**
 * AppForm class interface.
 */
interface IPhoebeFormClass extends IPhoebeClass
{
	/**
	 * Get the set of required data sources for a particular form
	 * @return array  [key] => ['key'=>$key, 'class_type'=>$classType]
	 */
	public function getRequiredDataSources();

	/**
	 * Get the phoebe key for a particular class type in the
	 * definition (appForms only - TODO NJ - 20/12/18 - create separate appForm interface)
	 * Note - if there is more than one class component with the same class type,
	 * this will return the last one.
	 * @param $ddsClassTypes  an array of daedalus class types as [n]=>classtype
	 * @return array  an array of ['classtype'=>null|string] where the phoebe key for the
	 *   class type is set if found otherwise left null
	 */
	public function findPhoebeKeysForClassTypesInDefinition($ddsClassTypes);
}
