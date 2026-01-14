<?php

namespace neon\cms\controllers;

use neon\cms\models\CmsPage;
use neon\cms\models\CmsUrl;
use neon\core\web\Controller;
use neon\core\web\Response;
use yii\filters\AccessControl;
use \neon\user\services\apiTokenManager\JwtTokenAuth;
use yii\web\HttpException;

/**
 * The RenderController will draw a page using Cobe. It needs to be both
 * non-secure for public pages and secure for private role-based pages
 */
class RenderController extends Controller
{
	private $_secure = false;
	private $_roles = [];

	public function __construct($id, $module, $config = [])
	{
		profile_begin('__construct');
		parent::__construct($id, $module, $config);
		// determine whether or not the page request needs to be secure
		if (neon('user')->routeHasRoles('/'.neon()->getRequest()->getPathInfo(), $roles)) {
			$this->_secure = true;
			$this->_roles = $roles;
		}
		profile_end('__construct');
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		profile_begin('controller-before-action');
		if ($this->_secure) {
			// Generate the JWT api token - allows logged in users to have access
			// to REST api methods without the need to generate an API token
			JwtTokenAuth::createCookie(neon()->request, neon()->response);
		}
		$return = parent::beforeAction($action);
		profile_end('controller-before-action');
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		$behaviours = parent::behaviors();
		if ($this->_secure) {
			$behaviours = array_merge($behaviours, [
				'access' => [
					'class' => AccessControl::class,
					'rules' => [
						// allow authenticated users
						[
							'allow' => true,
							'roles' => $this->_roles
						],
						// everything else is denied
					],
				],
			]);
		}

		return $behaviours;
	}

	public function actionPageOld($nice_id, $slug) {

	}

	/**
	 * Render a CMS Page
	 * @return string
	 * @throws \yii\web\HttpException
	 * @param string $nice_id
	 * @param string $slug - this is necessary for canonical urls or list pages for example blog-post
	 * nice id is impossible to get the overridden pretty url without extra data - the uuid of the blog post or the
	 * slug it uses - without this property the canonical function will return the url of the first blog-post
	 * (collection page) it finds.
	 */
	public function actionPage($nice_id, $slug='')
	{
		$cms = $this->getCms();

		\Neon::beginProfile('COBE::RENDER_ACTION', 'cobe');
		$cms = $this->getCms();
		$page = $cms->getPage();
		// check the page with the nice_id exists
		if (! $page->setById($nice_id)) {
			$this->pageNotFound();
		}
		
		// set the page mode
		if (isset($_GET['edit']) && !neon()->user->isGuest) {
			$page->setEditMode((bool) neon()->request->get('edit'));
		}

		// needs to do the theme look up here.
		if (($theme = neon()->request->get('_theme')) && !neon()->user->isGuest) {
			// override either default or config settings via get
			neon()->cms->setThemeName($theme);
		}

		// Don't display draft pages unless in edit mode.
		if ($page->isStatusDraft() && ! $page->isInEditMode()) {
			$this->pageNotFound();
		}

		\Neon::endProfile('COBE::RENDER_ACTION', 'cobe');

		// return $render;
		// to make this work the same as everything else
		// this would then enable the option to change the template and template renderer via the template extension
		// e.g. .neon or .tpl or .twig etc
		$this->layout = true; // layout is controlled by smarty
		$this->setViewPath(neon()->cms->getThemeAlias());
		return $this->render($page->getTemplate(), [
			'page' => $page->getPageData(),
			'site' => neon()->cms->getSiteData()
		]);
	}

	/**
	 * the set of unactioned static data requests made for a page
	 * @var array
	 */
	protected $staticDataRequests = [];

	/**
	 * the set of unactioned dynamic data requests made for a page
	 * @var array
	 */
	protected $dynamicDataRequests = [];

	/**
	 * Check to see if there are any data requests available
	 * @return boolean
	 */
	public function hasDataRequests()
	{
		return (count($this->staticDataRequests)+count($this->dynamicDataRequests) > 0);
	}

