<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 28/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\firefly\controllers\api;

use neon\core\helpers\Arr;
use neon\firefly\services\FireflySecurityPolicy;
use neon\firefly\services\plupload\Receiver;
use neon\core\web\ApiController;
use neon\firefly\services\mediaManager\models\Media;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Class MediaController
 *
 * @package neon\firefly\controllers
 */
class MediaController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = neon()->firefly->fileAccessRules;
		return empty($rules) ? parent::rules() : $rules;
	}

	public function beforeAction($action)
	{
		if ( FireflySecurityPolicy::isApiDisabled()) {
				// firefly API kill-switch!
				//
				// given the comments below it was clearly known in 2018 this API endpoint was likely
				// a security vulnerability & yet *nothing* has been done about it in nearly 10 years 🤦‍♂️
				//
				// if there was time it would make sense to go through and secure each, but some neon
				// apps do not even use the API at all!
				throw new HttpException(404, 'Not found');
			}

			return parent::beforeAction($action);

		// TODO 20180901 NJ - securely set root paths
		//
		// need to be able to define the root path through a method that
		// can be called at this point and determined through configuration
		// so that all media uploads can be controlled as to their root paths
		// without any changes to their code or client side knowledge of
		// where these are set.
		//
		// problem is - how can this know how to determine the actual root path?
		// as the media browser api doesn't require appropriate information.
		//
		// Fundamentally this api is insecure for any application with members
		// uploading their own private information for particular areas.
		//
		// A call to actionList for example, cannot then just give access to all of the
		// files by default. This would break confidentiality across other members
		// of the site.
		//
		// Also the media controller is only available if people are logged in
		// so wouldn't work for any forms that are for the public non logged in use
		// at least not currently
		//
		//  >> if permission are done per folder at the user level then guest users
		//  >> could be granted access to folders
		//
		// Requiring the caller to pass in the current path would not be sufficient
		// either as that could easily be hacked.
		//
		// Hmmmmm - how can this be set in such a way that it cannot be hacked
		// without the caller passing in information that can be substituted
		// etc etc???
		//
		// cannot use the referer(sic) header as that is often blocked and can
		// be hacked.
		//
		// Could require the niceid key for the page the image object is on? If the key isn't
		// passed in the media browser or doesn't correspond to a real
		// niceid it returns nothing. However, someone observing nice ids could
		// theoretically access other pages if they used those ids ... hmmmmm
		//
		// and what about ajaxed in forms that can be used in more than one place
		// but would require different information depending on location.
		//
		// Aargh - this is not straight forward at all.
		//
		//
	}

	public function verbs()
	{
		return [
			'rename' => ['POST'],
			'create-directory' => ['POST'],
			'delete' => ['DELETE'],
		];
	}

	public $enableCsrfValidation = false;

	/**
	 * List directory contents
	 *
	 * @param string $path GET variable 'path' e.g. /my/folder
	 * @return array
	 */
	public function actionList($path = '', $deleted = 0)
	{
		$items = neon()->firefly->mediaManager->listContents($path, $deleted);
		return ['data' => [
			'path' => $path,
			'items' => $items
		]];
	}

	/**
	 * API endpoint for plupload
	 *
	 * @param string $path - Optional path get parameter will defaults to '/'
	 * @return array
	 */
	public function actionUpload()
	{
		//sleep(1); // can uncomment to simulate (roughly) a large upload
		$r = new Receiver(neon()->request);
		$path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '/';
		return $r->receiveToFirefly('file', $path);
	}

	/**
	 * Upload a file from a url
	 *
	 * @return array
	 * @throws \neon\firefly\services\mediaManager\InvalidDirectoryException
	 * @throws \yii\web\HttpException
	 */
	public function actionUploadFromUrl()
	{
		if (FireflySecurityPolicy::isApiUploadFromUrlDisabled()) {
			throw new HttpException(404, 'Not found');
		}
		$url = request()->post('url');
		$path = request()->post('path');
		$basename = basename($url);
		FireflySecurityPolicy::assertWhitelistedExtension($basename);
		$id = neon()->firefly->saveFromUrl($url, $basename);
		return neon()->firefly->mediaManager->addFile($id, $path);
	}

	/**
	 * Transform an image based on the editor settings passed.
	 * @return array
	 */
	public function actionTransform()
	{
		$path = neon()->request->getBodyParam('path');
		// $transform = neon()->request->post('transform');
		$transform = neon()->request->getBodyParam('transform');
		// information of the current file
		$sourceMeta = neon()->firefly->getMeta($transform['sourceImage']);
		$transform['rotate'] =  Arr::get($transform, 'crop.rotate', false);
		$width = Arr::get($transform, 'crop.width', false);
		$height = Arr::get($transform, 'crop.height', false);

		$scaleX = Arr::get($transform, 'crop.scaleX', false);
		if ($scaleX == -1) {
			$transform['flip'] = 'h';
		}
		$scaleY = Arr::get($transform, 'crop.scaleY', false);
		if ($scaleY == -1) {
			$transform['flip'] = 'v';
		}
		
		$image = neon()->firefly->imageManager->process($sourceMeta['id'], $transform);
		// get a new name for the cropped file
		$nameInfo = pathinfo($sourceMeta['name']);
		
		$newName = $nameInfo['filename'].".{$width}x$height.".(isset($nameInfo['extension']) ? $nameInfo['extension'] : '');
		// save the new cropped file in the file manager
		
		$id = neon()->firefly->fileManager->save($image, $newName, ['transform' => $transform]);
		
		// Add the new cropped file to the media manager so it is visible
		$mediaFile = neon()->firefly->mediaManager->addFile($id, $path);

		return $mediaFile;
	}

	/**
	 * Creates a new directory
	 *
	 * @throws
	 * - \neon\firefly\services\mediaManager\DirectoryExistsException - http status 409 conflict if the directory already exists
	 * - \neon\firefly\services\mediaManager\InvalidDirectoryException - http status 400 invalid directory (path) parameter
	 */
	public function actionCreateDirectory()
	{
		$directory = neon()->request->getBodyParam('path');
		return neon()->firefly->mediaManager->createDirectory($directory);
	}

	/**
	 * Rename a file or folder
	 *
	 * @param string $id the id of the item whose name is to change
	 * @param string $name the new name of the item
	 * @return boolean
	 */
	public function actionRename($id, $name)
	{
		neon()->firefly->mediaManager->rename($id, $name);
		return true;
	}

	/**
	 * Soft delete a media item
	 *
	 * @param string $id
	 * @return boolean
	 * @throws NotFoundHttpException
	 */
	public function actionDelete($id)
	{
		return neon()->firefly->mediaManager->delete($id);
	}

	/**
	 * Move items into a folder
	 * Expects "items" array of ids, and "into" an id
	 *
	 * @apiParam string[] $items - An array of string ids
	 * @apiParam string $into - The id or the path of the folder to move the items into
	 */
	public function actionMove()
	{
		$items = neon()->request->getBodyParam('items');
		$into = neon()->request->getBodyParam('into');
		if ($items === null)
			throw new BadRequestHttpException('You must specify an "items" parameter - containing an array of item ids to move');
		if ($into === null)
			throw new BadRequestHttpException('You must specify an "into" parameter - specifying the directory to move the items into');
		if (strpos($into, '/') !== false) {
			// into has been specified as a path
			$dir = neon()->firefly->mediaManager->getDirectoryMeta($into);
			if ($dir === null)
				throw new BadRequestHttpException("No directory exists with the specified path of '$into' ");
			$into = $dir['id'];
		}
		return neon()->firefly->mediaManager->move($items, $into);
	}

	/**
	 * @param $path
	 * @return array
	 */
	public function actionMeta($path)
	{
		return ['data' => neon()->firefly->mediaManager->getDirectoryMeta($path)];
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function actionMetaById($id)
	{
		return ['data' => neon()->firefly->mediaManager->getMeta($id)];
	}

	/**
	 * Find all references in dds tables where this item is used
	 * @return array
	 * [
	 *  [
	 *      'class' => 'dds class type',
	 *      '_uuid' => 'the dds row object uuid'
	 *  ]
	 * ]
	 */
	public function actionFindReferences($uuid)
	{
		return neon()->firefly->fileManager->findReferences($uuid);
	}

	public function actionSearch($q)
	{
		return Media::search($q);
	}
}
