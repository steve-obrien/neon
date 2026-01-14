<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/07/2018
 * @package neon
 */

namespace neon\core\controllers\api;

use neon\core\web\ApiController;

/**
 * This controller provides an api into the generic data maps exposed by neon
 * Each neon app can expose itself as a map provider
 */
class VersionController extends ApiController
{
	public function verbs()
	{
		return [
			'index' => ['get', 'post']
		];
	}

	/**
	 * Get neon version information and return
	 * @return array
	 */
	public function actionIndex()
	{
		return [
			'versions' => neon()->getNeonVersions(),
			'newer' => neon()->getNeonVersionsNewer(),
			'current' => neon()->version,
			'latest' => neon()->getNeonVersionLatest()
		];
	}
}