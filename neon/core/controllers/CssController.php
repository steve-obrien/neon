<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 01/10/2018
 * @package neon
 */

namespace neon\core\controllers;

use neon\core\helpers\Str;
use neon\core\web\Controller;

/**
 * Class CssController
 * @package neon\core\controllers
 */
class CssController extends Controller
{
	/**
	 * Return the processed css file for the application
	 */
	public function actionAsset($key, $style)
	{
		$cacheSeconds = 31536000; // 1 year into the future - apparently the RFC standard max value
		// cache permanently - a new css file key will be generated if any source files are changes
		// also a new style key will generate if the style data changes (style is md5 of style data)
		// therefore the css url is unique to data and source files and can be cached for as long as possible
		// by apache / client
		// NOTE: may be useful to add this to the request / view as a helper function if the headers work
		// `neon()->response->sendCachePermanentHeaders()`;
		header("Content-type: text/css; charset: UTF-8");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheSeconds) . " GMT");
		header("Pragma: cache");
		header("Cache-Control: public, max-age=$cacheSeconds");
		// return css content with correct headers
		echo neon()->view->getCssByKey($key, $style);
		exit;
	}
}