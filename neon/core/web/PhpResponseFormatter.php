<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 27/06/2017
 * Time: 13:23
 */

namespace neon\core\web;

use Symfony\Component\VarDumper\VarDumper;
use yii\web\ResponseFormatterInterface;

class PhpResponseFormatter implements ResponseFormatterInterface
{
	/**
	 * Formats the specified response.
	 * @param Response $response the response to be formatted.
	 */
	public function format($response)
	{
		$response->content = VarDump($response->data);
	}
}