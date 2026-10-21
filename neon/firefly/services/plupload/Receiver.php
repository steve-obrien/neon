<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 26/11/2016 12:24
 * @package neon
 */

namespace neon\firefly\services\plupload;

use Closure;
use Exception;
use InvalidArgumentException;
use League\Flysystem\UnableToRetrieveMetadata;
use neon\core\helpers\File;
use neon\core\web\Request;
use neon\core\web\UploadedFile;
use neon\firefly\services\FireflySecurityPolicy;
use neon\firefly\services\MediaManager;
use neon\firefly\services\mediaManager\models\Media;
use neon\firefly\services\plupload\PluploadException;

/**
 * This class is focused on receiving plupload files
 * (new Receiver(neon()->request))->receive('file');
 * Plupload needs to negotiate messages back and forth to itself.
 * Therefore a controller action processing a plupload file will potentially
 * be called multiple times for a single upload.
 *
 * public function actionUpload()
 * {
 *     $receiver = new Receiver();
 *     $response = $receiver->receive('file', function(\neon\core\web\UploadedFile $file){
 *         // process the uploaded file
 *         $file->save();
 *     })
 *     return $response;
 * }
 */
class Receiver
{
	/**
	 * @var int seconds Maximum time the file will exist before it gets garbage collected
	 * default 600 (10 minutes) it must take no longer than 10 minutes to upload one chunk
	 * @see $this->_removeOldData
	 */
	public $maxFileAge = 600;

	/**
	 * Store the injected request object
	 * @var Request
	 */
	protected $_request;

