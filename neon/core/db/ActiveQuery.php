<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 05/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\db;

use \yii\db\ActiveQuery as YiiActiveQuery;
use \neon\core\db\QueryTrait;

class ActiveQuery extends YiiActiveQuery
{
	/**
	 * @inheritDoc
	 */
	public function where($condition, $params = [])
	{
		return $this->andWhere($condition, $params);
	}

	/**
	 * Overwrite any previously added where statements.
	 *
	 * @param string|array $condition
	 * @param array $params
	 * @return $this
	 */
	public function setWhere($condition, $params = [])
	{
		return parent::where($condition, $params);
	}
}
