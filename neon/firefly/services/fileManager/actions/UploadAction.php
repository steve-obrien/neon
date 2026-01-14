<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\firefly\services\fileManager\actions;

use neon\firefly\services\plupload\Receiver;
use yii\base\Action;

/**
 * Handles receiving a file via plupload
 * @return array
 */
class UploadAction extends Action
{
	public function run()
	{
		$r = new Receiver(neon()->request);
		$result = $r->receiveToFireflyFileManager('file');
		return $result;
	}
}