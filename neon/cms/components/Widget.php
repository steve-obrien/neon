<?php

namespace neon\cms\components;


use neon\core\helpers\Arr;
use neon\core\helpers\Str;
use yii\helpers\ArrayHelper;
use neon\cms\components\Daedalus;
use neon\cms\models\CmsStaticContent;
use neon\core\helpers\Html;

class Widget
{
	/**
	 * @var array
	 */
	public $params = [];

	/**
	 * @var Smarty_Internal_Template
	 */
	protected $template;

	/**
	 * The rendering system object
	 * @var neon\cms\components\Renderer
	 */
	protected $renderer = null;

	/**
	 * The required data request for the widget
	 * @var array
	 */
	protected $data = [];

	/**
	 * For dynamic requests, the meta information about the request
	 * Currently 'start','length' and 'total' for pagination. Total is only
	 * set if a request was made for it when 'start' was 0
	 * @var array
	 */
	protected $meta = [];

	/**
	 * The page component this widget is being rendered for
	 * @var neon\cms\components\Page
	 */
	public static $page;

	/**
	 * If true then this is a static content request widget. Otherwise
	 * it's a dynamic daedalus object
	 * @var boolean
	 */
	protected $isStatic = false;


	/**
	 * The widget factory function
	 * Creates a new widget object with appropriate params and tells it to render
	 *
	 * @param array $params
	 *   type: [required] - this is auto-populated with the tag name
	 *   file: [optional] the template file - default widgets/[type].tpl if not specified
	 *   wys|wysiwyg: [optional] - if set then this uses the wysiwyg version of the editor
	 *
	 *  Data request:
	 *  -------------------
	 *  Data requests can be done in one of three ways. These are all used to generate
	 *  a Daedalus request. For full details @see IDdsObjectManagement.
	 *
	 *    1. Site and Page specfic static content - this lets you request page editable
	 *      'static' content using the 'key' and optional 'page' & 'content' parameters. This
	 *      is static content as once editing is complete it doesn't change per view
	 *
	 *      key: [required] - if set this is combined with the type to build a
	 *        data request key from the static data
	 *      page: [optional] - set to true - this further specifies the data request
	 *        key to be page specific
	 *      content: [optional] - if set use this as the default content
	 *
	 *    2. dds_xxx - these let you get hold of page dynamic data - i.e. data that can
	 *      change each time you view the page e.g. lists of people.
	 *
	 *      dds | dds_class | dds_page: [one of these is required]
	 *        this is the dynamic data class you want to use. If you just set dds then this
	 *        assumes the class required is the widget type otherwise it uses the dds_class
	 *        provided. If you just set dds_page then it assumes the dds_class is the type
	 *        and that a page filter is added.
	 *
	 *      dds_page: [optiona] - if set then a page filter is added to the class
	 *        @see Daedalus 'page' attribute for details
	 *
	 *      dds_filter: [optional] - this is a list of filters that can be applied
	 *        to the request
	 *        @see Daedalus 'filter' attribute for details
	 *
	 *     dds_order: [optional] - set the order as a series of fields and direction
	 *        e.g. 'field1':'ASC', 'field2':'DESC'
	 *        @see Daedalus 'order' attribute for details
	 *
	 *     dds_limit: [optional] - set optional 'start', 'length', 'total' sections.
	 *        @see Daedalus 'limit' attribute for details
	 *
	 *   3. Using the data parameter - you can set the data string directly to be passed
	 *     through to Daedalus. You will need to know the @see IDdsObjectManagement
	 *     required syntax to use this directly.
	 *
	 *     This will be overridden by the other methods if you use them.
	 *
	 * @param \Smarty_Internal_Template  $template
	 * @return string
	 */
	public static function factory($params, $template)
	{
		$widget = new static();
		$defaults = [];
		if (isset($params['type'])) {
			$defaults['type'] = $params['type'];
			// path to the file is determined by the theme hierarchy
			$defaults['file'] = $params['type'] . '.tpl';
			if (isset($params['key'])) {
				self::buildDataFromCmsKeyAttributes($params);
			}
 		}
		 
		if (isset($params['dds']) || isset($params['dds_page']) || isset($params['dds_class']))
			Daedalus::buildDataFromDdsAttributes($params);
		$resolvedParams = Arr::merge($defaults, $params);
		return $widget->render($resolvedParams, $template);
	}

