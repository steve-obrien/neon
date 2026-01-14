<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/12/2016 19:04
 * @package neon
 */

namespace neon\core\web;

use neon\user\services\apiTokenManager\JwtTokenAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;

/**
 * Class Controller
 * @package neon\core\web
 *
 * @method \neon\core\web\View getView()
 */
class Controller extends \yii\web\Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'globalPassword' => [
				'class' => FilterGlobalPassword::class
			],
			'contentNegotiator' => [
				'class' => ContentNegotiator::class,
				'formats' => [
					'text/html' => Response::FORMAT_HTML,
					'application/json' => Response::FORMAT_JSON,
					'application/xml' => Response::FORMAT_XML,
				],
			],
		];
	}

	/**
	 * Check if a view file exists
	 *
	 * @param $view
	 * @return bool
	 */
	public function viewExists($view)
	{
		return $this->getView()->exists($view, $this);
	}

	/**
	 * Find a view under the context of this controller
	 *
	 * @param  string  $view  The name of the view to find
	 * @return  string|false The view file path
	 */
	public function findView($view)
	{
		return $this->getView()->findView($view, $this);
	}
}