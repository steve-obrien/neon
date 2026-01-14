<?php

namespace neon\phoebe\interfaces\daedalus;
use neon\phoebe\interfaces\IPhoebeType;

/**
 * Daedalus type interface.
 * Additional methods supported by the Daedalus phoebe type
 */
interface IPhoebeDaedalusType extends IPhoebeType
{
	/**
	 * Create or edit a Daedalus class from it's definition
	 *
	 * @param string $class  the name of the table to create / edit
	 * @param array $definition  the phoebe form definition for the class
	 *   Members are managed through the form fields parameter
	 * @param string $module  the module that requested to save a definition
	 *   e.g. phoebe or a test suite.
	 * @return neon\phoebe\interfaces\IPhoebeClass
	 */
	public function saveClassDefinition($class, $definition, $module);
}
