<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/08/2017
 * Time: 09:33
 */

namespace neon\core\grid\query;

use Faker\Provider\Base;

class DdsQuery extends BaseQuery implements IQuery
{
	/**
	 * Store the filters in appropriate format for DDS
	 *
	 * @see \neon\daedalus\services\ddsManager\interfaces\IDdsObjectManagement
	 * @var array
	 */
	protected $_filters = [];

	/**
	 * @see \neon\daedalus\services\ddsManager\interfaces\IDdsObjectManagement
	 * @var array
	 */
	protected $_order = [];

	/**
	 * @inheritdoc
	 */
	public function where($column, $operator = null, $value = null)
	{
		// handle the case where we have not specified an operator
		// for e.g. `$this->where('attribute', 'value')` in this scenario
		// we assume the equality operator ['attribute', '=', 'value']
		if (func_num_args() == 2) {
			$value = $operator;
			$operator = '=';
		}
		// If the column is an array, we will assume it is an array of key-value pairs
		// and can add them each as a where clause.
		if (is_array($column)) {
			return $this->addArrayOfWheres($column);
		}
		// finally add the filter
		$this->_filters[] = [$column, $operator, $value];
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function addArrayOfWheres($columns)
	{
		foreach($columns as $key => $value) {
			$this->_filters[] = [$key, '=', $value];
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function addOrderBy($column, $order='DESC')
	{
		$this->_order[$column] = $order;
	}

	/**
	 * @inheritDoc
	 */
	public function orderBy($column, $order='DESC')
	{
		$this->_order[$column] = $order;
	}

	/**
	 * Get the filters array definition
	 * This will be in a format suitable for the DDS filters
	 *
	 * @return array
	 */
	public function getFilter()
	{
		return $this->_filters;
	}

	/**
	 * @return array
	 */
	public function getOrder()
	{
		return $this->_order;
	}

	/**
	* @inheritDoc
	*/
	public function having($column, $operator = null, $value = null)
	{
		throw new \Exception('Having clause is currently not supported by DDS - you need to be able to specify custom columns for a start');
	}

	/**
	 * @inheritDoc
	 */
	public function orHaving($column, $operator=null, $value=null)
	{
		throw new \Exception('Having clause is currently not supported by DDS - you need to be able to specify custom columns for a start');
	}
}