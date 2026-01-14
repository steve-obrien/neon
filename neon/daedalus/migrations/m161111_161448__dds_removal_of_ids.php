<?php

use yii\db\Migration;

class m161111_161448__dds_removal_of_ids extends Migration
{
    public function safeUp()
    {
		// delete ids
		$this->dropColumn('dds_class', 'id');
		$this->dropColumn('dds_data_type', 'id');
		$this->dropColumn('dds_member', 'id');
		$this->dropColumn('dds_object', '_id');

		// add a uuid to object tables
		$this->execute('ALTER TABLE `dds_object` ADD `_uuid` CHAR(36) NOT NULL FIRST, ADD PRIMARY KEY (`_uuid`)');
    }

    public function safeDown()
    {
		// remove the uuid
		$this->dropColumn('dds_object', '_uuid');
		// add ids back again
		$this->execute('ALTER TABLE `dds_class` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
		$this->execute('ALTER TABLE `dds_data_type` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
		$this->execute('ALTER TABLE `dds_member` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
		$this->execute('ALTER TABLE `dds_object` ADD `_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`_id`)');
    }
}
