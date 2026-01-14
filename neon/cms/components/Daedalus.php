<?php

namespace neon\cms\components;

use neon\core\helpers\Arr;
use \neon\daedalus\services\ddsManager\DdsDataDefinitionManager;

/**
 * This component allows you to make daedalus calls which then returns the results
 * to the template it was requested in. This is different to making a daedalus
 * call on a widget which only supplies the data to the widget
 */
class Daedalus
{
	/**
	 * @var Page
	 */
	private static $_choices = [];
	private static $_maps = [];
	protected $params = [];
	protected $renderer = null;

	// ---------- Daedalus Tags ---------- //

	/**
	 * Make a Daedalus request from within a template and assign to the template
	 *
	 * NOTE - these are requests which means they do not return a result immediately
	 * but once the page has received all the requests and rerenders the page. Therefore
	 * you must check to see if variable['data'] is not null before.
	 *
	 * The assigned parameter has three parts
	 *   ['data'] => the returned data from the database
	 *   ['meta'] => meta information about the data - currently start, length and total
	 *
	 *  - edit no longer exists
	 *   ['edit'] => when in edit mode in the CMS this is editable data within the page
	 *     Use this if you want a user to be able to edit a field within the page directly
	 * You can check 'hasResponse' and 'hasData' to see respectively if the request
	 * has returned and whether or not there was anything returned.
	 *
	 * @param array $params  The parameters needed in the tag
	 *   Usage: assuming tag is dds and where [...] implies optional
	 *
	 * ```
	 *   {dds
	 *     class='class name'
	 *     [page=true | nice_id | page_id]
	 *     [filter=[filters]]
	 *     [order=[order clause]]
	 *     [limit=[limit clause]]
	 *     assign=variable}
	 * ```
	 *   where
	 *      Filter, order and limit are passed in as arrays. (These can also be passed
	 *      in as "almost JSON" strings with single quotes rather than double quotes,
	 *      but that approach is no longer recommended)
	 *
	 *      class: [required] this is the dds class to fetch
	 *      page: [optional] - if set then a page filter is added to the class -if true
	 *        this is the current page, otherwise this can be the nice or page id of an
	 *        alternative page and that will be used
	 *      filter: [optional] - this is a list of filters that can be applied
	 *        to the request - e.g. filter=['field','=','value'] for a single
	 *        filter, or filter=[['field1','=','value1'],['field2','LIKE','val%']]
	 *        for multiple ANDed filters.
	 *
	 *        A filter can also include arbitrary logic as in
	 *        filter=[['field1','=','2','K1'],['field2','>','3','K1'],'logic'=>'K1 AND NOT K2']
	 *
	 *        For more information @see IDdsObjectManagement, or Cobe's documentation
	 *
	 *     order: [optional] - set the order as a series of fields and direction
	 *        e.g. ['field1'=>'ASC', 'field2'=>'DESC']
	 *     limit: [optional] - set optional 'start', 'length', 'total' sections.
	 *        Default is 0 for start and a maximum of DdsCore::MAX_LENGTH.
	 *        If this is just a number then it is assumed to be the length.
	 *        Tf you want a total of possible results, e.g. for pagination, add a
	 *        'total'=>true. The total is calculated only if start is zero
	 *        so store the total for subsequent pages.
	 *        e.g. ['start'=>0, 'length'=>5, 'total'=>true]
	 *
	 *    assign: [required] set the variable you want to contain the returned data
	 *
	 * @param object $renderer  the renderer e.g. smarty
	 * @return
	 */
	public static function dds($params, $renderer)
	{
		(new Daedalus($params, $renderer))->query($params, $renderer);
	}

	/**
	 * Get the available choices for a class member from within in a template
	 *
	 * @param array $params  The array of parameters
	 *   Usage: where [...] implies optional
	 * ```
	 *   {dds_choice
	 *     [class='classType']
	 *     member='[classType::]memberRef'
	 *     [order='true|ASC|DESC']
	 *     assign='variable'}
	 * ```
	 *   where
	 *     member: [required]  the scoped member reference if class is omitted
	 *       or the unscoped member reference if not.  That is you can either
	 *       set this to 'classType::memberRef' or just 'memberRef'. In that
	 *       case you must pass in the classType using the class parameter
	 *     class: [optional]  the class type if the member is not scoped by this
	 *     order: [optional]  if set then order the choice - true or ASC mean by
	 *       by the value field ascending and DESC means descending. Leave off
	 *       if you want it as defined on the class member
	 *     assign: [required]  the name of the variable to hold the results
	 *
	 * @param object $renderer
	 * @return null|error  returns errors if not valid
	 */
	public static function dds_choice($params, $renderer)
	{
		(new Daedalus($params, $renderer))->queryChoice();
	}

