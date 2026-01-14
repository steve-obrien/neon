<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/08/2017
 * Time: 14:48
 */

namespace neon\core\grid\query;


class BaseQuery
{
	/**
	 * All of the available clause operators.
	 *
	 * @var array
	 */
	public $operators = [
		'=',        // equals or if passed an array an IN => value
		'!=',       // not equals or for array NOT IN => value
		'>',        // greater than,
		'<',        // less than,
		'>=',       // greater or equal to,
		'<=',       // less than or equal to,
		'in',       // value in following array
		'not in',   // value not in following array
		'like',     // value for a text search comparison
		'not like', // value for a text search comparison
		'null',     // set if testing for null [value not required]
		'not null'  // set if testing for not null [value not required]
	];

	/**
	 * Prepare the value and operator for a where clause.
	 *
	 * @param  string  $value
	 * @param  string  $operator
	 * @param  bool  $useDefault
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function prepareValueAndOperator($value, $operator, $useDefault = false)
	{
		if ($useDefault) {
			return [$operator, '='];
		} elseif ($this->invalidOperatorAndValue($operator, $value)) {
			throw new \InvalidArgumentException('Illegal operator and value combination.');
		}

		return [$value, $operator];
	}

	/**
	 * Determine if the given operator and value combination is legal.
	 *
	 * Prevents using Null values with invalid operators.
	 *
	 * @param  string  $operator
	 * @param  mixed  $value
	 * @return bool
	 */
	protected function invalidOperatorAndValue($operator, $value)
	{
		return is_null($value) && ! $this->invalidOperator($operator) &&
			! in_array($operator, ['=', '!=']);
	}

	/**
	 * Determine if the given operator is supported.
	 *
	 * @param  string  $operator
	 * @return bool
	 */
	protected function invalidOperator($operator)
	{
		return ! in_array(strtolower($operator), $this->operators, true);
	}
}