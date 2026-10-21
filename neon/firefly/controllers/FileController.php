<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 28/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\firefly\controllers;

use Carbon\Carbon;
use neon\core\helpers\Arr;
use neon\core\helpers\Hash;
use neon\core\helpers\Str;
use neon\firefly\services\FireflySecurityPolicy;
use neon\firefly\services\plupload\Receiver;
use neon\core\web\Response;
use neon\firefly\services\fileManager\models\FileManager;
use yii\helpers\FileHelper;
use yii\web\HttpException;

class FileController extends \yii\web\Controller
{
	public function actionDestroyDeleted()
	{
		return neon()->firefly->mediaManager->destroyDeleted();
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		// before doing anything we must turn off the session
		// this controller is for stateless non session APIs
		neon()->user->enableSession = false;
		neon()->user->loginUrl = null;
		neon()->getRequest()->enableCsrfValidation = false;
	}

	/**
	 * Show a file manager managed file
	 *
	 * /firefly/file/get?id=[fileuuid]
	 * /firefly/[fileuuid]
	 * @return \neon\core\web\Response
	 */
	public function actionGet()
	{
		$request = neon()->request;
		$firefly = neon()->firefly;
		$id = $request->get('id');
		if (FireflySecurityPolicy::fileGetOnlyAcceptsUuids() && !Hash::isUuid64($id)) {
			throw new HttpException(400, 'Invalid file id');
		}

		if (!$firefly->fileManager->exists($id)) {
			neon()->response->headers->add('Content-Type', 'image/jpeg');	
			neon()->response->format = Response::FORMAT_RAW;
			neon()->response->content = neon()->firefly->imageManager->placeholder(neon()->request->get());
			return neon()->response;
		}

		// as the url is unique for a file we can be aggressive about caching
		// see if we already have this in cache and the browser already has it too
		$this->handleNotModified();

		// Handle images
		// -------------
		$meta = $firefly->fileManager->getMeta($id);
		if (substr($meta['mime_type'], 0, 5) == 'image') {
			return $this->actionImg();
		}

		// Handle other file types
		// -----------------------
		// set expiry information on the file
		$this->sendCacheHeaders($meta['mime_type'], $meta['size'], $meta['updated_at'], md5($request->absoluteUrl), $meta['name']);
		$download = $request->get('download', $request->get('dl', false));
		
		try {
			// this can throw an error if the path is unknown
			return $firefly->fileManager->sendFile($id, (bool) $download);
		} catch (\Exception $e) {
			// if path unknown respond with a blank placeholder
			return neon()->response->sendFile(dirname(__DIR__) . '/assets/placeholder.jpg', 'No File', ['inline' => true]);
		}
	}

	/**
	 * Alternative action url for getting images for more consistent interface
	 *
	 * @return \neon\core\web\Response
	 * @throws HttpException
	 */
	public function actionImage()
	{
		return $this->actionImg();
	}

	/**
	 * Show a file manager managed image
	 * /mydomain.com/firefly/file/img?id=[fileuuid]
	 * @throws HttpException if id request parameter is not defined
	 * @return Response
	 */
	public function actionImg()
	{
		$id = neon()->request->getRequired('id');

		if (env('NEON_FIREFLY_FILE_IMG_ONLY_ACCEPT_UUIDS',false) && !Hash::isUuid64($id)) {
			throw new HttpException(400, 'Invalid file id');
		}

		if (Hash::isUuid64($id) && !neon()->firefly->fileManager->exists($id)) {
			// show placeholder image - should be handled by image manager - controllers should not know
			return neon()->response->sendFile(dirname(__DIR__) . '/assets/placeholder.jpg', 'No File', ['inline' => true]);
		}

		// create a unique request key - add v2 to force use of new cached version
		$requestKey = neon()->request->absoluteUrl . 'v10';

		// see if we already have this in cache and the browser already has it too
		// We want to remove the need to process images when ever possible
		// Send not modified header if already cached
		$this->handleNotModified($requestKey);

		// $meta = neon()->firefly->fileManager->getMeta($id);
		// $filePath = neon()->getAlias("@var/storage/media/".$meta['path']);
		// header('X-Accel-Redirect: ' . $filePath);
		// exit;

		$image = $this->handleImageProcessing($requestKey, $id);

		// This caches this whole request.... by generating the image file
		// note we only have the id when originally asking for the image via `getImage()`
		// Only images should ever be made public - these have been resaved as images by this point
		// $url = $this->generatePublicFile($id, $image['image']);
		// return $this->redirect($url);
		neon()->response->format = Response::FORMAT_RAW;
		$this->sendCacheHeaders($image['mime_type'], $image['size'], $image['updated_at'], $image['eTag'], basename($id));
		neon()->response->content = $image['image'];

		return neon()->response;
	}


