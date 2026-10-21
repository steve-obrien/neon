<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Newicon <theteam@newicno.net>
 * @date 12/12/2015 16:13
 */
namespace neon\core\grid\column;

use neon\core\form\interfaces\IField;
use neon\core\grid\GridBase;
use \neon\core\grid\query\IQuery;

interface IColumn
{
	public function __construct($config=[]);

	public function renderDataCell($model, $key, $index, $context='grid');  // GRID: called by grid

	/**
	 * Renders the contents of the cell (can be overriden by setting self::setDataCellContentFunction)
	 *
	 * @param mixed $model array or model
	 * @param string $key - the key of the column
	 * @return string
	 */
	public function renderDataCellContent($model, $key, $index, $context='grid');

	/**
	 * Set a callback to render the cell
	 * the function will receive $model, $key, $index, $column parameters.
	 * Where
	 * - $model: is the current row data or active record (depends on how the gris query parameter is set)
	 * - $key: is the key for the row (typically the id)
	 * - $index: is the index or current position
	 * - $column: the column object instance responsible
	 *
	 * @param mixed $callable a string or a vail callback
	 * @return $this
	 */
	public function setDataCellContentFunction($callable);

	/**
	 * Gets the specified callback to run to render the cell data
	 *
	 * @return callable | string
	 */
	public function getDataCellContentFunction();

	/**
	 * Render the filter cell - this function renders the tag and the contents - e.g. the <th>content</th>
	 * @see $this->renderFilterCellContent that renders the content
	 *
	 * @return string
	 */
	public function renderFilterCell();

	/**
	 * Output the content of the filter cell
	 *
	 * @return string
	 */
	public function renderFilterCellContent();

	/**
	 * Render the header cell - this function renders the tag and the contents - e.g. the <th>content</ht>
	 * @see $this->renderHeaderCellContent that renders the content
	 *
	 * @return string
	 */
	public function renderHeaderCell();

	/**
	 * Render the content of the header cell - typically the heading text
	 *
	 * @return string
	 */
	public function renderHeaderCellContent();

	/**
	 * Pass in a query builder interface object responsible for building the query
	 *
	 * @param IQuery $query
	 */
	public function processSearch(IQuery $query);

	/**
	 * Whether the column has search request data
	 *
	 * @return boolean
	 */
	public function hasRequestData();

	/**
	 * Function for each column to add its order clause
	 *
	 * @param IQuery $query
	 * @param boolean $descending true gives DESC, false gives ASC
	 */
	public function processSort(IQuery $query, $descending);

	/**
	 * Get the parent grid to which this column belongs
	 *
	 * @return GridBase
	 */
	public function getGrid();

	/**
	 * Get the title of this column
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Gets the posted search data for this column
	 * This can be used to filter the data by this column
	 * typical useage:
	 *
	 * ```php
	 * $val = $this->getSearch();
	 * $this->grid->query->andWhere(['id' => $val]);
	 * ```
	 *
	 * @return mixed
	 */
	public function getSearch();

	/**
	 * Called by the parent grid
	 *
	 * @return mixed
	 */
	public function callSearchFunction();

	/**
	 * Set whether or not this column is visible
	 * @param bool $visible
	 * @return $this
	 */
	public function setVisible($visible);

	/**
	 * Get whether or not this column is visible
	 * @return bool
	 */
	public function getVisible();

	/**
	 * Set whether this column is sortable or not
	 * @param bool $sortable
	 * @return $this
	 */
	public function setIsSortable($sortable=true);

	/**
	 * Is the column sortable
	 * @return bool
	 */
	public function getIsSortable();

	/**
	 * Set false to hide the filter
	 *
	 * @param $mixed
	 * @return $this
	 */
	public function setFilter($mixed);

	/**
	 * Get a boolean value indicating whether we should render the filter cell for this column
	 *
	 * @return boolean
	 */
	public function getFilter();

	/**
	 * Get a filter field definition or object to represent the filter for this column
	 * This can be a class definition array or an object implementing IField
	 * Return false if filtering is not possible for this type
	 *
	 * @return array|IField|false
	 */
	public function getFilterField();

	/**
	 * Set any configuration for a filter field. This will depend on the type of
	 * filter that is being used.
	 *
	 * @param array $config see the available config for the relevant filter field type
	 */
	public function setFilterFieldConfig($config);


