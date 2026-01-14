<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 05/12/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\db;

use \yii\db\ActiveQuery as YiiActiveQuery;
use yii\db\Expression;

class TreeQuery extends ActiveQuery
{
	/**
	 * Gets the root nodes.
	 *
	 * @return TreeQuery the owner
	 */
	public function root()
	{
		$this->andWhere(['tree_left' => 1, 'tree_depth' => 0, 'tree_parent' => null]);
		return $this;
	}

	/**
	 * Gets the leaf nodes.
	 *
	 * @return \yii\db\ActiveQuery the owner
	 */
	public function leaves()
	{
		$model = new $this->modelClass();
		$db = $model->getDb();

		$columns = ['tree_left' => SORT_ASC];

		if ($model->treeAttribute !== false) {
			$columns = ['tree_root' => SORT_ASC] + $columns;
		}

		$this->andWhere(['tree_right' => new Expression($db->quoteColumnName('tree_left') . ' + 1')])
			->addOrderBy($columns);

		return $this;
	}


	/**
	 * Gets a query object prepopulated with criteria that selects descendants of a particular node through all levels
	 *
	 * Descendants are different to children as they are not limited to only the next level
	 * of the tree down.
	 *
	 * @param ActiveRecordInterface $node The node which you would like to find all the descendant of
	 * @return \yii\db\Query
	 */
	public function descendentsOf(ActiveRecordInterface $node)
	{
		$this->getProperties($left, $right);
		$this->where([['>', $left, $node->$left], ['<', $right, $node->$right]])
			->orderBy([$left => 'ASC']);
	}

	/**
	 * Get the model properties for the tree column names
	 * @param null $left
	 * @param null $right
	 * @param null $parent
	 * @param null $root
	 */
	public function getProperties(&$left=null, &$right=null, &$parent=null, &$root=null)
	{
		$model = new $this->modelClass();
		$model->getPropertes($left, $right, $parent, $root);
	}
}
