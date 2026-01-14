<?php


/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 27/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\web;

use yii\web\RangeNotSatisfiableHttpException;

/**
 * Class Response
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\web
 */
class Response extends \yii\web\Response
{
	public $formatters = [
		self::FORMAT_JSON => '\neon\core\web\JsonResponseFormatter'
	];

	/**
	 * Prepares for sending the response.
	 * The default implementation will convert [[data]] into [[content]] and set headers accordingly.
	 * @throws \InvalidArgumentException if the formatter for the specified format is invalid or [[format]] is not supported
	 */
	protected function prepare()
	{
		// check if we should guess the format
		// mainly if we should convert the response to be json
		if ($this->shouldBeJson($this->data) && $this->format == self::FORMAT_HTML) {
			$this->format = self::FORMAT_JSON;
		}

		parent::prepare();
	}

	/**
	 * Set the response format to be JSON
	 */
	public function asJson()
	{
		$this->format = self::FORMAT_JSON;
	}

	/**
	 * Determine if the given content should be turned into JSON.
	 *
	 * @param  mixed  $content
	 * @return bool
	 */
	protected function shouldBeJson($content)
	{
		return !is_string($content) && $content !== null;
	}

	/**
	 * A duplicate of isSeekable - but is a private function
	 * 
	 * @param mixed $handle
	 * @return bool
	 */
	public function isSeek($handle)
	{
		if (!is_resource($handle)) {
			return true;
		}
		$metaData = stream_get_meta_data($handle);
		return isset($metaData['seekable']) && $metaData['seekable'] === true;
	}

	/**
	 * @inheritdoc
	 * Override Yii implementation to handle video streaming better.
	 * return neon()->response->sendStreamAsFile($handle, $attachmentName, $options);
	 * No need to call ->send(). The response is sent automatically.
	 * And isSent=true to avoid send() causing problems.
	 */
	public function sendStreamAsFile($handle, $attachmentName, $options = []) : static
	{
		$this->format = self::FORMAT_RAW;
		$this->trigger(self::EVENT_BEFORE_SEND);
		$this->prepare();
		$headers = $this->getHeaders();
		if (isset($options['fileSize'])) {
			$fileSize = $options['fileSize'];
		} else {
			if ($this->isSeek($handle)) {
				fseek($handle, 0, SEEK_END);
				$fileSize = ftell($handle);
				fseek($handle, 0, SEEK_SET); // reset to beginning of the file
			} else {
				// For non-seekable streams, try to get the size using fstat
				$stat = fstat($handle);
				$fileSize = $stat ? $stat['size'] : 0;
			}
		}

		$range = $this->getHttpRange($fileSize);
		if ($range === false) {
			$headers->set('Content-Range', "bytes */$fileSize");
			throw new RangeNotSatisfiableHttpException();
		}

		list($begin, $end) = $range;
		// only allow 3MB to be streamed at a time
		$maxSize = 3 * 1024 * 1024; // 3MB
		if (($end - $begin + 1) > $maxSize) {
			$end = $begin + $maxSize - 1;
		}
		$length = $end - $begin + 1;

		if ($begin != 0 || $end != $fileSize - 1) {
			$this->setStatusCode(206);
			$headers->set('Content-Range', "bytes $begin-$end/$fileSize");
		} else {
			$this->setStatusCode(200);
		}

		$mimeType = isset($options['mimeType']) ? $options['mimeType'] : 'application/octet-stream';
		$this->setDownloadHeaders($attachmentName, $mimeType, !empty($options['inline']), $length);

		// $this->stream = [$handle, $begin, $end];

		$headers->set('Content-Length', $length);
		$headers->set('Accept-Ranges', 'bytes');

		if (ob_get_length()) ob_end_clean();
		$this->sendHeaders();

		fseek($handle, $begin);
		$bufferSize = 524288; // 0.5 MB buffer size
		while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
			if ($pos + $bufferSize > $end) {
				$bufferSize = $end - $pos + 1;
			}
			echo fread($handle, $bufferSize);
			flush();
		}

		// safely close the handle if it's still a live stream
		if (is_resource($handle) && get_resource_type($handle) === 'stream') {
			fclose($handle);
		}

		$this->trigger(self::EVENT_AFTER_SEND);
		$this->isSent = true;

		return $this;
	}
}
