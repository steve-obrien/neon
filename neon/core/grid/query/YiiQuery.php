<?php
namespace neon\core\grid\query;

use \yii\db\ActiveQuery;

class YiiQuery extends BaseQuery implements IQuery
{
	/**
	 * @var \yii\db\ActiveQuery
	 */
	protected $_activeQuery;

	/**
	 * YiiQuery constructor.
	 * @param $activeQuery
	 */
	function __construct($activeQuery)
	{
		$this->_activeQuery = $activeQuery;
	}

	/**
	 * @inheritDoc
	 */
	public function where($column, $operator=null, $value=null)
	{
		// If the column is an array, we will assume it is an array of key-value pairs
		// and can add them each as a where clause. We will maintain the boolean we
		// received when the method was called and pass it into the nested where.
		if (is_array($column)) {
			return $this->addArrayOfWheres($column);
		}
		if ($operator==='=')
			$this->_activeQuery->andWhere([$column => $value]);
		else
			$this->_activeQuery->andWhere([$operator, $column, $value]);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function addOrderBy($column, $order='DESC')
	{
		$this->_activeQuery->addOrderBy([$column => ($order=='DESC') ? SORT_DESC : SORT_ASC]);
	}

	/**
	 * @inheritDoc
	 */
	public function orderBy($column, $order='DESC')
	{
		$this->_activeQuery->orderBy([$column => ($order=='DESC') ? SORT_DESC : SORT_ASC]);
	}

	/**
	 * @inheritDoc
	 */
	public function addArrayOfWheres($columns)
	{
		$this->_activeQuery->andWhere($columns);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function having($column, $operator = null, $value = null)
	{
		$this->_activeQuery->andHaving([$operator, $column, $value]);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function orHaving($column, $operator = null, $value = null)
	{
		$this->_activeQuery->orHaving([$operator, $column, $value]);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getFilter()
	{
		return $this->_activeQuery->createCommand()->rawSql;
	}

	public function count()
	{
		return $this->_activeQuery->count();
	}

	public function __clone()
	{
		$this->_activeQuery = clone $this->_activeQuery;
	}
}