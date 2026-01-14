<?php

use yii\db\Migration;

class m161018_154331__dds_add_definition_to_class extends Migration
{
    public function safeUp()
    {
    	$this->addColumn('dds_class', 'definition', $this->text(10000) . " DEFAULT NULL COMMENT 'A definition for use by other modules, but not understood by DDS'");
    }

    public function safeDown()
    {
    	$this->dropColumn('dds_class', 'definition');
    }
}
