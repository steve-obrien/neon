<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\cms;

use neon\cms\models\CmsPage;
use \neon\core\BaseApp;
use \neon\cms\components\CmsUrlRule;
use neon\core\helpers\Hash;
use neon\core\interfaces\IDataMapProvider;
use neon\core\helpers\Str;
/**
 * The neon cms app class
 *
 * @property \neon\cms\components\Page $page
 * @property \neon\cms\services\cmsManager\CmsManager $ICmsStaticData 
 */
class App extends BaseApp
implements IDataMapProvider
{
	public $requires = 'dds';

	/**
	 * @var array of roles that can edit page content via cobe
	 */
	public $editingRoles = ['neon-administrator'];

	/**
	 * @throws \yii\base\InvalidConfigException
	 * @inheritdoc
	 */
	public function configure()
	{
		$this->set('page', [
			'class' => '\neon\cms\components\Page'
		]);
		$this->set('ICmsStaticData', [
			'class' => '\neon\cms\services\cmsManager\CmsManager'
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		// Add the url rule to map urls to pages
		neon()->urlManager->rules[] = \Neon::createObject(CmsUrlRule::class);
		neon()->urlManager->addRules([
			'sitemap.xml' => 'cms/render/sitemap'
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{
		// add menu item (to be deprecated)
		return [
			'label' => $this->getName(),
			'order' => 1100,
			'url' => ['/cms/index/index'],
			'active' => is_route('/cms/*'),
		];
	}

	/**
	 * Get the name for this app
	 * @return string
	 */
	public function getName()
	{
		return 'CMS';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [
			'google_code' => [
				'label' => 'Google Code',
				'hint' => 'Set the google analytics or tag manager code - this will install the google scripts.  For tag manager add the code that looks like: "GTM-XXXX".
				Use the UA-XXX code or other Google product codes if you are nit using tag manager
				If you need more control of the scripts installed then leave this field blank and copy the code into the script blocks below',
			],
			'canonical' => [
				'class' => '\neon\core\form\fields\UrlLink',
				'label' => 'Global Canonical tag domain',
				'hint' => 'Set the global URL to use in the canonical tag for this site so search engines know the proper URL to index and don\'t serve duplicate content.
				All site pages will use this global canonical tag URL as their starting point and append the page\'s unique URL slug after it. [Learn more about canonical tags](https://moz.com/learn/seo/canonicalization).',
				'placeholder' => 'e.g. domain.com'
			],
			'head'=>[
				'label' =>	'Code to be inserted before the </head> tag on every page',
				'class' => '\neon\core\form\fields\Ide'
			],
			'body'=>[
				'label' =>	'Code to be inserted just before the closing </body> tag on every page',
				'class' => '\neon\core\form\fields\Ide'
			],
			'favicon' => [
				'label' =>	'Favicon',
				'hint' => 'Upload a square image for the favicon. Use the ```{favicon}``` tag to render the html in the ```<head>``` part of the document',
				'class' => \neon\core\form\fields\Image::class,
				'crop'=>true,
				'cropWidth'=>1,
				'cropHeight'=>1
			]
		];
	}

	/**
	 * Get the page component
	 * @return \neon\cms\components\Page
	 */
	public function getPage()
	{
		return $this->get('page');
	}

	/**
	 * 
	 */
	public function getPageData()
	{
		return $this->getPage()->getPageData();
	}

	/**
	 * request an ICmsStaticData interface
	 * @return \neon\cms\services\cmsManager\interfaces\ICmsStaticData
	 */
	public function getICmsStaticData() {
		return $this->get('ICmsStaticData');
	}

	/** Setting and Getting the Theme information **/
	/***********************************************/

	/**
	 * Get the current theme name
	 * Will return the current theme if no theme hierarchy was set
	 * or the top item in the theme hierarchy if set
	 * @return string
	 */
	public function getThemeName()
	{
		return $this->_themeName;
	}

	/**
	 * Set the theme name for site
	 * This can be done via the 'cms' => 'themeName' configuration information
	 * @param string $name  must be alphanumeric and _ - only
	 * @throws \InvalidArgumentException  if the name is not valid
	 */
	public function setThemeName($name)
	{
		$this->checkThemeName($name);
		$this->_themeName = $name;
	}
	private $_themeName = 'default';


	/** Setting and Getting site information **/
	/******************************************/

	/**
	 * @return array  information about the site
	 */
	public function getSiteData()
	{
		return $this->_siteData;
	}

	/**
	 * set site specific information
	 * This can be set via the 'cms' => 'siteData' adding
	 * whatever parameters are required.
	 * @param array $siteData
	 */
	public function setSiteData($siteData)
	{
		$this->_siteData = $siteData;
	}
	private $_siteData = [];

	/**
	 * path alias to the theme directory
	 * @return string
	 */
	public function getThemeAlias()
	{
		return '@root/themes/' . $this->getThemeName();
	}

	/**
	 * Get the basepath to the cms theme directory
	 * @return bool|string
	 */
	public function getThemeBasePath()
	{
		return neon()->getAlias($this->getThemeAlias());
	}

	/**
	 * Gets a public accessible url to the theme asset
	 * (Any file stored in the theme/assets folder)
	 * 
	 * @param string $path relative path from the theme assets folder
	 * @return string url to the asset
	 */
	public function themeAssetUrl($path)
	{
		// loading external resource so just return
		if (Str::startsWith($path, 'http')) return $path;
		if (Hash::isUuid64($path)) {
			return neon()->firefly->getUrl($path);
		}
		list($assetPath, $assetUrl) = $this->publishThemeAssets();
		$path = ltrim($path ?? '', '/');
		$assetUrl = ltrim($assetUrl, '/');
		$timestamp = @filemtime("$assetPath/$path");
		return '/'.$assetUrl.'/'.$path.(($timestamp > 0) ? '?v='.$timestamp : '');
	}

	/**
	 * Publish the current theme assets
	 * 
	 * @return array 0 => path and 1 => the url is was published as
	 */
	public function publishThemeAssets()
	{
		$themeAssets = neon()->cms->getThemeAlias().'/assets';
		return neon()->assetManager->publish($themeAssets);
	}

	/**
	 * @deprecated user parent publishAssets function
	 * Publish this modules assets directory
	 * @return array
	 */
	public function assets()
	{
		return neon()->assetManager->publish('@neon/cms/assets');
	}
	/**
	 * @deprecated user parent publishAssets function
	 * @return mixed
	 */
	public function assetsUrl()
	{
		list($path, $url) = $this->assets();
		return $url;
	}
	/**
	 * @deprecated user parent publishAssets function
	 * @return mixed
	 */
	public function assetsPath()
	{
		list($path, $url) = $this->assets();
		return $path;
	}

	/**
	 * @inheritdoc
	 * @unsupported - $filters is not currently supported
	 */
	public function getDataMap($key, $query='',  $filters=[], $fields=[], $start = 0, $limit = 100)
	{
		if ($key === 'pages') {
			return $this->getPagesMap($query, $filters, $fields, $start, $limit);
		}
	}

	/**
	 * @var string[] - string requestKey to array of makeMapLookupRequest arguments
	 */
	private $_mapRequestKey = null;

	/**
	 * @inheritdoc
	 */
	public function getMapResults($requestKey)
	{
		if (!isset($this->_mapRequestKey[$requestKey]))
			throw new \InvalidArgumentException('The provided request key "'.$requestKey.'" does not exist');
		list($key, $ids, $fields) = $this->_mapRequestKey[$requestKey];
		if ($key === 'pages') {
			$pages = CmsPage::find()->select(array_merge(['id', 'nice_id', 'title'], $fields))
				->where(['id' => $ids])
				->orderBy('nice_id')
				->asArray()
				->all();
			return $this->formatPageMapResults($pages, $fields);
		}
		return [];
	}

	/**
	 * Format a list of page rows into a map
	 * @param array $pages - assoc db rows
	 * @return array - maps typically contain an id to a single string value however if additional fields have been
	 *   defined then the map needs to return the id => array of values keyed by field name
	 *   for e.g.
	 * ```php
	 *  // when additional $fields have been requested:
	 *  ['uuid' => [
	 *      'field1' => 'value',
	 *      'field2' => 'value2',
	 *   ]]
	 *
	 *  // when no additional fields defined:
	 *  ['uuid' => 'nice_id: title'],
	 * ```
	 *   Essentially there is a dual responsibility.
	 *   An advanced select box could specify additional map fields additional filters and even display the data in a
	 *   different format. However we want the maps to work well with no additional configuration
	 */
	public function formatPageMapResults($pages, $fields)
	{
		return collect($pages)->mapWithKeys(function ($item) use($fields) {
			if (empty($fields))
				return [$item['id'] => "$item[title]  (nice=$item[nice_id])"];
			return [$item['id'] => $item];
		})->all();
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($key, $ids, $fields = [])
	{
		$requestKey = md5(serialize(func_get_args()));
		$this->_mapRequestKey[$requestKey] = func_get_args();
		return $requestKey;
	}

	/**
	 * Perform a search to find a map - used for map searches - dropdown displays
	 * where a string search can be used as well as fixed array defined filters.
	 * @see \neon\core\form\fields\SelectDymaic
	 * @see \neon\core\form\fields\SelectDymaic\Link
	 * @see \neon\core\interfaces\IDataMapProvider::getDataMap()
	 * Note this is not used directly to perform the getDataMap but it is used specifically to provide the map for
	 * cms pages (the getDataMap interface supports multiple maps per object)
	 * @param string $query - a string based search
	 * @param array $filters - an array of search criteria key => value
	 * @param array $fields
	 * @return array - @see $this->formatPageMapResults();
	 */
	public function getPagesMap($query=null, $filters=[], $fields=[], $start=0, $limit=100)
	{
		$pages = CmsPage::find()
			->select(array_merge(['id', 'nice_id', 'title'], $fields))
			->where(['or', ['like', 'nice_id', $query], ['like', 'title', $query]])
			->andWhere($filters)
			->offset($start)
			->limit(100)
			->orderBy('nice_id')
			->asArray()
			->all();
		return $this->formatPageMapResults($pages, $fields);
	}

	/**
	 * @inheritdoc
	 */
	public function getDataMapTypes()
	{
		return [
			'pages' => 'Pages'
		];
	}

	/** ********** Private and Protected Methods ********** **/

	/**
	 * check a theme name is valid
	 * - consists of alphanumeric and _- chars only
	 * @param string $name
	 * @throws \InvalidArgumentException
	 */
	private function checkThemeName($name)
	{
		if ( preg_match('/[^A-Za-z0-9_-]/', $name, $matches) ) {
			throw new \InvalidArgumentException("Error trying to set the theme name to '$name'"
				."\n"."The theme name must consist of only 'a-z', '0-9', '_' and '-' characters."
				."\n".'The following character is not allowed in theme names: ' . \yii\helpers\VarDumper::dumpAsString($matches[0]) );
		}
	}

}
