<?php

use yii\db\Migration;

class m170121_150942_cms_delete_ddt_cms_static extends Migration
{
	public function safeUp()
	{
		// remove the ddt_cms_static_content table within cms as it is
		// created by an earlier cms migration so always appears if the
		// migration creation order is not adhered to.
		$connection = neon()->db;
$sql =<<<EOQ
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `ddt_cms_static_content`;
DELETE FROM `dds_member` WHERE `class_type`='cms_static_content';
DELETE FROM `dds_class` WHERE `class_type`='cms_static_content';
SET foreign_key_checks = 1;
EOQ;
		$connection->createCommand($sql)->execute();
	}

	public function safeDown()
	{
	}
}
