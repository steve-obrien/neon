<?php
namespace neon\core\web;

use neon\core\helpers\Url;
use yii\web\UrlManager as WebUrlManager;

Class UrlManager extends WebUrlManager
{
	/**
	 * Returns the canonical URL of the currently requested page.
	 *
	 * The canonical URL is constructed using the current controller's [[\yii\web\Controller::route]] and
	 * [[\yii\web\Controller::actionParams]]. You may use the following code in the layout view to add a link tag
	 * about canonical URL:
	 *
	 * ```php
	 * $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::canonical()]);
	 * ```
	 *
	 * @return string the canonical URL of the currently requested page
	 */
	public function getCanonical()
	{
		$params = neon()->controller->actionParams;
        $params[0] = neon()->controller->getRoute();
		$urlManager = neon()->getUrlManager();
		$url = $urlManager->createUrl($params);
		if (strpos($url, '://') === false) {
			$hostInfo = setting('cms', 'canonical', $urlManager->getHostInfo());
			if (strncmp($url, '//', 2) === 0) {
				$url = substr($hostInfo, 0, strpos($hostInfo, '://')) . ':' . $url;
			} else {
				$url = $hostInfo . $url;
			}
		}
		return Url::ensureScheme($url, null);
	}
}