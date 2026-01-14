<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 29/01/2017 23:05
 * @package neon
 */

namespace neon\core\web;


class JsonResponseFormatter extends \yii\web\JsonResponseFormatter
{
	protected function formatJson($response)
	{
		// If we have requested an endpoint that returns json with a preferred format
		// of text/html instead of application/json then we are probably browsing the API
		// and therefore want a prettier version of the response.
		$acceptableContentTypes = array_keys(neon()->request->getAcceptableContentTypes());
		$contentTypeIsHtml =  (isset($acceptableContentTypes[0]) && $acceptableContentTypes[0] === 'text/html');
		$prettyGetVarExists = (neon()->request->get('_pretty', false) !== false);
		if ($contentTypeIsHtml || $prettyGetVarExists) {
			$this->prettyPrint = true;
		}
		parent::formatJson($response);
	}
}