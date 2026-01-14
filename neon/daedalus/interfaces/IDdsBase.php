<?php

namespace neon\daedalus\interfaces;

interface IDdsBase
{

	/** ---------- Utility Methods ---------- **/

	/**
	 * Canonicalise a candidate reference. References need to be converted into a standard
	 * format that is acceptable by the database for table names and fields. To
	 * save converting these every time a class type or member reference is provided,
	 * Daedalus forces the change during the definition. This forcing happens
	 * on @see addClass and @see addMember. You can convert the reference here too.
	 *
	 * @param string &reference  the reference to be canonicalised.
	 *   References can be [a-z_0-9] only. Spaces are converted to _ and multiple
	 *   spaces are removed. Other characters are removed.
	 * @return string  the canonicalised reference.
	 */
	public function canonicalise($reference);

	/**
	 * get the available storage types in the system
	 *
	 * @return an array of
	 *   ['storage_ref', 'label', 'type', 'description']
	 * in alphabetical order of label
	 */
	public function listStorageTypes();

	/**
	 * a database formatted string of now
	 * @return string
	 */
	public function now();

}