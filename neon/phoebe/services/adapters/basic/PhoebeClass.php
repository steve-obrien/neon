<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\basic;

use neon\phoebe\services\adapters\common\PhoebeClassBase;

/**
 * Provides a basic phoebe class with no additional functionality for
 * additional use cases
 */
class PhoebeClass extends PhoebeClassBase
{
	/**
	 * @inheritdoc
	 */
	public function getClassFormDefinition(array $fields=[])
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function getClassBuilderDefinition()
	{
		return [];
	}
}