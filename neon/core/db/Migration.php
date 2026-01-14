<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\db;

use \yii\db\Migration as YiiMigration;

class Migration extends YiiMigration
{
	/**
	 * returns correct column format for a created at column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function createdAt()
	{
		return $this->dateTime()->comment('When the record was created');
	}

	/**
	 * returns correct column format for a updated at column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function updatedAt()
	{
		return $this->dateTime()->comment('When the record was last updated');
	}

	/**
	 * @deprecated - we want to use the users uuid not its integer based id
	 * returns correct column format for a created by column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function createdBy()
	{
		return $this->integer(11)->defaultValue(0)->comment('The id of the user that created this row');
	}

	/**
	 * @deprecated - we want to use the users uuid not its integer based id
	 * returns correct column format for a updated by column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function updatedBy()
	{
		return $this->integer(11)->defaultValue(0)->comment('The id of the user that last updated this row');
	}

	/**
	 * Create correct column format for a uuid64 column
	 * @param string $comment
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function uuid64($comment='A uuid using base 64')
	{
		return $this->getDb()->getSchema()
			->createColumnSchemaBuilder('char(22) CHARACTER SET latin1 COLLATE latin1_general_cs')
			->comment($comment);
	}

	/**
	 * Runs a function specified by $function - adds the standard output messages and time logging.
	 * @param  string    $action
	 * @param  callable  $function
	 */
	public function run($action, callable $function)
	{
		echo "    > $action ...";
		$time = microtime(true);
		$function();
		echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
	}

	/**
	 * Add a soft delete column to the table
	 * @param string $table - the table to add the column name to
	 * @param string $column - the string name of the column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function addColumnSoftDelete($table, $column)
	{
		$this->addColumn($table, $column, $this->softDelete());
	}

	/**
	 * Returns correct column structure for a soft delete column
	 * @return \yii\db\ColumnSchemaBuilder
	 */
	public function softDelete()
	{
		return $this->tinyInteger(1)
			->unsigned()
			->notNull()
			->defaultValue(0)
			->comment('Whether this row is deleted or not');
	}

}