	/**
	 * Make any data requests and store the results.
	 */
	protected function makeDataRequests()
	{
		\Neon::beginProfile('COBE::MAKE_DATA_REQUESTS', 'cosmos');
		$this->makeDynamicDataRequests();
		$this->makeStaticDataRequests();
		\Neon::endProfile('COBE::MAKE_DATA_REQUESTS', 'cosmos');
	}

	/**
	 * Make any data requests to DDS and store the results.
	 * This then clears the data requests so if you need to know if there
	 * were any data requests, use @see hasDataRequests before calling
	 * this method
	 */
	protected function makeDynamicDataRequests()
	{
		if (count($this->dynamicDataRequests)>0) {
			$dds = $this->getIDdsObjectManagement();
			foreach ($this->dynamicDataRequests as $id=>$dr) {
				$dds->addObjectRequest($dr['class'], $dr['filters'], $dr['order'], $dr['limit'], $id);
			}
			$data = $dds->commitRequests();
			foreach ($data as $i=>$d) {
				$this->requestedData[$i] = $d['rows'];
				$this->requestedDataMeta[$i] = [
					'start' => $d['start'],
					'length' => $d['length'],
					'total' => $d['total']
				];
			}
			$this->dynamicDataRequests = [];
		}
	}

	/**
	 * Make any static data requests to CosMoS and store the results.
	 * This then clears the data requests so if you need to know if there
	 * were any data requests, use @see hasDataRequests before calling
	 * this method
	 */
	protected function makeStaticDataRequests()
	{
		if (count($this->staticDataRequests)>0) {
			$cms = $this->getICmsStaticData();
			$data = $cms->bulkStaticContentRequest($this->staticDataRequests);
			foreach ($data as $i=>$d)
				$this->requestedData[$i] = ($d===null ? 'not found' : $d);
			$this->staticDataRequests = [];
		}
	}

	/**
	 * Generate an xml sitemap for the website
	 */
	public function actionSitemap()
	{
		$urls = collect(
			CmsUrl::find()
			->select('url')
			->innerJoin(CmsPage::tableName(), 'cms_page.nice_id = cms_url.nice_id')
			->where("status='PUBLISHED'")
			->all()
		)->reduce(function($carry, $url){
			return $carry . "\t<url>\n\t\t<loc>" . url($url['url'], true) . "</loc>\n\t</url>" . "\n";
		});
		header('Content-Type:application/rss+xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$urls.'</urlset>';
		exit;
	}

	/**
	 * Set up the page from the routing information
	 * @return string
	 * @throws \yii\web\HttpException 404
	 */
	public function actionPageFromRoute()
	{
		$request = neon()->request;
		$url = $request->get('page');
		if (!$url)
			$this->pageNotFound();
		if (($urlEntry = CmsUrl::findByUrl($url)))
			return $this->actionPage($urlEntry['nice_id']);
		$this->pageNotFound();
	}

	/**
	 * @return \neon\cms\App
	 */
	public function getCms()
	{
		return neon('cms');
	}

	/**
	 * Throw a page not found exception
	 * @throws \yii\web\HttpException
	 */
	protected function pageNotFound()
	{
		throw new \yii\web\HttpException(404, 'No page found');
	}

	/**
	 * Get hold of an object manager
	 * @return \neon\daedalus\services\ddsManager\interfaces\IDdsObjectManagement
	 */
	protected function getIDdsObjectManagement()
	{
		return neon('dds')->IDdsObjectManagement;
	}

	/**
	 * create a unique id from a static page request key and page id
	 * @param type $request
	 * @return string
	 */
	private function createStaticContentId($request) {
		return $this->getICmsStaticData()->createStaticContentId($request);
	}

	/**
	 * private reference to a cmsStaticData handler
	 * @var neon\cms\services\cmsManager\interfaces\ICmsStaticData
	 */
	private function getICmsStaticData()
	{
		return neon('cms')->ICmsStaticData;
	}
}
