<?php

namespace neon\cms\components;

use \neon\cms\models\CmsUrl;
use \neon\cms\models\CmsPage;
use \neon\cms\components\Theme;
use \neon\cms\components\Renderer;
use neon\core\form\assets\CkeditorAsset;
use neon\core\helpers\Hash;
use neon\core\helpers\Str;
use \neon\core\helpers\Url;
use \neon\core\helpers\Html;
use yii\base\ViewContextInterface;

/**
 * This class is responsible for getting the data for a page
 * This includes the top level title and meta information but also loaded data for widgets
 * The page is basically the same as the global neon()->view
 * Ideally we deprecate the Page component in favour of the global neon()->view component
 *
 * See @render to understand how in-template data calls are made and affect the rendering
 */
class Page implements ViewContextInterface
{
	const COBE_EDITING = 'COBE_EDITING';

	/**
	 * the page we landed on initially
	 */
	protected $_page = null;

	/**
	 * the set of requested data for a page
	 * @var array
	 */
	protected $requestedData = [];

	/**
	 * for dynamic requests, contains the 'start', 'length' and 'total
	 * counts for the data
	 * @var array
	 */
	protected $requestedDataMeta = [];

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
	 * whether or not this page is in edit mode
	 */
	private $_editMode = false;

	/**
	 * ===============================================================================
	 * Methods on the current page
	 * ===============================================================================
	 * Methods related to the page landed on (the 'current' page)
	 */

	/**
	 * whether or not the page is in edit mode
	 *
	 * @return bool
	 */
	public function isInEditMode()
	{
		if (neon()->session===null) return false;
		return neon()->session->get(self::COBE_EDITING, false);
	}

	/**
	 * Whether the page is in draft mode
	 *
	 * @return boolean
	 */
	public function isStatusDraft()
	{
		return strtoupper($this->_page['status']) === 'DRAFT';
	}

	const CMS_STATIC_TABLE = 'cms_static_content';

	public function getViewPath()
	{
		return neon()->getAlias('@root/themes/default');
	}

	/**
	 * Set the page into edit mode
	 * You must be logged in to be able to edit pages
	 * @param bool $editable
	 */
	public function setEditMode($editable)
	{
		$editingRoles = neon('cms')->editingRoles;
		if (!empty($editingRoles) && neon()->user->hasRoles($editingRoles)) {
			// may receive request data to ensure conversion to boolean with (bool)
			neon()->session->set(self::COBE_EDITING, (bool) $editable);
		} else {
			// no permission to edit so remove session variable
			neon()->session->remove(self::COBE_EDITING);
		}
	}

	/**
	 * Whether the database page data has been found and set
	 * @return bool
	 */
	public function hasPage()
	{
		return $this->_page ? true : false;
	}

	/**
	 * Gets the url slug that loaded this page
	 *
	 * @return string - the url
	 */
	public function getUrl()
	{
		return $this->getPageData()['url'];
	}

	/**
	 * Sets a page by its id
	 * @param $pageId  the id or nice_id of the page
	 * @return boolean  true if the page is set
	 */
	public function setById($pageId)
	{
		$page = CmsPage::findBy($pageId);
		$this->setPageFromDb($page);
		return $this->hasPage();
	}

	/**
	 * Get the layout file if it's set
	 * @return string|null
	 */
	public function getLayout()
	{
		return isset($this->_page['layout']) ? $this->_page['layout'] : null;
	}

	/**
	 * Gets the template file from the database row data
	 * @return string|null
	 */
	public function getTemplate()
	{
		return isset($this->_page['template']) ? $this->_page['template'] : null;
	}

	/**
	 * Get the database page information
	 *
	 * @return []
	 */
	public function getPageData()
	{
		// protect against missing pages
		$data = ['page_params'=>[]];
		if (!empty($this->_page)) {
			$data = $this->_page;
			// add the nice_id as CmnUrRoute will add this on first parse through Cobe without gets but
			// not on requests with get parameters which has led to hard-to-debug runtime coding errors
			if (!isset($data['page_params']['nice_id']) && isset($data['nice_id']))
				$data['page_params']['nice_id'] = $data['nice_id'];
		}
		$params = array_merge($data['page_params'], neon()->request->get(), neon()->request->post());
		unset($data['page_params']);
		$data['params'] = Html::encodeEntities($params);
		$data['url'] = '/' . neon()->request->pathInfo;
		$data['uri'] = $_SERVER['REQUEST_URI'];
		$data['host'] = Url::base(true);
		$data['editing'] = $this->isInEditMode();
		return $data;
	}

	public function getId()
	{
		return $this->hasPage() ? $this->_page['id'] : null;
	}

