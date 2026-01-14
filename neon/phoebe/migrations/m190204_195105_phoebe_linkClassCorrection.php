<?php

use yii\db\Migration;

class m190204_195105_phoebe_linkClassCorrection extends Migration
{
	public function safeUp()
	{
		$this->execute("UPDATE `dds_member` SET `link_class`='phoebe_class' WHERE `link_class`='Class'");
		$this->execute("UPDATE `dds_member` SET `link_class`='phoebe_object' WHERE `link_class`='Object'");
	}

	public function safeDown()
	{
		$this->execute("UPDATE `dds_member` SET `link_class`='Class' WHERE `link_class`='phoebe_class'");
		$this->execute("UPDATE `dds_member` SET `link_class`='Object' WHERE `link_class`='phoebe_object'");
	}
}
