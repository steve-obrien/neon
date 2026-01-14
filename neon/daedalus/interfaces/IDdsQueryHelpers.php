<?php

namespace neon\daedalus\interfaces;

/**
 * Helpers for the (rare) cases when you need to create a yii query for
 * the database as Daedalus doesn't provide sufficient control.
 */
interface IDdsQueryHelpers
{
	/**
	 * Add object deletion checks to a query using join
	 * @param \yii\db\Query $query  the query object you are adding checks to
	 * @param $tables  the set of tables (or aliases) in your query that you want to check deletion on
	 * @param string $join  the method for joining - 'innerJoin' or 'leftJoin'. Use innerJoin when you
	 *   care if the object exists or not, and leftJoin when its ok it doesn't exist but not if it
	 *   does and has been deleted.
	 * @return mixed
	 */
	public function addObjectDeletionChecksOnJoin(\yii\db\Query $query, $tables, $join='innerJoin');
}