<?php

use neon\core\db\Migration;
use \neon\user\services\apiTokenManager\models\UserApiToken;

class m170104_26042017_user_api_tokens extends Migration
{

    /**
     * @inheritdoc
     * Install the User, AuthRule, AuthItem, AuthItemChild, AuthAssignment tables
     */
	public function safeUp()
    {
		$sql = "DROP TABLE IF EXISTS ".UserApiToken::tableName();
		\Yii::$app->getDb()->createCommand($sql)->execute();

	    $this->createTable(UserApiToken::tableName(), [
		    'token'=>'varchar(64) PRIMARY KEY COMMENT "The token"',
	    	'name' => 'varchar(100) COMMENT "A name for this token"',
		    'user_id'=>'int COMMENT "foreign key of the user who owns this token"',
		    'active'=>'int not null default 0 COMMENT "whether the token is currently active, 1 is an active token, 0 is a non active token and will not produce successful requests"',
		    'last_used'=>'datetime COMMENT "store the date and time of the last request that used this token"',
		    'used_count'=>'int not null default 0 COMMENT "number of times this token has been used"',
		    // timestamps
		    'created_at' =>  $this->createdAt(),
		    'updated_at' =>  $this->updatedAt(),
	    ]);
    }

    public function safeDown()
    {
	    $this->dropTable(UserApiToken::tableName());
    }
}
