<?php

namespace neon\phoebe\interfaces;

/**
 * Phoebe base interface.
 *
 * Provides some commone methods across all phoebe objects
 */
interface IPhoebeBase
{
	/**
	 * get the object attributes as key=>value pairs
	 */
	public function toArray();

	/**
	 * canonicalise a reference so that it follows a set pattern
	 *
	 * This is to prevent problems with queries where the field name may be
	 * illegitimate and also to help prevent SQL injection in raw queries
	 *
	 * @param string $ref  the uncanonicalised reference
	 * @return string  the canonicalised one
	 */
	public function canonicaliseRef($ref);

}