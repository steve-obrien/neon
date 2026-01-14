<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @date 1/1/2018 16:13
 */

namespace neon\core\grid\column;

use neon\core\form\fields\LocationMap;
use neon\core\form\interfaces\IField;
use neon\core\helpers\Arr;
use \yii\base\Component;
use \neon\core\grid\Grid;
use yii\data\ActiveDataProvider;
use \yii\helpers\Inflector;
use \yii\helpers\ArrayHelper;
use \neon\core\helpers\Html;
use \neon\core\grid\query\IQuery;

/**
 * Class Column
 * A grid is made of columns which in turn has a header, filter, and data cells
 * for each row of data.  Columns are associated with a form field which is used
 * both for filtering and helping to draw the cell data.
 *
 * @package neon\grid\column
 *
 * @property string $key readonly
 * @property \neon\core\grid\Grid $grid readonly
 * @property boolean $searchable
 * @property string $title
 * @property string $dbField - set this is the database field is different to $key
 * @property string $dbSortField - set this for sorting if the sort field is different to $dbField
 * @property string $emptyCellDisplay - @see getEmptyCellDisplay - string to display when cell content is empty
 * @property IField $member - the corresponding form field component
 */
class Column extends Component implements IColumn
{

	/**
	 * @var \neon\core\form\interfaces\IField
	 */
	private $_member = null;

	/**
	 * Get the form field used to handle the grid columns data
	 * Note: member refers to the name of the form field configuration for the column and not necessarily
	 * a daedalus member
	 * @param IField $member
	 */
	public function setMember(IField $member)
	{
		$this->_member = $member;
	}

	/**
	 * Get the form field member used to handle the columns data, filters, and grid output.
	 * Note: member refers to the name of the form field configuration for the column and not necessarily
	 * a daedalus member
	 * @return IField|null
	 */
	public function getMember()
	{
		return $this->_member;
	}

	/**
	 * Display options for a data cell in this column
	 * @var array
	 */
	public $dataCellOptions = [];

	/**
	 * Configuration options for the column filter object
	 * @var array
	 */
	public $filterFieldConfig = [];

	/**
	 * Html options for the column filter object for use in
	 *   HTML::tag('td', 'content', $filterCellOptions)
	 * @var array
	 */
	public $filterCellOptions = [];

	/**
	 * Display attributes for the header cell
	 * @see setHeaderCellAttributes, getHeaderCellAttributes
	 */
	protected $_headerCellAttributes = [];

	/**
	 * The string to output when the cell content is empty
	 *
	 * @var string
	 */
	private $_emptyCellDisplay = null;

	/**
	 * Get the string to render for the cell if the cells data is empty
	 *
	 * @return string
	 */
	public function getEmptyCellDisplay()
	{
		if ($this->_emptyCellDisplay !== null)
			return $this->_emptyCellDisplay;
		return $this->getGrid()->emptyCellDisplay;
	}

	/**
	 * Set string the column will output when the cell is empty
	 *
	 * @param string $string
	 * @return $this - chainable
	 */
	public function setEmptyCellDisplay($string)
	{
		$this->_emptyCellDisplay = $string;
		return $this;
	}


	/**
	 * Set the header cell attributes
	 * @param array $attributes
	 */
	public function setHeaderCellAttributes($attributes)
	{
		$this->_headerCellAttributes = $attributes;
		return $this;
	}

	/**
	 * Get the header cell attributes
	 */
	public function getHeaderCellAttributes()
	{
		return $this->_headerCellAttributes;
	}

	protected $_width = '';

	/**
	 * @deprecated for @see setWidth
	 */
	public function width($width)
	{
		return $this->setWidth($width);
	}

	/**
	 * Set the column width
	 *
	 * @param string $width
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->_width = $width;
		return $this;
	}

	/**
	 * Get the column width
	 *
	 * @return string
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function __construct($config=[])
	{
		parent::__construct($config);
		if (!isset($config['key'])) {
			throw new \InvalidConfigException('Config must contain "key" property, a string that identified this column $this->key');
		}
		if (!isset($config['grid'])) {
			throw new \InvalidConfigException('Config must contain "grid" property, defining the parent grid this column belongs to');
		}
		$this->_key = $config['key'];
		$this->_grid = $config['grid'];
	}

	/**
	 * Whether or not to show the filter
	 * @var boolean
	 */
	protected $_filter = true;

	/**
	 * @inheritDoc
	 */
	public function getFilter()
	{
		return $this->_filter;
	}

	/**
	 * @inheritDoc
	 */
	public function setFilter($filter)
	{
		$this->_filter = $filter;
		return $this;
	}

	/**
	 * @var string
	 */
	protected $_key;

