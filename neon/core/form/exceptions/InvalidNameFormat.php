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

class InvalidNameFormat extends Exception
{
	/**
	 * Constructor.
	 * @inheritdoc
	 */
	public function __construct($name)
	{
		$message = "Attribute name must contain word characters only [A-Za-z0-9_-]+ characters only no spaces. You passed in '$name'";
		parent::__construct($message);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Invalid name format ';
	}
}