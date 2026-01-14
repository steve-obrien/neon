<?php

namespace neon\tests\unit\core\db;

use neon\core\db\TreeRecord;
use neon\core\web\Response;

class Tree extends TreeRecord
{
	public static $idIsUuid64 = true;

	public $root = false;

	public static function tableName()
	{
		return 'tree';
	}

}