	/**
	 * Receiver constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->_request = $request;
	}

	/**
	 * Get the temporary path where plupload will store the file uploads
	 * @return string - writable directory path
	 */
	public function getPath()
	{
		$path = neon()->getAlias('@runtime/plupload');
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}
		return $path;
	}

	/**
	 * Whether the plupload upload will be uploaded in chunks
	 * @return bool
	 */
	public function hasChunks()
	{
		return (bool)$this->_request->getBodyParam('chunks', false);
	}

	/**
	 * Receive a plupload upload
	 * @param string $name - Name of the request variable that holds
	 * the plupload file(s) - typically the name of the plupload input field
	 * @param Closure $handler - A handler function that is passed the uploaded file
	 * method signature is: function(\neon\core\web\UploadedFile $file) {}
	 * @return array  - return the result of the upload back to plupload, this should be json encoded
	 */
	public function receive($name, Closure $handler)
	{
		//FireflySecurityPolicy::assertWhitelistedExtension($name);
		$response = [];
		$response['jsonrpc'] = '2.0';
		$response['id'] = 'plup';
		try {
			if ($this->hasChunks()) {
				$result = $this->receiveChunks($name, $handler);
			} else {
				$result = $this->receiveSingle($name, $handler);
			}
			$response['result'] = $result;
		}
		catch (\Throwable $e) {
			neon()->errorHandler->logException($e);
			$response['error'] = [
				'code' => 400,
				'message' => $this->getUserSafeUploadErrorMessage($e)
			];
		}
		return $response;
	}

	protected function getUserSafeUploadErrorMessage(\Throwable $e): string
	{
		$name = neon()->request->post("name");
		if ($e instanceof \League\Flysystem\FilesystemException) {
			return "The file could not be uploaded due to an invalid file or storage error ".($name ? " : ".$name : ".");
		}
		if ($e instanceof \InvalidArgumentException) {
			if ($name) {
				return rtrim($e->getMessage(),'.'). " : ".$name;
			}
			return $e->getMessage();
		}
		return "The file could not be uploaded".($name ? " : ".$name : ".");
	}

	/**
	 * Process single file upload
	 * @param string $name
	 * @param Closure $handler - function to call on successful upload completed
	 * @return bool|mixed
	 */
	public function receiveSingle($name, Closure $handler)
	{
		if ($file = $this->_request->file($name)) {
			$filename = FireflySecurityPolicy::safeFilenamesOnly()
				? File::getSafeUploadFilename($file->getFilename())
				: $file->getFilename();
			FireflySecurityPolicy::assertWhitelistedExtension($filename);
			FireflySecurityPolicy::assertRequestContentType($file->type);
			$mimetype = FireflySecurityPolicy::assertDetectedMimetype($file->getRealPath());
			if ($mimetype && FireflySecurityPolicy::isRasterImageMimetype($mimetype)) {
				FireflySecurityPolicy::assertRecreateRasterImage($file->getRealPath());
			}
			return $handler($file);
		}
		return false;
	}

	/**
	 * Process chunked file upload
	 * @param string $name
	 * @param Closure $handler - function to call on successful upload completed
	 * @return bool
	 * @throws Exception
	 */
	public function receiveChunks($name, $handler)
	{
		$result = false;
		if ($file = $this->_request->file($name)) {
			$chunk = (int)$this->_request->getBodyParam("chunk", 0);
			$chunks = (int)$this->_request->getBodyParam("chunks", 0);
			$originalName = $this->_request->getBodyParam('name');
			if (!is_string($originalName) || $originalName === '') {
				throw new InvalidArgumentException("Missing 'name' parameter value");
			}
			$safeName = FireflySecurityPolicy::safeFilenamesOnly()
				? File::getSafeUploadFilename($originalName)
				: $originalName;
			FireflySecurityPolicy::assertWhitelistedExtension($safeName);
			FireflySecurityPolicy::assertRequestContentType($file->type);
			$filePath = rtrim($this->getPath(), '/') . '/' . $safeName . '.part';
			$this->_removeOldData($filePath);
			$this->_appendData($filePath, $file);
			if ($chunk == $chunks - 1) {
				$mimetype = FireflySecurityPolicy::assertDetectedMimetype($filePath);
				if ($mimetype && FireflySecurityPolicy::isRasterImageMimetype($mimetype)) {
					FireflySecurityPolicy::assertRecreateRasterImage($filePath);
				}
				$file = new UploadedFile([
					'name' => $safeName,
					'size' => filesize($filePath),
					'tempName' => $filePath,
					'error' => UPLOAD_ERR_OK
				]);
				$result = $handler($file);
				unlink($filePath);
			}
		}
		return $result;
	}

	/**
	 * Todo: requires test
	 *       ^ this comment from 2017 can go f**k itself
	 * Receives a file and saves it to firefly.
	 * Plupload will receive the firefly file meta information and id
	 * in the plupload js:
	 *
	 * ```js
	 * plupload.bind('FileUploaded', function(up, file, result) {
	 *     var response = JSON.parse(result.response);
	 *     console.log(response.result);
	 *     // => gives the file meta (including firefly id) of the file
	 * }
	 * ```
	 *
	 * @param string $name name of the request parameter
	 * @param string $path Optional firefly media virtual path
	 * @return array
	 */
	public function receiveToFirefly($name, $path = '/')
	{
		if (FireflySecurityPolicy::safePathsOnly()) {
			$path = neon()->firefly->mediaManager->getSafeLogicalPath($path);
		};

		return $this->receive($name, function (UploadedFile $file) use ($path) {
			// save file to firefly
			$id = neon()->firefly->fileManager->saveFile($file);
			$meta = neon()->firefly->mediaManager->addFile($id, $path);
			return $meta;
		});
	}

	/**
	 * Receives a file and saves it to firefly file manager only.
	 * Files will not therefore show in the media manager
	 * Plupload will receive the firefly file meta information and id
	 * in the plupload js:
	 *
	 * ```js
	 * plupload.bind('FileUploaded', function(up, file, result) {
	 *     var response = JSON.parse(result.response);
	 *     console.log(response.result);
	 *     // => gives the file meta (including firefly id) of the file
	 * }
	 * ```
	 *
	 * @param string $name name of the request parameter
	 * @return array
	 */
	public function receiveToFireflyFileManager($name)
	{
		return $this->receive($name, function (UploadedFile $file) {
			// save file to firefly
			$id = neon()->firefly->fileManager->saveFile($file);
			return neon()->firefly->fileManager->getMeta($id);
		});
	}

	/**
	 * Append a chunk part file onto the original file
	 * @param $filePathPartial
	 * @param UploadedFile $file
	 * @throws PluploadException
	 */
	protected function _appendData($filePathPartial, UploadedFile $file)
	{
		if (!$out = fopen($filePathPartial, 'ab')) {
			throw new PluploadException('Failed to open output stream.', 102);
		}
		if (!$in = fopen($file->getRealPath(), 'rb')) {
			throw new PluploadException('Failed to open input stream', 101);
		}
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		fclose($out);
		fclose($in);
	}

	/**
	 * Remove old files
	 * @param string $filePath
	 */
	protected function _removeOldData($filePath)
	{
		if (file_exists($filePath) && filemtime($filePath) < time() - $this->maxFileAge) {
			unlink($filePath);
		}
	}
}