	/**
	 * Create a Block Widget and get it to render
	 *
	 * @param $params
	 * @param $content
	 * @param $smarty
	 * @param $repeat
	 */
	public static function factoryBlock($params, $content, &$template, &$repeat)
	{
		$widget = new static();
		if ($content == null) {
			// dont bother doing anything
			return;
		}
		$params['content'] = $content;
		return $widget->render($params, $template);
	}

	/**
	 * Create a static content block widget and get it to render
	 * The default content can be overridden by the user through
	 * the Cobe editor and that content will be used instead if
	 * it is set.
	 *
	 * @param $params
	 *   key - the key that is used to identify this content in the
	 *     static content table.
	 *   wys - if set then wysiwyg is set to this value. If not set
	 *     then this is defaults to true
	 * @param $content
	 * @param $smarty
	 * @param $repeat
	 */
	public static function staticBlock($params, $content, &$template, &$repeat)
	{
		// wait for closing tag so the content (between tags) exists
		if ($repeat == true) return;

		$key = '_scb_' . Arr::getRequired($params, 'key');
		$tag = Arr::get($params, 'tag', 'div');
		$page = Arr::get($params, 'page', true);
		$pageId = neon()->cms->page->getId();

		$filters = ['key' => $key];
		if ($page === true) $filters['page_id'] = $pageId;
		
		// look up static content
		$res = CmsStaticContent::find()->where($filters)->limit(1)->one();
		$content = $res['content'] ?? $content;

		// remove $params we do not want to appear on the html tag
		// this allows other tag params to translate to html attributes (class / style etc)
		foreach (['key', 'page', 'tag'] as $param) {
			unset($params[$param]);
		}

		if (neon()->cms->page->isInEditMode()) {
			// register the js
			\neon\cms\assets\CmsEditorAsset::register(neon()->view);
			// add the attributes to the tag
			$params = array_merge($params, [
				'contenteditable' => true,
				'data-key' => $key,
				'data-page-id' => $pageId,
				'data-page' => $page,
				'data-edit' => true,
				'style' => Arr::get($params, 'style', '') . 'outline:dotted'
			]);
		}

		return Html::tag($tag, $content, $params);
	}

	/**
	 * @return \neon\cms\components\Page
	 */
	public function getPage()
	{
		return neon()->cms->page;
	}

	/**
	 * Render a widget.
	 * The widget checks to see if it has a data request to make. If it does then
	 * it contacts the page and requests the data. If this is available then it
	 * continues to render. If it isn't available it returns a stub placeholder for
	 *
	 * @param $params
	 * @param $template
	 * @return string
	 */
	public function render($params, $template)
	{
		$this->params = $params;
		$this->isStatic = isset($params['is_static']) ? $params['is_static'] : false;
		$this->template = $template;
		$this->renderer = $template->smarty;

		// if we need data and are waiting for it from DDS then we output a
		// placeholder tag and do nothing else
		$data = [];
		if (($dataRequest = $this->getDataRequest()) !== null) {
			$id = $meta = null;
			if (!$this->getPage()->requestWidgetData($dataRequest, $data, $id, $this->isStatic, $meta))
				return $id;
			$this->saveData($data);
			$this->saveDataMetaInformation($meta);
		}
		// so now we have everything we need and we render ourselves (hoorah)
		$ret = $this->doRender();
		return $ret;
	}

