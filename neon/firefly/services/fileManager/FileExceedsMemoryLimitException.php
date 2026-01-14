<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\firefly\services\fileManager;

class FileExceedsMemoryLimitException extends \Exception
{
	public function __construct($message = "", $code = 0, \Exception $previous = null)
	{
		if ($message == '') {
			$message = 'The file exceeds php memory limit! Use streams to perform operations on this file.';
		}
		parent::__construct($message, $code, $previous);
	}
}