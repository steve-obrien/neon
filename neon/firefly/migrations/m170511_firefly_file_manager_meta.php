<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 07/06/2017
 * @package neon
 */

use \neon\core\db\Migration;
use \neon\firefly\services\fileManager\models\FileManager;

class m170511_firefly_file_manager_meta extends Migration
{
	public function safeUp()
	{
		$this->addColumn(FileManager::tableName(), 'meta', 'text COMMENT "JSON meta data for the file for example image dimensions etc"');
	}

	public function safeDown()
	{
		$this->dropColumn(FileManager::tableName(), 'meta');
	}
}