	/**
	 * Get the available choices for a class member from within a plugin
	 * @see dds_choice for details. The assign parameter is not required
	 * @return null | array
	 */
	public static function requestChoice($params)
	{
		return (new Daedalus($params))->queryChoice();
	}

	/**
	 * Get the available map for a class in a template
	 *
	 * @param array $params  The array of parameters
	 *   Usage: where [...] implies optional
	 * ```
	 *   {dds_map class='class' assign='variable' [filters=[] flip=true] }
	 * ```
	 *   where
	 *     class: [required]  the class type if the member is not scoped by this
	 *     assign: [required]  the name of the variable to hold the results
	 *     filters: [optional]  any additional field=>value filtering that may be required
	 *     flip: [optional]  if set then assign the transpose of the map
	 *
	 * @param object $renderer
	 * @return null|error  returns errors if not valid
	 */
	public static function dds_map($params, $renderer)
	{
		(new Daedalus($params, $renderer))->queryMap();
	}

	/**
	 * Get the available map for a class from within a plugin
	 * @see dds_map for details. The assign parameter is not required
	 * @return null|array
	 */
	public static function requestMap($params)
	{
		return (new Daedalus($params))->queryMap();
	}

	/**
	 *
	 * @param array $params  any paramters from @see dds and dds_choice
	 * @param object $renderer  the render object
	 */
	public function __construct($params, $renderer=null)
	{
		$this->params = $params;
		$this->renderer = $renderer;
	}

	/**
	 * Make a query to the page and await it's return. Once returned assign to
	 * the requested parameter if we are rendering or as a return parameter
	 * @return null|errors|array  returns errors if not valid, null if called
	 *   from within a template and the data if from within a plugin
	 */
	protected function query($params, $renderer)
	{
		Arr::getRequired($params, 'class', 'You must provide the dds class name');
		// allow popel to use 'filter' or 'filters' key
		$filters = Arr::get($params, 'filters', []);
		$params = Arr::defaults($params, ['filter' => $filters, 'order' => [], 'limit' => [], 'deleted' => false]);
		// limit can be an array of 'start', 'length' as well as 'total'=>true to force a total count.
		// or it can just be an int value - this makes it consistent:
		$limit = (is_array($params['limit']) ? $params['limit'] : ['start' => 0, 'length' => $params['limit']]);

		// do the query
		$data = neon()->dds->IDdsObjectManagement->get(
			$params['class'],
			$params['filter'],
			$params['order'],
			$limit,
		);

		$result = [];
		$rows = Arr::get($data, 'rows');
		$result['hasData'] = count($rows)>0;
		$result['hasResponse'] = true;
		$result['data'] = $rows;
		$result['meta'] = [
			'start' => $data['start'],
			'length' => $data['length'],
			'total' => $data['total'],
		];
		$renderer->assign(Arr::get($params, 'assign', $params['class']), $result);
	}

	/**
	 * get the choices for a particular class member
	 * TODO make this use the page to do the query
	 * Params should contain
	 *   'class' for the object class
	 *   'member' for which member you want the choices of
	 *   These can be combined as 'class::member'
	 * @param boolean $template  if true then assign to the template otherwise
	 *   return the value
	 * @return null|errors|array  returns errors if not valid, null if called
	 *   from within a template and the data if from within a plugin
	 */
	protected function queryChoice($template=true)
	{
		// check usage
		if (!isset($this->params['member']))
			return "Usage: you must provide a 'member' parameter. You provided ".print_r($this->params,true);
		if ($this->renderer && !isset($this->params['assign']))
			return "Usage: you must provide an 'assign' parameter. You provided ".print_r($this->params,true);
		if (strpos($this->params['member'],':') === false && !isset($this->params['class']))
			return "Usage: if you do not scope the member as class::member then you must provide the class parameter";

		// extract out class and member
		$class = null;
		$member = null;
		if (strpos($this->params['member'],':') !== false)
			list ($class, $member) = explode(':', str_replace('::',':',$this->params['member']));
		else {
			$class = $this->params['class'];
			$member = $this->params['member'];
		}

		// now find the results
		// Q. Should this be something that the page should do in conjunction with Daedalus to
		// run all of the queries at one go? Likely to be very few examples on a page though ...
		$key = "$class::$member";
		if (!isset(self::$_choices[$key])) {
			$dds = neon()->dds->IDdsClassManagement;
			$result = $dds->getMember($class, $member, ['choices']);
			if (!empty($result) && isset($result['choices']))
				self::$_choices[$key] = $result['choices'];
			else
				self::$_choices[$key] = [];
		}
		$choices = self::$_choices[$key];
		if (isset($this->params['order'])) {
			if ($this->params['order'] === 'DESC') {
				arsort($choices);
			} else {
				asort($choices);
			}
		}
		if ($this->renderer)
			$this->renderer->assign($this->params['assign'], $choices);
		else
			return $choices;
	}

