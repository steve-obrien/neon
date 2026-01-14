<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 23/08/2017
 * Time: 12:40
 */

namespace neon\core\grid;

use neon\core\grid\query\DdsQuery;
use neon\core\grid\query\IQuery;
use \yii\data\BaseDataProvider;


/**
 * @property DdsQuery $queryBuilder
 *
 *
 * Class DdsDataProvider
 * @package neon\core\grid
 */
class DdsDataProvider extends BaseDataProvider
{
	/**
	 * The dds class this data provider should represent
	 * @var string
	 */
	public $classType;

	/**
	 * Whether to include deleted records
	 * @var bool
	 */
	public $includeDeleted = false;

	/**
	 * @var DdsQuery
	 */
	private $_queryBuilder = null;

	/**
	 * On cloning make sure you clone the query builder
	 */
	public function __clone()
	{
		parent::__clone();
		if ($this->_queryBuilder)
			$this->_queryBuilder = clone $this->_queryBuilder;
	}

	/**
	 * @return DdsQuery
	 */
	public function getQueryBuilder()
	{
		if ($this->_queryBuilder === null) {
			$this->_queryBuilder = new DdsQuery();
		}
		return $this->_queryBuilder;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareModels()
	{
		// prepare pagination
		$pagination = $this->getPagination();
		if ($pagination === false)
			return [];
		// depending on how this grid was set up, this call may or may not have been made.
		// The results are cached so call it here to be safe.
		$totalCount = $this->getTotalCount();
		$limit =  [
			'start' => $pagination->getOffset(),
			'length' => $pagination->getLimit(),
			'total' => ($totalCount > 0 ? $totalCount : true)
		];
		// get all result rows
		$filters = $this->getQueryBuilder()->getFilter();
		$includeDeleted = $this->extractIncludeDeletedAndFilter($filters);
		$order = $this->getQueryBuilder()->getOrder();
		// if no order specified order by the latest row created
		if (empty($order)) $order = ['_created' => 'desc'];
		
		$results = neon()->getDds()->IDdsObjectManagement
			->runSingleObjectRequest($this->classType, $filters, $order, $limit, $includeDeleted);

		$pagination->totalCount = $results['total'];
		return $results['rows'];
	}

	/**
	 * @var \neon\core\form\Form
	 */
	private $_ddsForm;

	/**
	 * Get the DDS form for the class type
	 * @return \neon\core\form\Form
	 */
	public function getDdsForm()
	{
		if ($this->_ddsForm === null) {
			$this->_ddsForm = new \neon\core\form\Form(neon()->phoebe->getFormDefinition('daedalus', $this->classType));
		}
		return $this->_ddsForm;
	}

	public function getTotalCount()
	{
		return $this->prepareTotalCount();
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareKeys($models)
	{
		// TODO: Implement prepareKeys() method.
	}

	/**
	 * Store a total count to save multiple recalculations
	 * @var type
	 */
	private $_totalCountCache=[];

	/**
	 * @inheritDoc
	 */
	protected function prepareTotalCount()
	{
		$filters = $this->queryBuilder->getFilter();
		$includeDeleted = $this->extractIncludeDeletedAndFilter($filters);
		$key = md5(serialize($filters));
		if (!isset($this->_totalCountCache[$key])) {
			$results = neon()->getDds()->IDdsObjectManagement
				->runSingleObjectRequest($this->classType, $filters, [], ['start'=>0, 'length'=>0, 'total' => true], $includeDeleted);
			$this->_totalCountCache[$key] = $results['total'];
		}
		return $this->_totalCountCache[$key];
	}

	/**
	 * Checks if the filter implies that we need to return deleted rows and returns true if so.
	 * If false, then the filter is not required so is removed.
	 * @return boolean - true if the filtering on deleted rows
	 */
	public function extractIncludeDeletedAndFilter(&$filters)
	{
		$includeDeleted = false;
		foreach ($filters as $key=>$filter) {
			if ($filter[0] === '_deleted') {
				if ($filter[2] == true) {
					$includeDeleted = true;
				} else {
					unset($filters[$key]);
					$filters = array_values($filters);
				}
				break;
			}
		}
		return $includeDeleted;
	}

}