<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\firefly\services\fileManager\actions;

use yii\base\Action;
use neon\firefly\services\fileManager\FileNotFoundException;

/**
 * Get file manager managed meta data for a file given by id
 *
 * GET http://localhost/firefly/api/file/meta?id=MkPoda3CcE_d8Emb4KBRm0&amp; HTTP/1.1
 * Host: localhost
 * Content-Type: application/json
 *
 * @throws FileNotFoundException if no file exists with the specified id
 * @return array
 */
class MetaAction extends Action
{
	public function run()
	{
		$id = neon()->request->get('id');
		$metaIds = is_array($id) ? $id : [$id];
		$results = [];
		foreach ($metaIds as $mid) {
			$meta = neon()->firefly->fileManager->getMeta($mid);
			if (empty($meta)) {
				$results[] = [
					"uuid"=>$mid,
					"name"=>"No file exists with id '$mid'"
				];
			} else {
				$results[] = $meta;
			}
		}
		return is_array($id) ? $results : $results[0];
	}
}
