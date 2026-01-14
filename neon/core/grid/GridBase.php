<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015-18 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @date 12/12/2015 16:13
 */

namespace neon\core\grid;

use neon\core\form\Form;
use neon\core\grid\column\Column;
use neon\core\grid\column\IColumn;
use neon\core\helpers\Arr;
use neon\core\helpers\Hash;
use neon\core\interfaces\IProperties;
use neon\core\traits\PropertiesTrait;
use neon\core\web\View;
use yii\base\Component;
use yii\base\InvalidConfigException;
use \yii\helpers\ArrayHelper;
use \neon\core\grid\query\IQuery;
use ReflectionClass;

/**
 * The Grid class inherit from this class to create your own advanced neon Grids
 *
 * The grid goes through 4 main stages to render:
 * 1: $this->init(): function is called, you can add columns and settings but its cleaner to use the configure function
 * 2: $this->configure(): function is called, this is where you can configure the columns by adding them to the grid
 * (3: Deprecated $this->load($_REQUEST): data pertinent to the grid is loaded. Typically this is the request data)
 * 4: $this->setup(): the setup function is called, this allows customisation of the grid and columns based on the request data
 * 5: $this->run(): Finally run processes and renders the grid
 *
 * See the self::gridBoot function for more information
 *
 * If you want properties and filters to persist then make them public properties in child classes for example:
 * ```php
 * class MyGrid extends Grid {
 *      public $projectId;
 *      public function setup() {
 *          // add query
 *          $this->getQueryBuilder()->where('project', '=', $this->$projectId);
 *      }
 * }
 * ```
 * The grid data persists between requests and setup will be called multiple times - therefore make sure the query
 * is built fresh with each call to setup.
 *
 * @package neon\core\grid
 * @author Steve O'Brien <steve@newicon.net>
 *
 * @property \yii\data\DataProviderInterface $dataProvider
 * @property string $id - the grid ID @see $this->getId()
 */
abstract class GridBase extends Component implements IProperties
{
	use PropertiesTrait;

	/**
	 * @inheritdoc
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->configure();
		if (!$this->title) {
			$reflect = new ReflectionClass($this);
			$this->title = $reflect->getShortName();
		}
	}

	/**
	 * @var array the configuration for the pager widget. By default, [[LinkPager]] will be
	 * used to render the pager. You can use a different widget class by configuring the "class" element.
	 * Note that the widget must support the `pagination` property which will be populated with the
	 * [[\yii\data\BaseDataProvider::pagination|pagination]] value of the [[dataProvider]].
	 * @var \yii\widgets\LinkPager
	 */
	public $pager = ['hideOnSinglePage' => false];

	/**
	 * Whether to stripe odd/even rows
	 * @var bool
	 */
	public $stripedRows = true;

	/**
	 * Allows to specify a theme name for the grid
	 * This simply adds the theme string as a css class to the base theme element
	 * @var string
	 */
	public $theme = '';

	/**
	 * Set whether the grid fills the width of the container or can overflow
	 * @var boolean
	 */
	public $fillWidth = false;

	/**
	 * TODO: this should not be accessible by the grid directly - use the data provider
	 * child classes can "know" that the dataprovider has a query object and therefore access it this way
	 * You can use $this->grid->getDataProvider()->query or use the getDataProvider()->getQueryBuilder() for standard
	 * filter options
	 * @deprecated
	 * @var \yii\db\ActiveQuery
	 */
	public $query;

	/**
	 * @var \yii\data\ActiveDataProvider
	 */
	protected $_dataProvider;

	/**
	 * Whether we have a data provider
	 * @return bool
	 */
	public function hasDataProvider()
	{
		return $this->_dataProvider === null ? false : true;
	}

	/**
	 * Store column objects
	 * @var array
	 */
	protected $_columns = [];

	/**
	 * Store filters
	 * @var array
	 */
	protected $_scopes = [];

	/**
	 * The view file to use to render the grid body
	 * **NOTE** this can be overridden using the theme pathMap property
	 * Overriding this property in sub classes should be a last resort
	 * @var string
	 */
	protected $_viewBodyFile = '@neon/core/grid/views/body.tpl';

