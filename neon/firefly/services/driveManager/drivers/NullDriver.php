<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 17:24
 * @package neon
 */

namespace neon\firefly\services\driveManager\drivers;

use \yii\base\Component;
use \League\Flysystem\Adapter\NullAdapter;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;

class NullDriver extends Component implements IDriverConfig
{
	/**
	 * @return NullAdapter
	 */
	public function getAdapter()
	{
		return new NullAdapter();
	}
}