	public function handleImageProcessing($requestKey, $idOrUrl)
	{
		// retrieve (or generate) from cache and return with appropriate caching headers
		return neon()->firefly->getCache()->getOrSet($requestKey, function() use ($idOrUrl, $requestKey) {
			$processedImage = neon()->firefly->imageManager->process($idOrUrl, neon()->request->get());
			$mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $processedImage);
			// svgs don't seem display in browsers without specifically the svg+xml mime type
			if ($mimeType === 'image/svg') $mimeType = 'image/svg+xml';
			return [
				// recalculate the mime type in case was spoofed
				'size' => strlen($processedImage),
				'mime_type' => $mimeType,
				'updated_at' => date("Y-m-d H:i:s"),
				'image' => $processedImage,
				'eTag' => md5($requestKey)
			];
		});
	}


	/**
	 * Another potential cache strategy to generate a static asset image file in a publicly accessible folder
	 * @param string $id - fileManager file id
	 * @param mixed $imageContent - the contents of the image
	 * @throws \yii\base\Exception
	 * @return string - the public url path
	 */
	public function generatePublicFile($id, $imageContent)
	{
		$dir = substr($id,0, 3);
		$publicDir = neon()->getAlias("@webroot/assets/firefly/$dir");
		if (!file_exists($publicDir)) {
			FileHelper::createDirectory($publicDir);
			file_put_contents("$publicDir/$id", $imageContent);
		}
		return neon()->getAlias("@web/assets/firefly/$dir/$id");
	}

	/**
	 * Send appropriate cache headers
	 * @param string $mime - mime type of file
	 * @param string|int $size - size in bytes of response
 	 * @param string $modified - MySQL Datetime
	 * @param string $eTag - eTag token header to use
	 * @param string $name - filename string to use in content disposition header
	 */
	public function sendCacheHeaders($mime, $size, $modified, $eTag, $name)
	{
		$headers = neon()->response->headers;
		$headers->add('Accept-Ranges', 'bytes');
		$headers->add('Content-Type', $mime);
		$headers->add('Content-Length', $size);
		$headers->add('Access-Control-Allow-Origin', '*');
		$headers->add('Last-Modified', Carbon::createFromFormat('Y-m-d H:i:s', $modified)->toRfc1123String());
		$headers->add('Cache-Control', 'private, max-age=30000000, immutable, only-if-cached');
		$headers->add('Expires', Carbon::now()->addSeconds(30000000)->toRfc1123String());
		$headers->add('ETag', $eTag);
		$headers->add('Content-Disposition', 'inline; filename="'.$name.'"');
	}

	/**
	 * Handle a request that can return a 304 not modified header
	 * @param bool|string $cacheKey - default false - if cache key is specified
	 */
	public function handleNotModified($cacheKey=false)
	{
		$requestHeaders = neon()->request->headers;
		$browserHasCache = (isset($requestHeaders['If-Modified-Since']) || isset($requestHeaders['If-None-Match']));
		if ($browserHasCache && (!$cacheKey || neon()->firefly->getCache()->exists($cacheKey))) {
			$this->sendNotModifiedHeaders();
		}
	}

	/**
	 * Send appropriate headers for 304 Not Modified header
	 */
	public function sendNotModifiedHeaders()
	{
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not modified');
		header('Cache-Control: private, max-age=30000000, immutable, only-if-cached');
		header('Expires: ' . Carbon::now()->addYear()->toRfc1123String());
		exit;
	}

	/**
	 * API endpoint for plupload
	 * @return array
	 */
	public function actionUpload()
	{
		$r = new Receiver(neon()->request);
		return $r->receiveToFirefly('file');
	}
}
