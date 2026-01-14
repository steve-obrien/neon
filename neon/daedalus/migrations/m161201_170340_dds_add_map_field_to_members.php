<?php

use yii\db\Migration;

class m161201_170340_dds_add_map_field_to_members extends Migration
{

    public function safeUp()
    {
		$this->addColumn('dds_member', 'map_field', "TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If true, then this is field data used in defining maps' AFTER `definition`");
    }

    public function safeDown()
    {
		$this->dropColumn('dds_member', 'map_field');
    }

}