	/**
	 * @inheritDoc
	 */
	public function setKey($key)
	{
		$this->_key = $key;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getKey()
	{
		return $this->_key;
	}

	/**
	 * A reference to the columns parent Grid object that it belongs to
	 * @var \neon\core\grid\Grid;
	 */
	protected $_grid;

	/**
	 * @inheritDoc
	 */
	public function setGrid($grid)
	{
		$this->_grid = $grid;
		return $this;
	}

	protected $_visible = true;

	/**
	 * @inheritDoc
	 */
	public function setVisible($visible)
	{
		$this->_visible = $visible;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getVisible()
	{
		return $this->_visible;
	}

	/**
	 * @var boolean store the sortable option
	 */
	protected $_sortable = true;

	/**
	 * @inheritDoc
	 */
	public function setIsSortable($sortable=true)
	{
		$this->_sortable = $sortable;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getIsSortable()
	{
		return $this->_sortable;
	}

	public function setAsIndex($bool=true)
	{
		$this->grid->setIndexedByColumn($this->key);
		// thi is a bit gnarly but tests for yii Active record data providers specifically
		if ($this->grid->getDataProvider() instanceof ActiveDataProvider) {
			if ($bool) {
				$this->grid->query->indexBy($this->getDbField());
			} else {
				$this->grid->query->indexBy(null);
			}
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getGrid()
	{
		return $this->_grid;
	}

	/**
	 * Gets the posted search data for this column
	 * This can be used to filter the data by this column
	 * typical useage:
	 *
	 * ```php
	 * // note we make sure request data exists before using it otherwise we get the default empty form value
	 * if ($this->hasRequestData()) {
	 *     $val = $this->getSearch();
	 *     $this->grid->query->andWhere(['id' => $val]);
	 * }
	 * ```
	 *
	 * @return mixed
	 */
	public function getSearch()
	{
		$form = $this->getGrid()->getFilterForm();
		if ($form->hasField($this->getKey())) {
			$value = $form->getField($this->getKey())->getValue();
			return $value;
		}
		return null;
	}

	protected $_dataCellContentFunction;

	/**
	 * @inheritdoc
	 */
	public function setDataCellContentFunction($callable)
	{
		$this->_dataCellContentFunction = $callable;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataCellContentFunction()
	{
		// function has been hard set
		if ($this->_dataCellContentFunction !== null) {
			return $this->_dataCellContentFunction;
		}
		// check for convention
		$functionName = 'render' . $this->key;
		if (method_exists($this->grid, $functionName) && $functionName !== 'renderfile') {
			return $functionName;
		}
		// otherwise render the default column render function
		return [$this, 'renderDataCellContent'];
	}

	/**
	 * @var string
	 */
	protected $_title = null;

	/**
	 * @inheritdoc
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle()
	{
		if ($this->_title === null) {
			$title = Inflector::humanize($this->getKey());
			$this->_title = ucwords(trim($title));
		}
		return $this->_title;
	}


	protected $_searchFunction;

	/**
	 * @inheritDoc
	 */
	public function setSearchFunction($function)
	{
		$this->_searchFunction = $function;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSearchFunction()
	{
		if ($this->_searchFunction === null) {
			$searchFunction = 'search'.ucwords($this->key);
			if (method_exists($this->grid, $searchFunction)) {
				$this->_searchFunction = $searchFunction;
			}
		}
		return $this->_searchFunction;
	}

	/**
	 * The query field used for this column for sorting, filtering
	 * and getting the value. If this isn't set, key is used. If you require
	 * sorting over a separate field @see setDbSortField
	 * @var string
	 */
	protected $_dbField;

	/**
	 * @inheritDoc
	 */
	public function setDbField($field){
		$this->_dbField = $field;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getDbField(){
		if ($this->_dbField === null){
			// set to be the key
			$this->_dbField = $this->key;
		}
		return $this->_dbField;
	}

	/**
	 * The query field used for sorting. If this isn't set then dbField is
	 * used. If that isn't set then key is used
	 * @var string
	 */
	protected $_dbSortField;

	/**
	 * @inheritDoc
	 */
	public function setDbSortField($field){
		$this->_dbSortField = $field;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getDbSortField(){
		if ($this->_dbSortField === null){
			// set to be the key
			$this->_dbSortField = $this->dbField;
		}
		return $this->_dbSortField;
	}

	/**
	 * @var array
	 */
	protected $_style = [];

	/**
	 * Set header css styles
	 * @param array $styles
	 * @return $this
	 */
	public function setHeaderStyle(array $styles)
	{
		$this->_style = $styles;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function renderHeaderCell()
	{
		$options = $this->getHeaderCellAttributes();
		if ($this->grid->hasSort($this->key))
			Html::addCssClass($options, ($this->grid->hasSortDescending() ? 'desc' : 'asc'));
		if (trim($this->width) != '')
			Html::addCssStyle($options, ["width" => $this->width]);
		Html::addCssStyle($options, $this->_style);

		return Html::tag('th', $this->renderHeaderCellContent(), $options);
	}

	/**
	 * @inheritdoc
	 */
	public function renderHeaderCellContent()
	{
		if (!$this->isSortable)
			return $this->getTitle();
		$sort = '';
		if ($this->grid->hasSort($this->key)) {
			$sort = $this->grid->hasSortDescending() ? ' - ' : ' + ';
		}
		$sortParam = $this->grid->hasSortDescending() ? $this->key : '-' . $this->key;
		$url = neon()->getUrlManager()->createUrl([\Yii::$app->controller->getRoute(), $this->grid->id => ['sort' => $sortParam]]);
		return Html::a($this->getTitle() . $sort, $url, ['data-sort'=>$sortParam]);
	}

	/**
	 * @inheritdoc
	 */
	public function renderFilterCell()
	{
		$content = '';
		if ($this->filter !== false) {
			$content = $this->renderFilterCellContent();
		}
		if (trim($this->width) != '') {
			Html::addCssStyle($this->filterCellOptions, "width:{$this->width};");
		}
		return Html::tag('td', $content, $this->filterCellOptions);
	}

	/**
	 * @inheritdoc
	 */
	public function renderFilterCellContent()
	{
		if ($this->filter === false)
			return '';
		if (!$this->getGrid()->getFilterForm()->hasField($this->getKey()))
			return '';
		$field = $this->getGrid()->getFilterForm()->getField($this->getKey());
		// remove any automatic validators
		$field->setValidators([], true);
		return $field->run();
	}

	/**
	 * Get the base definition of the filter field.
	 * Returns the array definition of a form field, an instance of a form field (implementing IField)
	 * or a boolean false to indicate no filtering.
	 * @return array|false|IField
	 */
	protected function getBaseFilterField()
	{
		if ($this->member instanceof IField) {
			return $this->member->getFilterField();
		}
		return ['class' => 'text', 'name' => $this->getKey()];
	}


	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		$field = $this->getBaseFilterField();
		foreach($this->filterFieldConfig as $key => $value)
			$field[$key] = $value;
		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function setFilterFieldConfig($config)
	{
		$this->filterFieldConfig = $config;
		return $this;
	}

	public function setMemberConfig($config)
	{
		foreach($config as $key => $value) {
			if (isset($this->member->$key))
				$this->member->$key = $value;
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCellData($row, $noValue='-')
	{
		$data = null;
		$key = $this->getDbField();
		// If dbField is defined as table.field then this field will never exist in the row array (returned select statement columns)
		// therefore in a complex query often the key will be the column name returned in the SELECT statement
		// and db field set to something like table.field to avoid ambigious filter and enable filters in COUNT(*) statements.
		if (isset($row[$key])) {
			$data = $row[$key];
		} else if (isset($row[$this->key])) {
			$data = $row[$this->key];
		} else {
			return $noValue;
		}
		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataCellOptions($model, $key, $index)
	{
		$options = $this->dataCellOptions;
		if (trim($this->width) != '') {
			Html::addCssStyle($options, "width:{$this->width};");
		}
		return $options;
	}

	/**
	 * @inheritdoc
	 */
	public function renderDataCell($model, $key, $index, $context='grid')
	{
		$cellContent = $this->callRenderDataCellContentFunction($model, $key, $index, $context='grid');
		// if row actions:
		$cellContent .= $this->renderRowActions($model, $key, $index);
		$options = $this->getDataCellOptions($model, $key, $index);
		$cell = Html::tag('div', $cellContent, array_merge($options, ['class'=>'neonGrid_cell']));
		return Html::tag('td', $cell, $options);
	}

	/**
	 * Calls the render function and passes in the current row $model, $key, $index
	 *
	 * @param $model
	 * @param $key
	 * @param $index
	 * @return mixed
	 * @throws \Exception
	 */
	public function callRenderDataCellContentFunction($model, $key, $index, $context='grid')
	{
		if (($cellFunc = $this->getDataCellContentFunction())) {
			if (is_string($cellFunc)) {
				return $this->grid->$cellFunc($model, $key, $index, $context, $this);
			} else {
				return call_user_func($cellFunc, $model, $key, $index, $context, $this);
			}
		}
		throw new \Exception('Unable to call a valid data cell render function');
	}

	/**
	 * @inheritdoc
	 */
	public function renderDataCellContent($model, $key, $index, $context='grid')
	{
		if ($this->getMember() instanceof IField) {
			$cellData = $this->getCellData($model, null);
			$this->member->setValueFromDb($cellData);
			$display = $this->member->getValueDisplay($context);
			if ($display === null || $display === []) { return $this->emptyCellDisplay; }
			if (is_array($display)) {
				$display = json_encode($display);
			}
			$out = $this->highlight($display, $this->getSearch());
			return "<span title=\"".str_replace('"',"'", html_entity_decode(strip_tags($display)))."\">$out</span>";
		}
		return $this->getCellData($model);
	}

	/**
	 * @inheritDoc
	 */
	public function getFieldName()
	{
		return $this->getGrid()->getId() . '[' . $this->key . '][search]';
	}

	/**
	 * @inheritDoc
	 */
	public function highlight($stringToHighlight, $search)
	{
		return Html::highlight($search, $stringToHighlight);
	}

	/**
	 * @inheritdoc
	 */
	public function processSort(IQuery $query, $descending)
	{
		$sort = $descending ? 'DESC' : 'ASC';
		$query->orderBy($this->dbSortField, $sort);
	}

	/**
	 * @inheritDoc
	 */
	public function processSearch(IQuery $query)
	{
		$postVal = $this->getSearch();
		$form = $this->getGrid()->getFilterForm();
		if ($form->hasField($this->getKey())) {
			$form->getField($this->getKey())->processAsFilter($query, $postVal);
		}
	}

	/**
	 * Whether search request data exists for the column
	 * @return bool
	 */
	public function hasRequestData()
	{
		$filterForm = $this->getGrid()->getFilterForm();
		return isset($_REQUEST[$this->getGrid()->getId()][$filterForm->getName()][$this->getKey()]);
	}

	/**
	 * @inheritDoc
	 */
	public function callSearchFunction()
	{
		if (($search = $this->getSearchFunction())) {
			return $this->callFunction($search, [$this->getSearch(), $this->getGrid()->getQueryBuilder()]);
		}
		throw new \Exception('Unable to call a valid search function');
	}

	protected $_actions =[];

	/**
	 * @inheritDoc
	 */
	public function addRowAction($key, $text, $linkOptions=[])
	{
		$linkOptions['text'] = $text;
		$linkOptions['data-action'] = $key;
		$linkOptions['href'] = '';
		$linkOptions['data-pjax'] = 0;
		$this->_actions[$key] = $linkOptions;
	}

	public function addRowActionLink($text, $function)
	{
		$linkOptions['text'] = $text;
		$linkOptions['function'] = $function;
		$this->_actions[$text] = $linkOptions;
	}

	/**
	 * @inheritDoc
	 */
	public function renderRowActions($model, $key, $index)
	{
		if (empty($this->_actions)) {
			return '';
		}
		$out = '<div class="neonGrid_rowActions">';
		$i = 0;
		foreach($this->_actions as $actionKey => $linkOptions) {
			if ($i > 0) $out .= ' | ';
			if (isset($linkOptions['function'])) {
				$func = $linkOptions['function'];
				$out .= $this->callFunction($func, [$model, $key, $index]);
			} else {

				if ($this->getGrid()->hasIndexColumn()) {
					$key = $this->getGrid()->getIndexColumn()->getKey();
					$linkOptions['data-index'] = isset($model[$key]) ? $model[$key] : '';
				}

				$text = ArrayHelper::remove($linkOptions, 'text');
				$out .= Html::tag('a', $text, $linkOptions);
			}
			$i++;
		}
		$out .= '</div>';
		return $out;
	}

	/**
	 * @param mixed $function if a string then assumes this is a string function on the parent grid.
	 * Can be a callable function or  array [$object, 'functionName']
	 * @param array $arguments of arguments
	 * @return mixed
	 */
	public function callFunction($function, $arguments=[])
	{
		if (is_string($function)) {
			return call_user_func_array([$this->grid, $function], $arguments);
		} else {
			return call_user_func_array($function, $arguments);
		}
	}

	/**
	 * @inheritdoc
	 * Currently the column will respect dataMapFilters applied to the columns form field member
	 * Note: member only refers to the name of the form field configuration for the column and not necessarily
	 * a daedalus member
	 */
	public function prepareDisplay()
	{
		// limit the column results to respect any mapFilters
		if (isset($this->member->dataMapFilters)) {
			$models = $this->getGrid()->dataProvider->getModels();
			// extract out non-empty column values only, flatten the data so just an array of keys and remove any duplicates
			$columnData = array_unique(Arr::flatten(array_filter(Arr::getColumn($models, $this->key))));
			if (count($columnData)) {
				$this->member->dataMapFilters = ['uuid' => $columnData];
			}
			foreach($models as $model) {
				$this->member->makeMapRequest($this->getCellData($model, null));
			}
		}
	}
}
