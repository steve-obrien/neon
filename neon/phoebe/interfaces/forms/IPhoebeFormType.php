<?php

namespace neon\phoebe\interfaces\forms;
use neon\phoebe\interfaces\IPhoebeType;

/**
 * AppForm type interface.
 */
interface IPhoebeFormType extends IPhoebeType
{
	/**
	 * Create a new IPhoebeFormObject without saving it to the database
	 * @param string $classType  the class type of the object
	 * @return \neon\phoebe\interfaces\IPhoebeFormObject
	 * @throw \RuntimeException  if the class type is invalid or doesn't exist
	 */
	public function createStubObject($classType);
}
