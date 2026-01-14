<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\basic;

use neon\phoebe\services\adapters\common\PhoebeTypeBase;

/**
 * Basic phoebe type with no additional functionality
 */
class PhoebeType extends PhoebeTypeBase
{
	public function __construct($config=[])
	{
		$path = '\neon\phoebe\services\adapters\basic';
		$config['phoebeType'] = 'basic';
		$config['phoebeClass'] = $path.'\PhoebeClass';
		$config['phoebeObject'] = $path.'\PhoebeObject';

		parent::__construct($config);
	}
}