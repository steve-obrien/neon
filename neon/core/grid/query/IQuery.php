<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/08/2017
 * Time: 11:35
 */

namespace neon\core\grid\query;

/**
 * Class IQueryBuilder
 *
 * This class is responsible for providing a standard interface for query building allowing columns and the grid
 * to specify search criteria in a standardised way - this can then be converted into the relevant formats such as
 * SQL or a DDS filter etc
 *
 * @package neon\core\grid
 */
interface IQuery
{
	/**
	 * Set a where search filter on a column
	 *
	 * Supported calls:
	 *
	 * ```php
	 * $this->where('email', 'person@newicon.net');
	 * // is equivalent to:
	 * $this->>where('email', '=', 'person@newicon.net')
	 * // and equivalent to:
	 * $this->where(['email' => 'person@newicon.net']);
	 * ```
	 *
	 * @param mixed $column - column name or array of column keys and values
	 * @param mixed $operator - string operator @see IDdsObjectManagement
	 * or if it's not an operator assumes it's the columns value with a simple equality operator ('=')
	 * @param mixed $value
	 * @return $this - provide a fluent chainable interface
	 */
	public function where($column, $operator=null, $value=null);

	/**
	 * Add an array of key (column) => values as simple equality filters
	 *
	 * @param array $columns
	 * @return IQuery
	 */
	public function addArrayOfWheres($columns);

	/**
	 * Add an order by clause to existing clause
	 *
	 * @param string $column
	 * @param string $order - 'ASC' | 'DESC'
	 */
	public function addOrderBy($column, $order='DESC');

	/**
	 * Sets the order by clause to existing clause
	 *
	 * @param string $column
	 * @param string $order - 'ASC' | 'DESC'
	 */
	public function orderBy($column, $order='DESC');

	/**
	 * Add a having clause to the query builder
	 * Having clause is run after group by operations and useful for querying custom db columns
	 *
	 * @param mixed $column - column name or array of column keys and values
	 * @param mixed $operator - string operator @see IDdsObjectManagement
	 * or if it's not an operator assumes it's the columns value with a simple equality operator ('=')
	 * @param mixed $value
	 * @return $this - provide a fluent chainable interface
	 */
	public function having($column, $operator=null, $value=null);

	/**
	 * Create an orHaving clause
	 * @param mixed $column - column name or array of column keys and values
	 * @param mixed $operator - string operator @see IDdsObjectManagement
	 * or if it's not an operator assumes it's the columns value with a simple equality operator ('=')
	 * @param mixed $value
	 * @return $this - provide a fluent chainable interface
	 */
	public function orHaving($column, $operator=null, $value=null);

	/**
	 * return information on the current filters applied - for mysql this may be the raw query string - for
	 * filter objects this might be a filter definition array
	 * @return array|string
	 */
	public function getFilter();
}