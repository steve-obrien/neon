<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields\filters;

use neon\core\form\fields\Field;
use neon\core\form\fields\Text;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class File extends Text
{
	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		// do a query
		$ids = neon()->db->query()->from('firefly_file_manager')->select('uuid')->where(['like', 'name', $searchData])->column();
		if (!empty($ids))
			$query->where($this->getDataKey(), '=', $ids);
	}
}