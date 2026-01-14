<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsHelpers;

use neon\daedalus\interfaces\IDdsQueryHelpers;

/**
 * Helpers for the (rare) cases when you need to create a yii query for
 * the database as Daedalus doesn't provide sufficient control.
 */
class DdsQueryHelpers implements IDdsQueryHelpers
{
	/**
	 * @inheritdoc
	 */
	public function addObjectDeletionChecksOnJoin(\yii\db\Query $query, $tables, $join='innerJoin')
	{
		foreach ($tables as $table) {
			if ($join == 'innerJoin') {
				$query->innerJoin("dds_object {$table}Obj", "{$table}Obj._uuid = {$table}._uuid");
				$query->andWhere("({$table}Obj._deleted = 0)");
			} else if ($join == 'leftJoin') {
				$query->leftJoin("dds_object {$table}Obj", "{$table}Obj._uuid = {$table}._uuid");
				$query->andWhere("({$table}Obj._deleted IS NULL || {$table}Obj._deleted = 0)");
			}
		}
	}
}