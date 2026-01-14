<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/07/2018
 * @package neon
 */

namespace neon\core\controllers\api;

use neon\core\helpers\Hash;
use neon\core\web\ApiController;

/**
 * This controller provides an api into the generic data maps exposed by neon
 * Each neon app can expose itself as a map provider
 */
class MapController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function verbs()
	{
		return [
			'providers' => ['get'],
		];
	}

	/**
	 * Get a list of data map providers
	 * @return array
	 */
	public function actionProviders()
	{
		return neon()->app('core')->getDataMapProviders();
	}
}