	/**
	 * Get the data for this cell from the row data
	 * will return the data or null
	 *
	 * @param array $row
	 * @return mixed|null
	 */
	public function getCellData($row);

	/**
	 * returns the string of the field name the column will store its request data in
	 * typically this is just gridId[key]
	 *
	 * @return string
	 */
	public function getFieldName();

	/**
	 * Set the width of the column e.g. '10px'
	 *
	 * @return this
	 */
	public function setWidth($width);

	/**
	 * Get the width of the column
	 *
	 * @return string
	 */
	public function getWidth();

	/**
	 * Set the column key
	 * Usually the key is the same as the database field name in the query.
	 * However for non database columns and in other scenarios you may wish to set
	 * the database field name and the key separately.
	 *
	 * @return this;
	 */
	public function setKey($key);

	/**
	 * Set the column key
	 *
	 * @return string
	 */
	public function getKey();

	/**
	 * Set the grid this column is part of
	 *
	 * @return this
	 */
	public function setGrid($grid);

	/**
	 * Set the index (???)
	 *
	 * @param boolean $bool
	 */
	public function setAsIndex($bool=true);

	/**
	 * Set the title of the column
	 *
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title);

	/**
	 * Set a custom search function
	 * Note you can define a search function using a standard naming convention
	 * if you create a function named searchColumnKey this will be used
	 * e.g.
	 *
	 * ```PHP
	 *  public function init() {
	 *	  $grid->addTextColumn('name');
	 *  }
	 *
	 *  public function searchName($searchValue) {
	 *	 // this function will be called to perform the search
	 *  }
	 * The search function gets passed as its first parameter the searched user data.
	 * Essentially the result of $this->getSearch(); (@see IColumn::getSearch())
	 * You may define as a anonymous function also:
	 *
	 * ```PHP
	 * $grid->addTextColumn('name')->setSearchFunction(function($searchData){
	 *
	 * })
	 * ```
	 *
	 * @param callable $function
	 * @return this
	 */
	public function setSearchFunction($function);

	/**
	 * Get the search function for this column
	 * returns null if a search function has not been specified
	 * Note: a search function can be defined by convention
	 * by prefixing the cell key name with search.
	 * So a column with key "name" ($grid->addTextColumn('name')) would use a search function
	 * defined in the parent grid object called: $grid->searchName
	 *
	 * @return null|callable
	 */
	public function getSearchFunction();  // GRID: called by grid

	/**
	 * Set the database field name. This is used to lookup the row data, and in filters and ordering
	 * If this is not explicitly set the column will assume $this->key is the field name
	 *
	 * @param string $field
	 * @return $this
	 */
	public function setDbField($field);

	/**
	 * The database field name. Defaults to $this->key if not explicitly set
	 *
	 * @return string
	 */
	public function getDbField();

	/**
	 * Set the database sort field name. This is used to sort by the column. If this
	 * is not set then $this->dbField is used. This is rarely needed but may be useful
	 * in mapped columns where the filter is likely based on an index (usually a uuid)
	 * whereas the sorting is based on the map value which may or may not be the
	 * column key.
	 *
	 * @param string $field
	 * @return $this
	 */
	public function setDbSortField($field);

	/**
	 * The database sort field name. Defaults to $this->dbKey if not explicitly set
	 *
	 * @return string
	 */
	public function getDbSortField();

	/**
	 *
	 * @param $model
	 * @param $key
	 * @param $index
	 * @return array
	 */
	public function getDataCellOptions($model, $key, $index);

	/**
	 * Highlight a search string
	 *
	 * @param string $stringToHighlight
	 * @param string $search
	 * @return string - the string with appropriate markup to denote the highlighted portion of the string
	 */
	public function highlight($stringToHighlight, $search);

	/**
	 * Add an action to the column
	 *
	 * @param $key
	 * @param $text
	 * @param array $linkOptions
	 * @return mixed
	 */
	public function addRowAction($key, $text, $linkOptions=[]);

	public function renderRowActions($model, $key, $index);

	/**
	 * This is called after the data has been set on the grid ($grid->dataProvider->prepare())
	 * but before the columns and the grid have rendered any output
	 * Grid data is therefore available to the columns via `$this->getGrid()->dataProvider->getModels()`
	 * which can be used for additional configuration of the grid or the member from live data
	 *
	 * @return void
	 */
	public function prepareDisplay();
}