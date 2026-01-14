<?php
/**
 * Neon
 *
 * @author Steven O'Brien <steven.obrien@newicon.net>
 * @link http://newicon.net/framework
 * @copyright Copyright &copy; 2009-2011 Newicon Ltd
 * @license http://newicon.net/framework/license/
 */

namespace neon\core\db;

use \neon\core\db\Query;
use \neon\core\db\ActiveRecord;
use \neon\tests\unit\core\db\Tree;
use \yii\db\ActiveRecordInterface;

/**
 * The neon tree table behavior
 * the owner ActiveRecord must have an id column which is a single primary key column
 *
 * NOTES: when moving nodes sometimes you have to call
 * <code>$record->refresh()</code>
 * to see the changes performed by this class
 * This is because often the moving and manipulating the tree affects many record at once.
 *
 *
 * @author Steven O'Brien <steven.obrien@newicon.net>
 *
 * @property \yii\db\ActiveRecord $owner
 * @method static TreeQuery find
 *
 * @property int $tree_left
 * @property int $tree_right
 * @property int $tree_depth
 * @property mixed $tree_parent parent id - primary key of the parent node
 */
class TreeRecord extends \neon\core\db\ActiveRecord implements ITreeRecord
{
	/**
	 * @inheritdoc
	 */
	public static $queryClass = TreeQuery::class;

	/**
	 * Determines whether the node is root.
	 *
	 * @return boolean whether the node is root
	 */
	public function isRoot()
	{
		return $this->tree_left == 1;
	}

	/**
	 * Make this node a root node
	 *
	 * @throws \Exception if already root node already exists - you can;t have more than 1 root node
	 * @return boolean - true if save is successful
	 */
	public function makeRoot()
	{
		if (static::find()->root()->exists())
			throw new \Exception('You can only create one root node');
		$this->tree_left = 1;
		$this->tree_right = 2;
		$this->tree_depth = 0;
		$this->tree_parent = null;
		return $this->save();
	}

