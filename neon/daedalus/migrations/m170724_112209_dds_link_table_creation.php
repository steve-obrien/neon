<?php

use yii\db\Migration;

/**
 * Migration to add linking to Daedalus.
 * Links are foreign keys and stored in the dds_link table but a field is
 * reserved on the table to make life easier with the rest of the coding.
 */
class m170724_112209_dds_link_table_creation extends Migration
{
    public function safeUp()
    {
		// create a linking table for links
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET latin1 COLLATE latin1_general_cs ENGINE=InnoDB';
		}
		$this->createTable('dds_link', [
			'from_id' => "char(22) NOT NULL COMMENT 'The UUID64 of the from object'",
			'from_member' => "char(50) NOT NULL COMMENT 'The member ref within from object'",
			'to_id' => "char(22) NOT NULL COMMENT 'The UUID64 of the to object'",
			'UNIQUE KEY `linky` (`from_id`,`from_member`,`to_id`)',
			'KEY (`from_id`)',
			'KEY (`to_id`)',
		], $tableOptions);

		// create the new link and link datatypes
		$sql =<<<EOQ
SET foreign_key_checks = 0;
REPLACE INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
('link','link','Single or many to many link placeholder. Can be used in joins. Data is stored in dds_link not the ddt_xxx table.','{"size":10}','char',0);
SET foreign_key_checks = 1;
EOQ;
		$this->execute($sql);

		// add a link_class field to members
		$this->addColumn('dds_member', 'link_class', "CHAR(50) NULL COMMENT 'For links, the linked classtype.' AFTER `choices`");

	}

    public function safeDown()
    {
		$this->dropTable('dds_link');
		$connection = neon()->db;
		$sql =<<<EOQ
SET foreign_key_checks=0;
DELETE FROM `dds_data_type` WHERE `data_type_ref`='link';
SET foreign_key_checks=1;
EOQ;
		$this->execute($sql);
		$this->dropColumn('dds_member', 'link_class');

    }
}
