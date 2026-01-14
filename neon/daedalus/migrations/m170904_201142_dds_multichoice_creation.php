<?php

use yii\db\Migration;

class m170904_201142_dds_multichoice_creation extends Migration
{
    public function safeUp()
    {
	    $sql =<<<EOQ
SET foreign_key_checks = 0;
REPLACE INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
('choice_multiple','Multiple Choice','For storing multiple choice keys','{"size":10000}','text',0);
SET foreign_key_checks = 1;
EOQ;
	    $this->execute($sql);

    }

    public function safeDown()
    {
	    $sql =<<<EOQ
SET foreign_key_checks=0;
DELETE FROM `dds_data_type` WHERE `data_type_ref`='choice_multiple';
SET foreign_key_checks=1;
EOQ;
	    $this->execute($sql);
    }
}
