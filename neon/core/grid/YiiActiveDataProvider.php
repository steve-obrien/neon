<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 23/08/2017
 * Time: 12:40
 */

namespace neon\core\grid;

use neon\core\grid\query\DdsQuery;
use neon\core\grid\query\YiiQuery;
use yii\data\ActiveDataProvider;

/**
 * @property DdsQuery $queryBuilder
 *
 *
 * Class DdsDataProvider
 * @package neon\core\grid
 */
class YiiActiveDataProvider extends ActiveDataProvider
{
	private $_queryBuilder;

	/**
	 * @return YiiQuery
	 */
	public function getQueryBuilder()
	{
		if ($this->_queryBuilder === null) {
			$this->_queryBuilder = new YiiQuery($this->query);
		}
		return $this->_queryBuilder;
	}

	/**
	 * On cloning make sure you clone the query builder
	 */
	public function __clone()
	{
		parent::__clone();
		if ($this->_queryBuilder)
			$this->_queryBuilder = clone $this->_queryBuilder;
	}
}