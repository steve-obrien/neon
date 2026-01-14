<?php

namespace neon\tests\unit\core\db;

use neon\core\db\TreeRecord;

class MultipleTree extends TreeRecord
{
	public static $idIsUuid64 = true;

	public $root = 'tree_root';

	public static function tableName()
	{
		return 'tree_multiple';
	}

}
