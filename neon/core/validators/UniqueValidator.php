<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/01/2018
 * @package neon
 */

namespace neon\core\validators;

use neon\core\interfaces\IProperties;
use neon\core\traits\PropertiesTrait;
use yii\validators\UniqueValidator as YiiValidator;

class UniqueValidator extends YiiValidator implements IProperties
{
	use PropertiesTrait,
		ValidationTrait {
		ValidationTrait::toArray insteadof PropertiesTrait;
	}

	public function validateValue($value)
	{
		/* @var $targetClass ActiveRecordInterface */
		$targetClass = $this->targetClass;
		$targetAttribute = $this->targetAttribute;
		$conditions = $this->applyTableAlias($targetClass::find(), [$targetAttribute => $value]);
		$alreadyExists = $this->modelExists($targetClass, $conditions, null);
		if ($alreadyExists) {
			return ['This already exists', []];
		}
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateAttribute($model, $attribute)
	{
		/* @var $targetClass ActiveRecordInterface */
		$targetClass = $this->targetClass;
		$targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
		$this->prepareConditions($targetAttribute, $model, $attribute);

		$alreadyExists = $this->modelExists($targetClass, [], null);

		if ($alreadyExists) {
			$this->addError($model, $attribute, $this->message);
		}
	}

	private function prepareConditions($targetAttribute, $model, $attribute)
	{
		if (is_array($targetAttribute)) {
			$conditions = [];
			foreach ($targetAttribute as $k => $v) {
				$conditions[$v] = is_int($k) ? $model->getField($v)->getValue() : $model->getField($k)->getValue();
			}
		} else {
			$conditions = [$targetAttribute => $model->$attribute];
		}

		$targetModelClass = $this->targetClass;
		if (!is_subclass_of($targetModelClass, 'yii\db\ActiveRecord')) {
			return $conditions;
		}

		/** @var ActiveRecord $targetModelClass */
		return $this->applyTableAlias($targetModelClass::find(), $conditions);
	}

	/**
	 * Checks whether the $model exists in the database.
	 *
	 * @param string $targetClass the name of the ActiveRecord class that should be used to validate the uniqueness
	 * of the current attribute value.
	 * @param array $conditions conditions, compatible with [[\yii\db\Query::where()|Query::where()]] key-value format.
	 * @param Model $model the data model to be validated
	 *
	 * @return bool whether the model already exists
	 */
	private function modelExists($targetClass, $conditions, $model)
	{
		/** @var ActiveRecordInterface|\yii\base\BaseObject $targetClass $query */
		$query = $this->prepareQuery($targetClass, $conditions);
		return $query->exists();
	}

	private function prepareQuery($targetClass, $conditions)
	{
		$query = $targetClass::find();
		$query->andWhere($conditions);
		if ($this->filter instanceof \Closure) {
			call_user_func($this->filter, $query);
		} elseif ($this->filter !== null) {
			$query->andWhere($this->filter);
		}
		return $query;
	}

	private function applyTableAlias($query, $conditions, $alias = null)
	{
		if ($alias === null) {
			$alias = array_keys($query->getTablesUsedInFrom())[0];
		}
		$prefixedConditions = [];
		foreach ($conditions as $columnName => $columnValue) {
			if (strpos($columnName, '(') === false) {
				$columnName = preg_replace('/^' . preg_quote($alias) . '\.(.*)$/', '$1', $columnName);
				if (strpos($columnName, '[[') === 0) {
					$prefixedColumn = "{$alias}.{$columnName}";
				} else {
					$prefixedColumn = "{$alias}.[[{$columnName}]]";
				}
			} else {
				// there is an expression, can't prefix it reliably
				$prefixedColumn = $columnName;
			}

			$prefixedConditions[$prefixedColumn] = $columnValue;
		}

		return $prefixedConditions;
	}

}