	/**
	 * Recusive *Expensive* function that rebuilds the tree based on the parentId
	 *
	 * @param ITreeRecord $parent
	 * @param int $left
	 * @param int $depth
	 * @return int
	 */
	public function rebuildTree($parent, $left, $depth=0)
	{
		// the right value of this node is the left value + 1
		$right = $left + 1;

		// get all children of this node
		// getChildrenByParentId

		$children = $this->owner->findAll([
			'condition'=>"tree_parent = :pid",
			'params'=>[':pid'=>$parent->getPrimaryKey()],
			'order'=>'tree_left'
		]);

		foreach ($children as $node) {
			// recursive execution of this function for each child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			$right = $this->rebuildTree($node, $right, $depth + 1);
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$parent->tree_depth = $depth;
		$parent->tree_left = $left;
		$parent->tree_right = $right;
		$parent->save();

		// return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Add a child node to this node
	 *
	 * @param TreeRecord $node
	 * @param callable $callable - function receiving the new child enables building nested structures for simple trees
	 * @throws \Exception|\Throwable
	 * @return TreeRecord the child added
	 */
	public function addChild(TreeRecord $node, callable $callable=null) : TreeRecord
	{
		$result = static::addAsLastChildOf($this, $node);
		if ($callable) $callable($node);
		return $node;
	}

	/**
	 * @param TreeRecord $node
	 * @throws \Exception|\Throwable
	 * @return ITreeRecord the child added
	 */
	public function addAsLastChild(TreeRecord $node)
	{
		static::addAsLastChildOf($this, $node);
		return $node;
	}

	/**
	 * @param TreeRecord $node
	 * @throws \Exception|\Throwable
	 * @return ITreeRecord the child added
	 */
	public function addAsFirstChild(TreeRecord $node)
	{
		static::addAsFirstChildOf($this, $node);
		return $node;
	}

	/**
	 * moves $node as first child of $parent record
	 *
	 * @param TreeRecord $parent
	 * @throws \Exception
	 * @return boolean
	 */
	public function moveAsFirstChildOf(TreeRecord $parent)
	{
		if ($parent === $this || $this->isAncestorOf($parent))
			throw new \Excepton("Cannot move node as first child of itself or into a descendant");
		$oldDepth = $this->tree_depth;
		$this->tree_left = $parent->tree_depth + 1;
		$this->tree_parent = $parent->getPrimaryKey();
		$this->save();
		$this->_updateNode($this, $parent->tree_left + 1, $this->tree_depth - $oldDepth);

		return true;
	}

	/**
	 * Gets a criteria object prepopulated with criteria that selects descendants of a particular node through all levels
	 *
	 * Descendants are different to children as they are not limited to only the next level
	 * of the tree down.
	 *
	 * @param ActiveRecordInterface $node The node which you would like to find all the descendant of
	 * @return \yii\db\Query
	 */
	public static function findDescendentsOf(ActiveRecordInterface $node)
	{
		return static::find()->descendentsOf($node);
	}

	/**
	 * Add the node as the last child of the parent
	 *
	 * @param TreeRecord $parent
	 * @param TreeRecord $node
	 * @return boolean whether save was successful
	 * @throws \Exception|\Throwable if there is any exception during query.
	 */
	public static function addAsLastChildOf(TreeRecord $parent, TreeRecord $node)
	{
		static::validateAdd($parent, $node);
		return static::getDb()->transaction(function() use ($node, $parent) {
			$parent->refresh();
			$newLeft = $parent->tree_right;
			$newRight = $parent->tree_right + 1;
			static::_shiftRlValues($newLeft, 2);
			return static::_save($node, $newLeft, $newRight, $parent->tree_depth + 1, $parent->getPrimaryKey());
		});
	}

	/**
	 * Add the node as the first child of the parent
	 *
	 * @param TreeRecord $parent
	 * @param TreeRecord $node
	 * @return boolean whether save was successful
	 * @throws \Throwable
	 */
	public static function addAsFirstChildOf(TreeRecord $parent, TreeRecord $node)
	{
		static::validateAdd($parent, $node);
		return static::getDb()->transaction(function() use ($node, $parent) {
			$parent->refresh();
			$newLeft = $parent->tree_left + 1;
			$newRight = $parent->tree_left + 2;
			static::_shiftRlValues($newLeft, 2);
			return static::_save($node, $newLeft, $newRight, $parent->tree_depth + 1, $parent->getPrimaryKey());
		});
	}

	/**
	 * Validate parent and child nodes before adding
	 *
	 * @param TreeRecord $parent
	 * @param TreeRecord $node
	 * @throws \Exception
	 */
	public static function validateAdd(TreeRecord $parent, TreeRecord $node)
	{
		// cannot insert as child of itself
		if ($parent === $node)
			throw new \Exception("Cannot insert node as last child of itself");
		if ($parent->getIsNewRecord())
			throw new \Exception('The parent node must exist.');
		if ($node->tree_depth === 0 && $parent->isRoot())
			throw new \Exception('Can not put a root node inside another root node');
	}

	/**
	 * Move a node and its children to location $destLeft and updates rest of tree
	 *
	 * @param TreeRecord $node
	 * @param int $destLeft destination left value
	 * @param int $depthDiff difference in depth
	 * @throws \Exception
	 */
	private function _updateNode(TreeRecord $node, $destLeft, $depthDiff)
	{
		static::getDb()->transaction(function() use($node, $destLeft, $depthDiff, $treeSize) {
			$left = $node->tree_left;
			$right = $node->tree_right;
			$treeSize = $right - $left + 1;
			// Make room in the new branch
			$this->_shiftRlValues($destLeft, (int) $treeSize);
			// src was shifted too?
			if ($left >= $destLeft) {
				$left += $treeSize;
				$right += $treeSize;
			}
			// update level for descendants
			$tableName = static::tableName();
			$params = [':depthDiff'=>$depthDiff, ':left'=>$left, ':right'=>$right];
			$this->getDb()->createCommand("UPDATE `$tableName` 
				SET tree_depth = tree_depth + :depthDiff 
				WHERE tree_left > :left
				AND tree_right < :right", $params
			)->execute();
			// now there's enough room next to target to move the subtree
			$this->_shiftRlRange($left, $right, $destLeft - $left);
			// correct values after source (close gap in old tree)
			$this->_shiftRlValues($right + 1, -$treeSize);
		});
	}

	/**
	 * Adds '$delta' to all Left and Right values that are >= '$first'. '$delta' can also be negative.
	 *
	 * @param int $first First node to be shifted
	 * @param int $delta Value to be shifted by, can be negative
	 * @throws \Exception - exceptions during query
	 */
	private static function _shiftRlValues($first, $delta)
	{
		// shift left columns
		$tableName = static::tableName();
		$params = [':delta' => $delta, ':first' => $first];

		static::getDb()->createCommand("UPDATE `$tableName` 
			SET tree_left = tree_left + :delta 
			WHERE tree_left >= :first", $params
		)->execute();

		static::getDb()->createCommand("UPDATE `$tableName`
			SET tree_right = tree_right + :delta 
			WHERE tree_right >= :first", $params
		)->execute();
	}

	/**
	 * Adds '$delta' to all Left and Right values that are >= '$first' and <= '$last', '$delta' can also be negative.
	 *
	 * @param int $first First node to be shifted (L value)
	 * @param int $last	Last node to be shifted (L value)
	 * @param int $delta Value to be shifted by, can be negative
	 * @throws \Exception - exceptions during query
	 */
	private function _shiftRlRange($first, $last, $delta)
	{
		$tableName = static::tableName();
		$params = [':delta' => $delta, ':first' => $first, ':last' => $last];

		$this->getDb()->createCommand("UPDATE `$tableName` 
			SET tree_left = tree_left + :delta 
			WHERE tree_left >= :first 
			AND tree_left <= :last", $params
		)->execute();

		$this->getDb()->createCommand("UPDATE `$tableName` 
			SET tree_right = tree_right + :delta 
			WHERE tree_right >= :first 
			AND tree_right <= :last", $params
		)->execute();
	}

	/**
	 * Save a node with defined values
	 *
	 * @param TreeRecord $node
	 * @param int $left
	 * @param int $right
	 * @param int $depth
	 * @param mixed $parent
	 * @return bool
	 */
	private static function _save(TreeRecord $node, $left, $right, $depth, $parent)
	{
		$node->tree_left = $left;
		$node->tree_right = $right;
		$node->tree_depth = $depth;
		$node->tree_parent = $parent;
		return $node->save();
	}

	/**
	 * @inheritDoc
	 * @throws TreeUnsupportedException
	 */
	public function beforeSave($insert)
	{
		if ($insert && ($this->tree_left === null || $this->tree_right === null)) {
			throw new TreeUnsupportedException('You must insert the record as a child of an existing node or declare the node as a root node via makeRoot method');
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function transactions()
	{
		return [
			self::SCENARIO_DEFAULT => self::OP_ALL,
		];
	}
}
