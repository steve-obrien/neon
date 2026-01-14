<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 05/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\db;

use \yii\db\Query as YiiQuery;
use neon\core\db\QueryTrait;

class Query extends YiiQuery
{
	use QueryTrait;
}