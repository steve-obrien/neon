<?php

use neon\core\db\Migration;

class m20200220_183243_daedalus_set_updated_to_created_for_new_objects extends Migration
{
	public function safeUp()
	{
		$sql = <<<EOQ
	UPDATE dds_object SET [[_updated]] = [[_created]] WHERE [[_updated]] IS NULL;
EOQ;
		$this->execute($sql);
	}

	public function safeDown()
	{
		$sql = <<<EOQ
	UPDATE dds_object SET [[_updated]] = NULL WHERE [[_updated]] = [[_created]];
EOQ;
		$this->execute($sql);

	}
}
