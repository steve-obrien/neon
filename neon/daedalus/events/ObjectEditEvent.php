<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 11/05/2020
 * @package neon
 */
namespace neon\daedalus\events;

use yii\base\Event;

class ObjectEditEvent extends Event
{
	/**
	 * @var string the class type that triggered the edit event
	 */
	public $classType;
	/**
	 * @var array the current dds object (after editing)
	 */
	public $object;
	/**
	 * @var array the original values (before editing)
	 */
	public $originalValues;
}