	/**
	 * get the map for a particular class
	 * TODO make this use the page to do the query
	 * Params should include
	 *   'class' => the class you want to use
	 *   'assign' => what variable you are assigning to if within a template
	 *   'flip' => flip the map (swap keys and values)
	 *   'fields' => set to map different fields from the default map field
	 *     in the order you want them combined.
	 * @return null|errors|data  returns errors if not valid, null if called
	 *   from within a template and the data if from within a plugin
	 */
	protected function queryMap()
	{
		// check usage
		if (!isset($this->params['class']))
			return "Usage: you must provide a 'class' parameter. You provided ".print_r($this->params,true);

		if ($this->renderer && !isset($this->params['assign']))
			return "Usage: you must provide an 'assign' parameter. You provided ".print_r($this->params,true);

		$fields = isset($this->params['fields']) ? $this->params['fields'] : [];

		// extract out class and member
		$class = $this->params['class'];

		// and any filters
		$filters = (!empty($this->params['filters']) ? $this->params['filters'] : []);

		// now find the results
		// Q. Should this be something that the page should do in conjunction with Daedalus to
		// run all of the queries at one go? Likely to be very few examples on a page though ...
		if (!isset(self::$_maps[$class])) {
			$dds = neon('dds')->iDdsObjectManagement;
			$result = $dds->getObjectMap($class, $filters, $fields);
			self::$_maps[$class] = $result;
		}
		$map = self::$_maps[$class];
		if (isset($this->params['flip']))
			$map = array_flip($map);

		if ($this->renderer)
			$this->renderer->assign($this->params['assign'], $map);
		return $map;
	}

	/**
	 * Get the data request from the data string.
	 * Override this if you have a different way of defining your data request
	 * @throws \Exception  If request didn't decode from JSON properly
	 * @return array
	 */
	public static function convertDataRequestToJson($data)
	{
		$request = null;
		$json = trim(str_replace("'", '"', $data));
		if (strlen($json) > 0) {
			// for convenience the beginning and ending brackets are optional so add if not supplied
			if (strpos($json, '{') !== 0)
				$json = '{'.$json.'}';
			$request = json_decode($json, true);
			if ($request === null)
				throw new \Exception("Failing to json decode the data request: $json converted from $data. ");
		}
		return $request;
	}


