<?php

use neon\core\db\Migration;

class m20200114_203825_update_data_types extends Migration
{
	public function safeUp()
	{
		$this->execute("UPDATE dds_data_type SET `storage_ref`='textlong' WHERE `data_type_ref` IN ('html', 'markdown')");
	}

	public function safeDown()
	{
		$this->execute("UPDATE dds_data_type SET `storage_ref`='text' WHERE `data_type_ref` IN ('html', 'markdown')");
	}
}