	/**
	 * Get the page title as defined by the database
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->hasPage() ? $this->_page['title'] : '';
	}

	/**
	 * ===============================================================================
	 * Other pages
	 * ===============================================================================
	 * Methods dealing with finding information about other pages by id, nice_id
	 * and their URLs
	 */

	/**
	 * Get the main url for a page from its id
	 * @param string  $id  The id of a page or the nice id
	 * @deprecated - use standard url rules to convert between routes and urls @see CmsUrlRule
	 * @return string
	 */
	public function getUrlOfPage($id)
	{
		if (!$id) return '';
		return $url = url(['/cms/render/page', 'nice_id'=>$id]);
	}

	/**
	 * Get the main url for a page from its nice_id
	 *
	 * @param string  $niceId  The nice id of a page
	 * @deprecated - use standard url rules to convert between routes and urls @see CmsUrlRule
	 * @return string
	 */
	public function getUrlOfPageByNiceId($niceId)
	{
		return $url = url(['/cms/render/page', 'nice_id'=>$niceId]);
	}

	/**
	 * ===============================================================================
	 * Template functions
	 * ===============================================================================
	 * note these should not be aware of smarty as we may want to switch the template
	 * engine in the future (twig or other) but should do the work
	 *
	 */

	/**
	 * Get the global site information
	 *
	 * @return array
	 */
	public function getSiteData()
	{
		return neon('cms')->siteData;
	}

	/**
	 * ===============================================================================
	 * Page rendering, static and dynamic data methods
	 * ===============================================================================
	 * Methods relating to the rendering of the page and getting data
	 * from the database
	 */

	/**
	 * Renders the page
	 *
	 * If during rendering in-template data requests are made then the page is re-rendered
	 * once the data has been collected and passed to the template. If there are additional
	 * data requests that rely on the results from previous ones, then these are collected
	 * on additional parses. This means data can be collected in a dynamic and flexible
	 * way and data can be collected in bulk calls rather than individual calls throughout
	 * a page.
	 *
	 * This works well when the data requests are heavy compared to rendering time
	 * however it works not so well when rendering time is heavy compared to the db calls.
	 * In most cases it's proved not to be a significant performance issue and where it has
	 * been the following technique resolved it.
	 *
	 * To improve the rendering time of the page keep the data requests either at the top
	 * of the page allowing no rendering until all the data has been collected, or put them
	 * into a plugin called at the top of the page. Ensure the plugin uses the request
	 * commit approach of Daedalus rather than making individual calls so that it can take
	 * advantage of Daedalus' bulk database calling.
	 *
	 * Once Daedalus supports graphql and we have a page builder we can hoist all calls
	 * out of new templates and add a data collection method generically which would mean
	 * pages built that way render only once and pages built otherwise can still function.
	 *
	 * @return html | bool  the rendered page or false if the template couldn't be found
	 */
	// public function render()
	// {
	// 	$this->handleEditMode();

	// 	// use this to trouble shoot slow page rendering
	// 	static $numberOfRenders = 0;

	// 	\Neon::beginProfile('COBE::RENDER_PAGE', 'cobe');

	// 	$template = $this->getTemplate();
	// 	if ($template === null) {
	// 		\Neon::endProfile('COBE::RENDER_PAGE', 'cobe');
	// 		return false;
	// 	}

	// 	$renderer = $this->getRenderer();
	// 	$html = '';
	// 	$hasDataRequests = false;

	// 	// render page until no more data requests found (see notes above)
	// 	// do {
	// 	// 	$numberOfRenders++;
	// 	// 	$html = $renderer->renderTemplate($template);
	// 	// 	if (($hasDataRequests = $this->hasDataRequests()))
	// 	// 		$this->makeDataRequests();
	// 	// } while ($hasDataRequests == true);

	// 	// // If the number of renders are excessive (4+), issue a warning.
	// 	// if ($numberOfRenders >= 4)
	// 	// 	\Neon::debug('Excessive number of renders (>=4): ' . $numberOfRenders);

	// 	// inject assets into template
	// 	$html = neon()->view->injectHtml($html);

	// 	\Neon::endProfile('COBE::RENDER_PAGE', 'cobe');
	// 	return $html;
	// }