	/**
	 * Do the render - this can be overridden in classes that do not require
	 * the renderer to render their template
	 * @return html
	 */
	protected function doRender()
	{
		// store template files that are reused multiple times during a render
		static $templates = [];
		if ($this->isStatic) {
			$content = isset($this->data['content']) ? $this->data['content'] : $this->params['content'];
			return $this->createStaticEditable($content);
		} else {
			$file = $this->params['file'];
			$this->params['hasData'] = count($this->data)>0;
			$this->params['hasResponse'] = true;
			$this->params['data'] = $this->data;
			$this->params['edit'] = $this->createDynamicEditable();
			$this->params['meta'] = $this->meta;

			// createTemplate is an expensive call so only call it once per file and reset all variables on the stored template.
			if (!isset($templates[$file]))
				$templates[$file] = $this->renderer->createTemplate($file);
			$tpl = $templates[$file];
			$tpl->tpl_vars = $tpl->config_vars = [];

			// assign new variables and store results
			$tpl->assign($this->params);
			$out = $tpl->fetch();
			return $out;
		}
	}

	/**
	 * Get the data request for the widget.
	 * Override this if you have a different way of defining your data request
	 * @return array
	 */
	protected function getDataRequest()
	{
		if (isset($this->params['data']))
			return Daedalus::convertDataRequestToJson($this->params['data']);
		return null;
	}

	/**
	 * Save the data for the widget. Override this if you need to do something
	 * else with the data than provided here
	 * @param array $data
	 */
	protected function saveData($data)
	{
		$this->data = $data;
	}

	/**
	 * save any metainformation about the data
	 * @param array $meta
	 */
	private function saveDataMetaInformation($meta)
	{
		$this->meta = $meta;
	}

	/**
	 * @var string the unique id for the widget
	 */
	private $_id;

	public static $autoIdPrefix = 'cobe_widget_';

	public static $counter = 1;

	/**
	 * Get a unique id representing this widget
	 * @return string
	 */
	public function getId()
	{
		if ($this->_id === null) {
			$this->_id = $this->getPage()->getId() . '_' . static::$autoIdPrefix . static::$counter++;
		}
		return $this->_id;
	}

	/**
	 * Create a data request from type and key paramters
	 * This is suitable for requests of static or page specific static content
	 * @param array &$params  the set of params including
	 *   'type' - required - the widget type
	 *   'key' - required - an additional key for the data
	 *   'page' - optional - if set will include current page in key
	 *   'content' - optional - if set, the default content
	 *   This will have a 'data' key added created from the above
	 */
	protected static function buildDataFromCmsKeyAttributes(&$params)
	{
		if (!isset($params['type'], $params['key']))
			return;
		$key = "$params[type]_$params[key]";
		$pageId = isset($params['page']) ? neon()->cms->page->getId() : null;
		$params['data-key'] = $key;
		$params['data-page-id'] = $pageId;
		$params['data'] = "'key':'$key','pageId':'$pageId'";
		if (!isset($params['content']))
			$params['content'] = "Add Content for $params[key] here";
		$params['is_static'] = true;
	}

	/**
	 * Create static editable data from a database key and value
	 *
	 * @param string $key  the static content editable type
	 * @param string $value  the value to be displayed
	 * @return string  the editable version of the data
	 */
	protected function createStaticEditable($value)
	{
		if ($this->getPage()->isInEditMode()) {
			$dataKey = $this->params['data-key'];
			$dataPageId = $this->params['data-page-id'];
			if ((isset($this->params['wys']) || isset($this->params['wysiwyg'])) || (isset($this->params['editor']) && strpos('wys', $this->params['editor'] === 0)))
				return '<div id="'.$this->getId().'" data-id="'.$this->getId().'" data-key="'.$dataKey.'" data-page-id="'.$dataPageId.'" class="cobe-editable cobe-static-block" contenteditable="true">' . $value . '</div>';
			else
				return "<div style='display:inline-block;' class='cobe-editable cobe-static'  data-key='$dataKey' data-page-id='$dataPageId' contenteditable='true' />$value</div>";
		} else {
			return $value;
		}
	}

	/**
	 * Create the editable data ($edit) array for use in
	 * inline editable areas. This must be identical to $this->data if
	 * the page is not being edited, or allow inline editing if the page is being
	 * edited. If you override this make sure that is true.
	 * @return & array  a reference to the array of $edit
	 */
	protected function & createDynamicEditable()
	{
		return Daedalus::createEditable($this->data);
	}
}