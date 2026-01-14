<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 04/11/2018
 * @package neon
 */

namespace neon\dev\debug\panels;


class EventPanel extends \yii\debug\panels\EventPanel
{
	/**
	 * @inheritdoc
	 */
	public function isEnabled()
	{
		return true;
	}
}