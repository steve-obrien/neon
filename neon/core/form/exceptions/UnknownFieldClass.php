<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/07/2018
 * @package neon
 */

namespace neon\core\form\exceptions;


use yii\base\Exception;

class UnknownFieldClass extends Exception
{
	/**
	 * Constructor.
	 * @inheritdoc
	 */
	public function __construct($class)
	{
		$message = sprintf('The form field class specified of "%s" does not exist', $class);
		parent::__construct($message);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Unknown form field class';
	}
}