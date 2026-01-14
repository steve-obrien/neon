<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 27/04/2017
 * @package neon
 */

/**
 * Current working spec - take care of generic LADDERU functions
 * Currently not used
 * Interface IResource
 */
interface IResource
{
	public function resourceList($filters);

	public function resourceAdd($data);

	public function resourceDelete($uuid);

	public function resourceDestroy($uuid);

	public function resourceEdit();

	public function resourceRead($uuid);

	public function resourceUnDelete($uuid);
}