<?php
namespace neon\core\db;

use yii\db\ActiveRecordInterface;

interface ITreeRecord extends ActiveRecordInterface
{

	public function addChild(TreeRecord $node) : TreeRecord;
	public function addAsLastChild(TreeRecord $node);
	public function addAsFirstChild(TreeRecord $node);

	public static function addAsLastChildOf(TreeRecord $parent, TreeRecord $node);
}
