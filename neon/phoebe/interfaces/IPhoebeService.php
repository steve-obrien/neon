<?php

namespace neon\phoebe\interfaces;

/**
 * Phoebe main interface.
 *
 * PhoebeTypes are the different types of component that Phoebe can support. Each
 * type will correspond to a particular builder and adapter within the system.
 *
 * To use phoebe, get the appropriate type first of all and then use that to
 * get hold of its classes and related objects
 */
interface IPhoebeService
{
	/**
	 * Get the available types that Phoebe understands
	 *
	 * @return array  an array of types and descriptions
	 *   [key] => [
	 *     'key' - the phoebe type key,
	 *     'label' - a label for the type
	 *     'description' - a description about the type
	 *     'status' => one of 'AVAILABLE', 'IN_DEVELOPMENT', 'WISHLIST'
	 *   ],...
	 *
	 *   e.g. database, application forms, client forms,
	 *   page templates, solution modules etc
	 * Currently only database and application forms
	 */
	public function listPhoebeTypes();

	/**
	 * Get hold of a phoebe type interface
	 *
	 * @param string $phoebeTypeKey  the key for the phoebeType required
	 * @return null | \neon\phoebe\interfaces\IPhoebeType   The result is
	 *   null or IPhoebeType depending on the state of the type. Items that are
	 *   AVAILABLE or IN_DEVELOPMENT can be created. WISHLISTs items will return null
	 */
	public function getPhoebeType($phoebeTypeKey);

}