	/**
	 * A unique id for this grid
	 * it will be used to attach request data to
	 * @var string
	 */
	private $_id;

	/**
	 * The name of the grid.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The header title for the grid
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Configures the search url the grid will call this by ajax.
	 * To return correctly the controller action must return the Grid Html by
	 *   simply instantiating the grid and echoing the run command
	 * Minimum controller required for an example MyNeonGrid:
	 *
	 * ```php
	 * public function actionGrid()
	 * {
	 *	  $grid = new MyNeonGrid();
	 *		echo $grid->run();
	 * }
	 * ```
	 *
	 * @var array|string route e.g. ['/controller/index/grid']
	 */
	public $filterUrl = '/core/grid/filter';

	/**
	 * DDS filter format - a placeholder for columns to manipulate filters
	 *
	 * @var array
	 */
	public $filters = [];

	/**
	 * @var IQuery
	 */
	public $queryBuilder = null;

	/**
	 * Store the query builder object for the grid
	 *
	 * @var IQuery
	 */
	protected $_queryBuilder = null;

	/**
	 * Specify whether or not this grid can export a CSV
	 * @var bool
	 */
	public $canExportCsv = true;

	/**
	 * The string to display in a cell when empty
	 * @var string
	 */
	public $emptyCellDisplay = '-';

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		/**
		 * Return all the properties that are needed for generic rendering
		 * or exporting of this grid in any form.
		 */
		return [
			'id', 'pageSize', 'name', 'title', 'canExportCsv',
			'theme', 'emptyCellDisplay', 'stripedRows', 'filterUrl'
		];
	}

	/**
	 * Get the query builder object responsible for building search queries for columns
	 *
	 * @return IQuery
	 */
	public function getQueryBuilder()
	{
		return $this->getDataProvider()->getQueryBuilder();
	}

	/**
	 * Set the query builder object
	 *
	 * @param IQuery $queryBuilder
	 */
	public function setQueryBuilder(IQuery $queryBuilder)
	{
		$this->_queryBuilder = $queryBuilder;
	}

	/**
	 * The current scope the grid will render
	 * if not defined then it will use the first scope defined in the $this->_scopes array
	 *
	 * @var string the scope key
	 */
	protected $_scope;

	/**
	 * The key of the default scope for the grid.
	 * The grid will apply this scope if no request scope exists
	 * If this is not defined the grid will apply the first scope defined in $this->_scopes
	 *
	 * @var string
	 */
	public $defaultScope;

	/**
	 * Set the pagination page number
	 */
	public function setPageNumber()
	{
		$page = neon()->request->post($this->id);
		if (isset($page['page'])) {
			$this->getDataProvider()->pagination->setPage($page['page']);
		}
	}

	/**
	 * Set the pagination page size
	 *
	 * @param int $size
	 */
	public function setPageSize($size)
	{
		$this->getDataProvider()->pagination->pageSize = $size;
	}

	/**
	 * Get hold of the current pagination size
	 */
	public function getPageSize()
	{
		return $this->getDataProvider()->pagination->pageSize;
	}

	/**
	 * Get a scope key string
	 * This returns the active scope the grid must process
	 * This is calculated from either request data, a defaultScope parameter or the first in the scopes list
	 * returns null if a scope is not found
	 *
	 * @return string|null
	 */
	public function getScope()
	{
		if (empty($this->_scopes)) {
			return null;
		}
		// If a scope on the request has been set
		// _scope property exists
		$postScope = $this->_scope;
		$scope = ArrayHelper::getValue($this->_scopes, $postScope);
		if ($scope) {
			return $scope['key'];
		}
		// No url request scope defined check if a default is set
		if ($this->defaultScope !== null)
			return $this->defaultScope;

		// No default scope so return the first in the list as the default
		$defaultScope = reset($this->_scopes);
		return $defaultScope['key'];
	}

	/**
	 * @deprecated - this function is no longer needed
	 */
	public function load()
	{}

	/**
	 * Whether the specified scope is the currently the active one
	 * Note that request data must have been loaded - therefore this function will not work
	 * when used inside the init function
	 *
	 * @param string $scopeKey
	 * @return bool
	 */
	public function isScopeActive($scopeKey)
	{
		return ($this->getScope() == $scopeKey);
	}

	/**
	 * Does the grid have the scope defined
	 * @param string $scopeKey
	 * @return boolean
	 */
	public function hasScope($scopeKey)
	{
		return isset($this->_scopes[$scopeKey]);
	}

	/**
	 * Set the active scope
	 *
	 * @param string $scope
	 */
	public function setScope($scope)
	{
		$this->_scope = $scope;
	}

	/**
	 * Get the array of scope definitions
	 *
	 * @return array
	 */
	public function getScopes()
	{
		return $this->_scopes;
	}

	/**
	 * Add a filter scope - typically "deleted" | "active" etc
	 *
	 * @param string $key
	 * @param string $name
	 * @param callable $function
	 */
	public function addScope($key, $name, $function=null)
	{
		$this->_scopes[$key] = compact('key', 'name', 'function');
	}

	/**
	 * Whether to show the filters
	 *
	 * @var bool
	 */
	public $filterShow = true;

	/**
	 * Chainable method to set filterShow property
	 *
	 * @param boolean $bool
	 * @return $this
	 */
	public function filterShow($bool)
	{
		$this->filterShow = $bool;
		return $this;
	}

	/**
	 * The default sort (adopt yii terminology)
	 * columnKey
	 *
	 * @var string
	 */
	public $sort;

	/**
	 * A private cache of the grid data
	 *
	 * @var array
	 */
	private $_gridData = [];

	/**
	 * Store the form object responsible for each columns filter field
	 *
	 * @var null|\neon\core\form\Form
	 */
	protected $_filterForm = null;

	/**
	 * Get a form object representing all the filters
	 * This calls each column and asks it kindly for a form field object
	 * that it suitable to render its filter input control
	 *
	 * @throws \Exception on incorrect column config
	 * @return Form
	 */
	public function getFilterForm()
	{
		if (empty($this->getColumns())) {
			throw new \Exception('No grid columns have been defined yet.  The filter form is built from the grid columns, make sure you have setup your grid columns before loading request data into the form');
		}
		if ($this->_filterForm === null) {
			$this->_filterForm = (new Form($this->getId()))
				->setId($this->getId().'Filter')
				->addSubForm('filter');
			foreach($this->getColumns() as $key => $column) {
				if ($column->getFilter() && $column->getFilterField() !== false) {
					$field = $column->getFilterField();
					if (is_object($field))
						$field = $field->toArray();
					$field['name'] = $key;
					$field['dataKey'] = $column->getDbField();
					$this->_filterForm->add($field);
				}
			}
		}
		return $this->_filterForm;
	}

	/**
	 * Return the original grid data that has been sent to the grid
	 *
	 * @return array
	 */
	public function getGridData()
	{
		return $this->_gridData;
	}

	/**
	 * This function is called before request data is loaded (and before setup is ran)
	 * This is equivalent of putting code into the init function
	 * This should be used to add columns and grid settings
	 */
	public function configure()
	{
	}

	/**
	 * Function called as the first step of the run process
	 * at this point any relevant request data should have been loaded typically which scope is active
	 * therefore the setup of the grid can be altered based on post data
	 * Note for filter data to be stored in the filter form the column objects must first be initialized
	 * (as the filter form is created from the columns on the grid)
	 * Its best to add columns in the init function and use the setup to tweak them
	 * you can access an existing column via $this->getColumn($key); or store references in the subclass
	 *
	 * @return void
	 */
	public function setup()
	{
	}

	/**
	 * Returns the grid name that this model class should use.
	 *
	 * The grid name is mainly used by [[\yii\widgets\ActiveForm]] to determine how to name
	 * the input fields for the attributes in a model. If the form name is "A" and an attribute
	 * name is "b", then the corresponding input name would be "A[b]". If the form name is
	 * an empty string, then the input name would be "b".
	 *
	 * By default, this method returns the model class name (without the namespace part)
	 * as the form name. You may override it when the model is used in different forms.
	 *
	 * @return string the grid name of this class.
	 * @throws \Exception
	 */
	public function gridName()
	{
		$reflector = new ReflectionClass($this);
		return $reflector->getShortName();
	}

	/**
	 * Get an id for the grid
	 *
	 * @return string
	 * @throws \Exception if no id has been set
	 */
	public function getId()
	{
		if ($this->_id === null) {
			$this->_id = $this->gridName();
		}
		return $this->_id;
	}

	/**
	 * Set the grid id, the id is used as a key to attach request data to
	 * and to identify the html block for the grid when rendering
	 *
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * Set a bunch of columns by an array definition
	 *
	 * ```php
	 * new Grid(['columns'=>[
	 *     'name' => [
	 *         'class' => 'neon\core\grid\column\Text',
	 *         'title' => 'First Name',
	 *         'dbField' => 'first_name'
	 *     ]
	 * ]])
	 * ```
	 * @throws \Exception on incorrect column config
	 * @param array $columns containing ['key' => ['class' => 'column class', ...]]
	 */
	public function setColumns($columns)
	{
		foreach($columns as $key => $config) {
			$this->addColumn($key, $config);
		}
	}

	/**
	 * Add a text column to the grid
	 *
	 * @param string $key
	 * @param array $config
	 * @throws \Exception on incorrect column config
	 * @return \neon\core\grid\column\Text
	 */
	public function addTextColumn($key, $config=[])
	{
		$config['class'] = isset($config['class']) ? $config['class'] : 'neon\core\grid\column\Text';
		return $this->addColumn($key, $config);
	}

	/**
	 * Add a Date column to the grid
	 *
	 * @param string $key
	 * @param array $config
	 * @throws \Exception on incorrect column config
	 * @return \neon\core\grid\column\Date
	 */
	public function addDateColumn($key, $config=[])
	{
		$config['class'] = isset($config['class']) ? $config['class'] : 'neon\core\grid\column\Date';
		return $this->addColumn($key, $config);
	}

	/**
	 * Add a int column to the grid
	 *
	 * @param string $key
	 * @param array $config
	 * @throws \Exception on incorrect column config
	 * @return \neon\core\grid\column\IntColumn
	 */
	public function addIntColumn($key, $config=[])
	{
		$config['class'] = isset($config['class']) ? $config['class'] : 'neon\core\grid\column\IntColumn';
		return $this->addColumn($key, $config);
	}

	/**
	 * Add a check column to the grid
	 *
	 * @param $key
	 * @param array $config
	 * @return \neon\core\grid\column\Check
	 * @throws \Exception
	 */
	public function addCheckColumn($key, $config=[])
	{
		$config['class'] = isset($config['class']) ?  $config['class'] : 'neon\core\grid\column\Check';
		return $this->addColumn($key, $config);
	}

	/**
	 * Add an action column to the grid
	 *
	 * @param $key
	 * @param $config
	 * @throws \Exception on incorrect column config
	 * @return \neon\core\grid\column\Button
	 */
	public function addButtonColumn($key, $config=[])
	{
		$config['class'] = isset($config['class']) ?  $config['class'] : 'neon\core\grid\column\Button';
		return $this->addColumn($key, $config);
	}

	/**
	 * Create a column based on its field definition
	 *
	 * @param string $key - a key for the column
	 * @param string $class - the class reference
	 * @param string $member - the member reference string typically from Daedalus.
	 *   If this is null, then the key is used as the member
	 * @param string $phoebeType - the phoebe type for the form definition. Defaults to 'daedalus'
	 * @throws \Exception
	 * @return Column
	 */
	public function addColumnFromDefinition($key, $class, $member=null, $phoebeType='daedalus')
	{
		static $formsDefinitionCache=[];
		$cacheKey = $class.$phoebeType;
		if (!array_key_exists($cacheKey, $formsDefinitionCache)) {
			$formDefinition = neon('phoebe')->getFormDefinition($phoebeType, $class);
			$formsDefinitionCache[$cacheKey] = $formDefinition;
		}
		$form = new \neon\core\form\Form($formsDefinitionCache[$cacheKey]);
		if ($member === null)
			$member = $key;
		return $this->addColumn($key, [
			'title' => $form->getField($member)->getLabel(),
			'class' => 'neon\core\grid\column\Column',
			'member' => $form->getField($member),
		]);
	}

	/**
	 * @var array
	 */
	protected $_columnGroups = [];

	/**
	 * @return array
	 */
	public function getColumnGroups()
	{
		return $this->_columnGroups;
	}

	/**
	 * @param $array
	 */
	public function setColumnGroups($array)
	{
		$this->_columnGroups = $array;
	}

	public function addColumnField($key, $formFieldClass)
	{
		$this->addColumn($key, [
			'class' => Column::class,
			'member' => $formFieldClass
		]);
	}

	/**
	 * Add a column to the grid
	 *
	 * @param string $key - the internal key for the column will also be used as the key to look up the value in the
	 * row data. If you need this to be different then you can set the dbField option of the column to specify the
	 * precise key in the column data to return
	 * @param array $config
	 * @return \neon\core\grid\column\Column
	 * @throws \Exception if no `$config['class']` is set
	 */
	public function addColumn($key, $config=[])
	{
		if (!isset($config['class'])) {
			// 99% of the time you just want the standard Column class
			$config['class'] = \neon\core\grid\column\Column::class;
		}
		$class = Arr::remove($config, 'class');
		unset($config['class']);
		$this->_columns[$key] = \Neon::createObject(array_merge([
			'class' => $class,
			'key' => $key,
			'grid' => $this
		], $config));
		return $this->_columns[$key];
	}

	/**
	 * Remove a column from the grid
	 *
	 * @param $key
	 */
	public function removeColumn($key)
	{
		if ($this->columnExists($key))
			unset($this->_columns[$key]);
	}

	/**
	 * Set the data provider used to get the data
	 *
	 * @param \yii\data\BaseDataProvider $provider
	 */
	public function setDataProvider($provider)
	{
		$this->_dataProvider = $provider;
	}

	/**
	 * Get the grids Data provider object
	 *
	 * @return \yii\data\ActiveDataProvider
	 */
	abstract public function getDataProvider();

	/**
	 * Get the columns.
	 * Returns an array of column objects implementing IColumn interface key => column object
	 *
	 * @return \neon\core\grid\column\IColumn[]
	 */
	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * Whether the grid has any data set
	 *
	 * @return bool
	 */
	public function hasData()
	{
		return ! empty($this->_gridData);
	}

	/**
	 * We must refresh the data provider before calling scopes
	 * This removes scopes that have been added previously but keeps initial setup query conditions
	 */
	public function refreshDataProvider()
	{
		if ($this->_dataProvider)
			$this->_dataProvider = clone $this->getDataProvider();
	}

	/**
	 * The main render function to draw the grid and run the search etc
	 * @throws \Exception
	 * @return string
	 */
	public function run()
	{
		$this->gridBoot();
		$this->setPageNumber();
		$this->registerAssets();
		// executes the query and stores the data results in models accessible via `$this->getDataProvider()->getModels()`
		$this->getDataProvider()->prepare();
		// enable columns to prepare for display (i.e. load maps based on current result set)
		$this->prepareDisplay();
		return $this->getGridRenderer()->doRender();
	}

	/**
	 * @throws InvalidConfigException
	 */
	public function gridBoot()
	{
		if (! $this->hasData())
			$this->_gridData = Arr::get($_REQUEST, $this->id, null);
		// load the active scope
		$this->_scope = Arr::get($this->_gridData, "scope", []);
		// load request data into the form
		// note: columns should have already been added to the grid otherwise the form will be empty
		$this->getFilterForm()->load(Arr::get($this->_gridData, "filter", []));
		// enable the grid to configure columns visibility and filters based on active scopes and request data
		$this->setup();
		// force the data provider to load (only necessary when this is a subclass of YiiGridView
		$this->getDataProvider();
		$this->refreshDataProvider();
		$this->processActionsOnSelected();
		$this->processScopes();
		$this->processSearch();
		$this->processSort();
	}

	/**
	 * Return the string url to the export link
	 * @return string
	 */
	public function exportCsvUrl()
	{
		return url(['/core/grid/export-csv', 'token' => Hash::setObjectToToken($this)]);
	}

	/**
	 * Export the grid data in a format suitable for CSV
	 * @param int $page  the starting page in the data
	 * @param int $pageSize  the number of rows in a page. Maximum 1000.
	 * @param bool $asRows  false if you want the data imploded into a string
	 *   and true if you want the data returned as an array
	 * @return string | array  depending on value of $asRows
	 */
	public function getCsvData($page=0, $pageSize=1000, $asRows=false)
	{
		// check we're allowed to do this
		if (!$this->canExportCsv)
			return null;

		$this->gridBoot();
		// Get header column names
		$headerCols = [];
		$columns = $this->getColumns();

		foreach ($columns as $colKey => $column) {
			$headerCols[] = $this->prepareCellContentForCSV($column->getTitle());
		}
		// For some reason excel throws an SYLK file error if the first cell is ID uppercase

		if (isset($headerCols[0]) && $headerCols[0] == 'ID') {
			$headerCols[0] = 'Id';
		}

		// Render the rows
		$dataProvider = $this->getDataProvider();
		$pagination = $dataProvider->pagination;
		$pagination->page=$page;
		$pagination->pageSize = min(1000,$pageSize);
		$models = $dataProvider->getModels();
		if (empty($models))
			return null;

		$rows = [];
		foreach($models as $index => $row) {
			$singleRow = [];
			// use the columns set as not all columns exists in row data e.g. derived ones
			foreach ($columns as $colKey => $column) {
				$singleRow[] = $this->prepareCellContentForCSV($column->callRenderDataCellContentFunction($row, $colKey, 0, 'csv'));
			}
			$rows[] = implode(',', $singleRow);
		}

		// clear the models to prevent ooming
		$dataProvider->models = null;

		// Add headers columns as the first row if we are on the first page
		if ($page==0)
			array_unshift($rows, implode(',', $headerCols));

		return $asRows ? $rows : implode("\n", $rows);
	}

	/**
	 * Get a grid renderer object
	 */
	public function getGridRenderer()
	{
		return new GridRenderer($this);
	}

	/**
	 * Prepare the columns for display
	 */
	public function prepareDisplay()
	{
		foreach ($this->getColumns() as $column) {
			$column->prepareDisplay();
		}
	}

	/**
	 * Checks if there are selected row actions and runs them
	 */
	public function processActionsOnSelected()
	{
		// run actions on any selected rows
		// TODO: tidy up running actions
		if (isset($_POST[$this->id]['action']) && isset($_POST[$this->id]['action_index']) && $_POST[$this->id]['action'] != 'undefined') {
			$rows = explode(',', $_POST[$this->id]['action_index']);
			foreach ($rows as $id) {
				call_user_func_array(array($this, $_POST[$this->id]['action']), array($id));
			}
		}
	}

	/**
	 * Applies scope filter criteria
	 * @throws \Exception
	 */
	public function processScopes()
	{
		$this->_processScopeCounts();
		// apply default scope
		if (!empty($this->getScopes())) {
			$activeScope = $this->getScope();
			// remove previous scopes added
			$scope = ArrayHelper::getValue($this->getScopes(), $activeScope);
			if ($scope) {
				// before calling a scope we will tell the query builder to label any rules added
				// by the scope - this allows us to remove them later
				$this->callScope($scope, $this->getQueryBuilder());
			}
		}
	}

	private $_scopeCounts = [];

	/**
	 * Perform a count for each scope and store ready for rendering.
	 * Populates $this->_scopeCounts with total count for each scope.
	 * This function must be called before applying the default scope otherwise we cannot easily remove the
	 * default scope from the query builder object. Thus ending up with inaccurate counts
	 *
	 * Note - counts are extremely expensive, and can take a long time to return
	 * for database tables with items in the millions of rows especially if joins
	 * are involved. So counts should not be made more than once if possible.
	 *
	 * @throws \Exception if no scope function exists for specified key
	 */
	private function _processScopeCounts()
	{
		foreach ($this->getScopes() as $key => $scope) {
			// counts across whole tables are very very expensive. Don't count more than once
			if (isset($this->_scopeCounts[$key]))
				continue;
			$dp = clone $this->getDataProvider();
			// call the scope function defined by `'scope'.$key` functions in child classes
			$this->callScope($scope, $dp->getQueryBuilder());
			// reset cached counts
			$dp->setTotalCount(null);
			$this->_scopeCounts[$key] = $dp->getTotalCount();
		}
	}

	/**
	 * return the count for a given scope key
	 * @param string $key
	 * @return int
	 */
	public function getScopeTotalCount($key)
	{
		return isset($this->_scopeCounts[$key]) ? $this->_scopeCounts[$key] : '?';
	}

	/**
	 * Checks search data and applied correct filter modifications to the query/filter object
	 *
	 * @throws InvalidConfigException
	 */
	public function processSearch()
	{
		$data = $this->getGridData();
		if (!isset($data['filter']))
			return;
		foreach ($this->getColumns() as $key => $column) {
			// check there is a form filter field
			// check that the filter field has request data
			if ($column->getFilter() && $column->hasRequestData()) {
				// if there is valid search data:
				if ($column->getSearchFunction() !== null) {
					$column->callSearchFunction();
				} else {
					$column->processSearch($this->getQueryBuilder());
				}
			}
		}
	}

	/**
	 * Attach sort information to the data provider.
	 * Typically a column will be identified to be sorted, this information can be
	 * found out from `$this->getSortInfo()`
	 *
	 * @see getSortInfo()
	 */
	public function processSort()
	{
		list ($columnKey, $desc) = $this->getSortInfo();
		if ($columnKey && $this->columnExists($columnKey)) {
			// lookup the column db field to apply the sorting to
			$column = $this->getColumn($columnKey);
			$column->processSort($this->getQueryBuilder(), $desc);
		}
	}

	/**
	 * Whether a column currently has a sort set
	 *
	 * @param $columnKey
	 * @return bool
	 */
	public function hasSort($columnKey)
	{
		list ($columnSort, $desc) = $this->getSortInfo();
		return $columnSort == $columnKey;
	}

	/**
	 * Whether the current sort is in descending order
	 *
	 * @return mixed
	 */
	public function hasSortDescending()
	{
		list ($columnSort, $desc) = $this->getSortInfo();
		return $desc;
	}

	/**
	 * example useage:
	 *
	 * ```PHP
	 *	 list($columnKey, $descending) = $this->getSortInfo();
	 * ```
	 * Note that the column key (the first key in the returned array) will be null
	 * if there is currently no sort
	 *
	 * @return array first key columnKey the second a boolean representing descending
	 */
	public function getSortInfo()
	{
		$sort = ArrayHelper::getValue($this->getGridData(), 'sort');
		$descending = false;
		if (strncmp($sort ?? '', '-', 1) === 0) {
			$descending = true;
			$sort = substr($sort, 1);
		}
		return [$sort, $descending];
	}

	/**
	 * @param $columnKey
	 * @return \neon\core\grid\column\Column
	 * @throws \Exception
	 */
	public function getColumn($columnKey)
	{
		if (!isset($this->_columns[$columnKey]))
			throw new \Exception("No column with key '$columnKey' exists in the grid");
		return $this->_columns[$columnKey];
	}

	/**
	 * Check a column exists
	 * @param string $columnKey - the column key
	 * @return bool
	 */
	public function columnExists($columnKey)
	{
		return isset($this->_columns[$columnKey]);
	}

	/**
	 * Calls the scope function to action the scope filter
	 *
	 * @param array $scope the scope array containing ['function' => ..., 'key' => '...']
	 * @param IQuery $query
	 * @throws \Exception
	 */
	public function callScope($scope, IQuery $query)
	{
		if ($scope['function'] == null) {
			$func = 'scope' . ucfirst($scope['key']);
			if ($this->hasMethod($func, false)) {
				call_user_func(array($this, $func), $query);
			} else {
				throw new \Exception("A scope function with name '$func' is not defined in the grid class.");
			}
		} else {
			call_user_func($scope['function'], $query);
		}
	}

	/**
	 * Get the grid row data
	 *
	 * @return array
	 */
	public function getRows()
	{
		return $this->getDataProvider()->getModels();
	}

	public function getFilterUrl()
	{
		$filterUrl = is_array($this->filterUrl) ? $this->filterUrl : [$this->filterUrl];
		$filterUrl['token'] = Hash::setObjectToToken($this);
		return url($filterUrl);
	}

	/**
	 * Registers client assets
	 */
	protected function registerAssets()
	{
		GridAssets::register(neon()->view);
		$options = [];
		$options['filterUrl'] = $this->getFilterUrl();
		neon()->view->registerJs('(new Vue()).$mount("#'.$this->id.'");', View::POS_END, $this->id.'vue');
		neon()->view->registerJs('$("#'.$this->id.'").neonGrid('.json_encode($options).')', View::POS_READY, $this->id.'neonGrid');
	}

	/**
	 * Gets a debug result message showing the query performed to
	 * fetch the current set of grid results
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function showQuery()
	{
		return json_encode($this->getQueryBuilder()->getFilter());
	}

	/**
	 * Get an array of only visible columns
	 * format:
	 * ```
	 * [
	 *	 ['key' => {column object}],
	 * ]
	 * ```
	 *
	 * @return array
	 */
	public function getColumnsVisible()
	{
		$columns = [];
		foreach($this->getColumns() as $key => $column) {
			if ($column->visible)
				$columns[$key] = $column;
		}
		return $columns;
	}

	/**
	 * When outputting the object as a string call the run function
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->run();
	}

	/**
	 * Key of the column this grid is indexed by
	 *
	 * @var string
	 */
	protected $_indexedByColumn;

	/**
	 * Set a column as the index of the grid
	 * An index column assumes that the value in of that column can be used to find the row of data from the data store
	 * This is useful for grid actions passing the value of that rows index column - the row id to the action
	 *
	 * @param string $columnKey
	 * @throws \Exception if no column exists with the $columnKey in the $this->_columns array
	 */
	public function setIndexedByColumn($columnKey)
	{
		if (!isset($this->_columns[$columnKey])) {
			throw new \Exception("No column exists with key '$columnKey'");
		}
		$this->_indexedByColumn = $columnKey;
	}

	/**
	 * Get the column that has been set as the index
	 * @return IColumn
	 * @throws \Exception - if no index column exists
	 */
	public function getIndexColumn()
	{
		if (!isset($this->_columns[$this->_indexedByColumn]))
			throw new \Exception("No index column exists with key '$this->_indexedByColumn'.");
		return $this->_columns[$this->_indexedByColumn];
	}

	/**
	 * Whether ter is a column on the grid that has been set to be the index
	 * @return bool
	 */
	public function hasIndexColumn()
	{
		return isset($this->_columns[$this->_indexedByColumn]);
	}

	/**
	 * Replace field tags in a string with the values from the row of data.  A field tag is denoted with a leading colon.
	 * For example a row containing ['name' => 'steve'] and a string of 'hello {$name}' will give 'hello steve'
	 *
	 * @param string|array $string - a string or array of strings containing field tags e.g. ':field'
	 * @param array $row - The row data
	 * @return string
	 */
	public function replaceRowTagsWithValues($string, $row)
	{
		$tags = $this->getRowSearchTags($row);
		return str_replace(array_keys($tags), array_values($tags), $string);
	}

	/**
	 * Generates an array of search tags based on the keys of the row
	 * A tag is simply the key of a row item wrapped with a particular string
	 * It assumes that each row in the grid will have a consistent keys
	 *
	 * @param array $row
	 * @return array
	 */
	protected function getRowSearchTags($row)
	{
		$tags = [];
		foreach ($row as $key => $val) {
			if (!is_array($val)){
				$tags['{{' . $key . '}}'] = $val;
			}
		}
		return $tags;
	}

	/**
	 * Prepare the content for CSV export by removing tags, html entities etc
	 * @param string $content  the content to be converted
	 */
	protected function prepareCellContentForCSV($content)
	{
		/**
		 * From experimentation with real data:
		 * grid cells and headers can have html tags added
		 * To export properly to csv, any html tags need to be removed and
		 * html entities reverted. These can leave newlines in the data so
		 * need to clear those out too.
		 */
		$plain = html_entity_decode(
				str_replace(["\n", '"'], ['', '\''], strip_tags($content)),
				ENT_HTML401 | ENT_QUOTES | ENT_HTML5);
		if ($plain === $this->emptyCellDisplay)
			$plain = '';
		return '"'.$plain.'"';
	}
}
