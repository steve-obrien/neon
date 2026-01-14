<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\firefly\services\fileManager\actions;

use neon\firefly\services\fileManager\FileNotFoundException;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Download a file
 *
 * @throws FileNotFoundException
 * @return $this|\neon\core\web\Response
 */
class DownloadAction extends Action
{
	public function run()
	{
		$id = neon()->request->get('id');
		try {
			return neon()->firefly->fileManager->sendFile($id, true);
		} catch (FileNotFoundException $e) {
			throw new NotFoundHttpException('File not found');
		}
	}
}