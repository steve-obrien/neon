<?php

namespace neon\tests\unit\daedalus;

use \neon\core\test\Unit;
use \neon\daedalus\services\ddsManager\DdsCore;
use \neon\core\helpers\Hash;
use Yii;

class DdsBaseTestUnit extends Unit
{
	public static function setUpBeforeClass(): void {
		// data needs to be deleted in a particular order
		$sql =<<<EOQ
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM `dds_member`;
DELETE FROM `dds_object`;
DELETE FROM `dds_class`;
SET FOREIGN_KEY_CHECKS=1;
EOQ;
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function tearDown():void {
		// data needs to be deleted in a particular order
		$sql =<<<EOQ
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM `dds_member`;
DELETE FROM `dds_object`;
DELETE FROM `dds_class`;
SET FOREIGN_KEY_CHECKS=1;
EOQ;
		Yii::$app->db->createCommand($sql)->execute();
	}
}


