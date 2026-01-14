<?php

namespace neon\cms\components;

use neon\cms\models\CmsVisit;
use neon\core\web\Request;
use neon\core\web\Response;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;
use \neon\cms\models\CmsUrl;
use yii\base\BaseObject;

class CmsUrlRule extends BaseObject implements UrlRuleInterface
{
	/**
	 * The controller action route where matching rules will be sent
	 * @var string
	 */
	public $cmsRoute = 'cms/render/page';

	/**
	 * Parses the given request and returns the corresponding route and parameters.
	 * Parse the incoming request url to see if it is a CMS route - we test for an exact url path match
	 *
	 * @param UrlManager $manager the URL manager
	 * @param Request $request the request component
	 * @throws \Exception - if query goes wrong.
	 * @return array|bool the parsing result. The route and the parameters are returned as an array.
	 * If false, it means this rule cannot be used to parse this path info.
	 */
	public function parseRequest($manager, $request)
	{
		$pathInfo = '/' . $request->getPathInfo();
		//dd(CmsUrl::find()->where(['url' => $pathInfo])->limit(1)->one());
		$url = neon()->cache->getOrSet($pathInfo, function() use ($pathInfo) {
			return CmsUrl::find()->where(['url' => $pathInfo])->limit(1)->one();
		});

		// if the url is not in the url table then return false to continue processing other route rules
		// typically in a neon setup the module/controller/action rule
		if (! $url) return false;
		// check if the url found represents a redirect
		if ($url['redirect'] !== null) {
			// keep query params:
			$qs = neon()->request->getQueryString();
			neon()->response->redirect($url['redirect'].($qs ? '?'.$qs : ''), 301)->send();
			exit;
		}
		return [$this->cmsRoute, ['nice_id' => $url['nice_id'], 'slug' => $pathInfo]];
	}

	/**
	 * Creates a URL according to the given route and parameters.
	 *
	 * Convert a cms page route to its pretty url for e.g.:
	 * a route of: `['/cms/render/page', 'nice_id' => 'my_nice_id']`
	 * will return the pretty url as defined against the nice_id in the url table
	 *
	 * @param UrlManager $manager the URL manager
	 * @param string $route the route. It should not have slashes at the beginning or the end.
	 * @param array $params the parameters
	 * @return string|bool the created URL, or false if this rule cannot be used for creating this URL.
	 */
	public function createUrl($manager, $route, $params)
	{
		// only run this if trying to access a cms controller route otherwise
		// this would be called for every url call.
		if ($route === $this->cmsRoute && isset($params['nice_id'])) {
			// enables the canonical lookup to work when only the request data is available
			if (isset($params['slug']) && !empty($params['slug'])) {
				return $params['slug'];
			}
			// without a provided slug asking yii to translate ['cms/render/page', 'nice_id'=>'blog']
			// will lookup the correct url for the blog page.
			return $this->getUrlByNiceId($params['nice_id']);
		}
		return false;
	}

	/**
	 * Get the url details based on nice_id - assumes routes for /cms/render/page
	 *
	 * @param string $niceId
	 * @return array|false
	 */
	public function getUrlByNiceId($niceId)
	{
		return CmsUrl::getUrlByNiceId($niceId);
	}
}