	/**
	 * Create a data request from dds_xxx provided parameters in $params
	 * This is suitable for requests of dynamic content
	 * These are one of
	 *   dds | dds_class - required
	 *   dds_page - add a page filter set to true for current page or the nice_id
	 *     of another page if you want to filter by that one
	 *   dds_filter - set of filters on the class
	 *   dds_order - ordering for the results
	 *   dds_limit - iterator over the results
	 * @param array &$params  the set of params including the above
	 *   This will have a 'data' key added created from the above
	 */
	public static function buildDataFromDdsAttributes(&$params)
	{
		$dpa = ['dds', 'dds_class', 'dds_page', 'dds_filter', 'dds_order', 'dds_limit'];
		$dps = array_intersect_key($params, array_flip($dpa));
		if (count($dps)) {
			$data = [];
			if ((isset($dps['dds']) || isset($dps['dds_page'])) && empty($dps['dds_class']))
				$dps['dds_class'] = $params['type'];
			if (isset($dps['dds_class']))
				$data[] = "'class':'$dps[dds_class]'";

			// create the filters
			$filters = [];

			// create the page specific filter
			if (!empty($dps['dds_page'])) {
				$pageId = null;
				if ($dps['dds_page'] === true || $dps['dds_page'] === 'true')
					$pageId = neon()->cms->getPage()->getId();
				else {
					// either a nice id or a page id
					// $pageId = self::$page->niceId2PageId($dps['dds_page']);
					// if (!$pageId)
					// SO: commented out the above because niceId2PageId does not exist as a function on the $page
					// object therefore has not worked for a while!
					$pageId = $dps['dds_page'];
				}
				if ($pageId)
					$filters[] = "['page_id','=','$pageId']";
			}

			// add other filters
			if (isset($dps['dds_filter']) && !empty($dps['dds_filter'])) {
				if (is_array($dps['dds_filter']))
					$filters[] = json_encode($dps['dds_filter']);
				// else deprecated json approach
				else if (strpos(trim($dps['dds_filter']), '[')!==0)
					$filters[] = "[$dps[dds_filter]]";
				else
					$filters[] = $dps['dds_filter'];
			}

			// create the filters clause
			if (count($filters))
				$data[] = "'filters':".implode(',',$filters);

			// sort out the order
			if (isset($dps['dds_order'])) {
				if (is_array($dps['dds_order']))
					$data[] = "'order':".json_encode($dps['dds_order']);
				// else deprecated json approach
				else
					$data[] = "'order':{".$dps['dds_order']."}";
			}

			// sort out the limit
			if (isset($dps['dds_limit'])) {
				if (is_numeric($dps['dds_limit']))
					$data[] = "'limit':{'length':$dps[dds_limit]}";
				else if (is_array($dps['dds_limit']))
					$data[] = "'limit':".json_encode($dps['dds_limit']);
				// else deprecated json approach
				else
					$data[] = "'limit':{".$dps['dds_limit']."}";
			}
			$params['data'] = '{'.implode(', ', $data).'}';
		}
	}

	/**
	 * because the component is associated with the smarty tag dds, there is
	 * no need to prefix the request with dds. However in case they do and
	 * because the conversion to data is provided to the widget which does
	 * require the dds_ prefix, we add the prefix here
	 * @param array $params
	 * @return array  the canonicalised params
	 */
	protected function canonicalise($params)
	{
		$canon = [];
		if (empty($params['assign']) && $this->renderer !== null)
			throw new \InvalidArgumentException("You must set an 'assign' parameter when using the 'dds' tag");
		foreach ($params as $k => $v) {
			if ($k == 'assign') {
				$canon[$k] = $v;
				continue;
			}
			$key = strpos($k, 'dds_') === 0 ? $k : 'dds_'.$k;
			$canon[$key] = $v;
		}
		return $canon;
	}

	/**
	 * Create the editable data ($edit) array for use in
	 * inline editable areas. This must be identical to $this->data if
	 * the page is not being edited, or allow inline editing if the page is being
	 * edited. If you override this make sure that is true.
	 *
	 * @return & array  a reference to the array of $edit
	 * @deprecated
	 */
	public static function & createEditable(&$data)
	{
		$edit = [];
		$page = neon()->cms->getPage();
		if ($page->isInEditMode()) {
			foreach ($data as $i => $d) {
				$classType = $d['_class_type'];
				foreach ($d as $field=>$value) {
					// ignore all bookkeeping fields
					if (strpos($field, '_')===0)
						continue;
					$edit[$i][$field] = self::createFieldEditable($classType, $field, $d['_uuid'], $value);
				}
			}
		} else {
			$edit = &$data;
		}
		return $edit;
	}

	/**
	 * Create an editable version of a dynamic data field
	 * @param string $key  the static content editable type
	 * @param string $value  the value to be displayed
	 * @return string  the editable version of the data
	 * @todo: This needs to be deprecated - or fixed somehow - this vue component (neon-cobe-editor) only exists
	 * in an experimental branch based on work by Luke.
	 * It doesn't exist now.
	 * A more robust solution is required to cater for using dynamic data in html attributes (typically anchor href tags)
	 * @deprecated
	 */
	public static function createFieldEditable($classType, $field, $key, $value)
	{
		if (is_array($value))
			return $value;

		/**
		 * This needs to be changed to generate an appropriate form to edit the
		 * required form so a person can edit the appropriate database element.
		 * For now we don't do anything and editing of a daedalus object is
		 * no longer available.
		 */
		return "<neon-cobe-editor value='$value' class-type='$classType' field='$field' uuid='$key'  class='cobe-editable' >$value</neon-cobe-editor>";
	}

}
