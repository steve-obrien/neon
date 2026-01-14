<?php

use yii\db\Migration;

class m170830_121945_firefly_change_local_drive_to_media extends Migration
{
    public function safeUp()
    {
        $this->execute('UPDATE firefly_file_manager SET drive = "media" WHERE drive = "local"');
    }

    public function safeDown()
    {
        $this->execute('UPDATE firefly_file_manager SET drive = "local" WHERE drive = "media"');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170830_121945_firefly_change_local_drive_to_media cannot be reverted.\n";

        return false;
    }
    */
}