	// /**
	//  * A widget requests its data from the page, passing in its data request
	//  * If the data exists, it is returned, and if not the request is noted
	//  * ready for the next bout of requests to Daedalus or static data
	//  * @param string | array $dataRequest
	//  * @param array &$data  the returned data (which can be an empty array)
	//  * @param string &$id  on return set to a unique identifier for the request.
	//  * @param boolean $isStatic  whether or not this request is for static or
	//  *   dynamic (daedalus) data
	//  * @param array &$meta  if the data is dynamic, this is set to the
	//  *   meta information for that request (currently start, length, total)
	//  * @return boolean true if data was returned and false if the data was
	//  *   not yet available
	//  */
	// public function requestWidgetData($dataRequest, &$data, &$id, $isStatic, &$meta)
	// {
	// 	$data = [];
	// 	$meta = [];
	// 	$id = $this->canonicaliseRequest($dataRequest, $isStatic);
	// 	// see if we have the data
	// 	if (isset($this->requestedData[$id])) {
	// 		$data = $this->requestedData[$id];
	// 		if (!$isStatic && isset($this->requestedDataMeta[$id]))
	// 			$meta = $this->requestedDataMeta[$id];
	// 		return true;
	// 	}
	// 	// if not then add it to the list of requested data
	// 	if ($isStatic)
	// 		$this->staticDataRequests[$id] = $dataRequest;
	// 	else
	// 		$this->dynamicDataRequests[$id] = $dataRequest;
	// 	return false;
	// }

	// /**
	//  * convert a request into a format that can be used in the rest of the
	//  * page component. Return an id based on a canonicalised form of the request
	//  * @param array | string $dataRequest  the request or a request key.
	//  *   The content of this depends on whether or not this is a static or dynamic
	//  *   request. For static ones, this should contain a 'key' and 'page_id'. The
	//  *   page_id can be null. For dynamic calls, it should contain any or all of
	//  *   'class', 'filters', 'order', and 'limit' or be a data request string
	//  * @param
	//  * @return string
	//  */
	// protected function canonicaliseRequest(&$dataRequest, $isStatic=false)
	// {
	// 	if ($isStatic) {
	// 		$id = $this->createStaticContentId($dataRequest);
	// 		$dataRequest['id'] = $id;
	// 	} else {
	// 		$request = array('class'=>null, 'filters'=>[], 'order'=>[], 'limit'=>[]);
	// 		$dataRequest = array_merge($request, $dataRequest);
	// 		$id = md5(str_replace([' '],'', json_encode($dataRequest)));
	// 	}
	// 	return $id;
	// }

	// /**
	//  * Make any data requests and store the results.
	//  */
	// public function makeDataRequests()
	// {
	// 	\Neon::beginProfile('COBE::MAKE_DATA_REQUESTS', 'cosmos');
	// 	$this->makeDynamicDataRequests();
	// 	$this->makeStaticDataRequests();
	// 	\Neon::endProfile('COBE::MAKE_DATA_REQUESTS', 'cosmos');
	// }


	// /**
	//  * Make any data requests to DDS and store the results.
	//  * This then clears the data requests so if you need to know if there
	//  * were any data requests, use @see hasDataRequests before calling
	//  * this method
	//  */
	// protected function makeDynamicDataRequests()
	// {
	// 	if (count($this->dynamicDataRequests)>0) {
	// 		$dds = $this->getIDdsObjectManagement();
	// 		foreach ($this->dynamicDataRequests as $id=>$dr) {
	// 			$dds->addObjectRequest($dr['class'], $dr['filters'], $dr['order'], $dr['limit'], $id);
	// 		}
	// 		$data = $dds->commitRequests();
	// 		foreach ($data as $i=>$d) {
	// 			$this->requestedData[$i] = $d['rows'];
	// 			$this->requestedDataMeta[$i] = [
	// 				'start' => $d['start'],
	// 				'length' => $d['length'],
	// 				'total' => $d['total']
	// 			];
	// 		}
	// 		$this->dynamicDataRequests = [];
	// 	}
	// }

	// /**
	//  * Make any static data requests to CosMoS and store the results.
	//  * This then clears the data requests so if you need to know if there
	//  * were any data requests, use @see hasDataRequests before calling
	//  * this method
	//  */
	// protected function makeStaticDataRequests()
	// {
	// 	if (count($this->staticDataRequests)>0) {
	// 		$cms = $this->getICmsStaticData();
	// 		$data = $cms->bulkStaticContentRequest($this->staticDataRequests);
	// 		foreach ($data as $i=>$d)
	// 			$this->requestedData[$i] = ($d===null ? 'not found' : $d);
	// 		$this->staticDataRequests = [];
	// 	}
	// }

	/**
	 * Check to see if there are any data requests available
	 * @return boolean
	 */
	public function hasDataRequests()
	{
		return (count($this->staticDataRequests)+count($this->dynamicDataRequests) > 0);
	}

	/**
	 * Set the page retrieved from the database and perform any conversions
	 * @param array $page
	 */
	protected function setPageFromDb($page)
	{
		if ($page) {
			$pageParams = json_decode($page['page_params'] ?? '', true);
			$page['page_params'] = empty($pageParams) ? [] : $pageParams;
			$this->_page = $page;
		}
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
