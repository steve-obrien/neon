<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Schema;
use yii\rbac\DbManager;
use \neon\user\models\User;
use \neon\user\models\Session;
use \neon\user\models\AuthRule;
use \neon\user\models\AuthItem;
use \neon\user\models\AuthItemChild;
use \neon\user\models\AuthAssignment;


/**
 * Initializes the User app tables
 */
class m140506_102106_user_init extends \yii\db\Migration
{
	/**
	 * @throws yii\base\InvalidConfigException
	 * @return DbManager
	 */
	protected function getAuthManager()
	{
		$authManager = Yii::$app->getAuthManager();
		if (!$authManager instanceof DbManager) {
			throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
		}
		return $authManager;
	}

	/**
	 * @inheritdoc
	 * Install the User, AuthRule, AuthItem, AuthItemChild, AuthAssignment tables
	 */
	public function safeUp()
	{
		$this->createTable(User::tableName(), [
			'id' => 'pk',
			'username' => 'string',
			'email' => 'string',
			'status' => 'string',
			'password_hash' => 'string',
			'auth_key' => 'string COMMENT "The auth key is used to authenticate the user when logging in via their cookie. It is auto generated when a new user is created"',
			'password_reset_token' => 'string',
			'super' => 'bool not null default 0 COMMENT "If true (1) then this user is always granted access"',
			'failed_logins' => 'int not null default 0 COMMENT "increments on each failed login attempt, is reset to zero on successful login"',
			'created_on' => 'datetime',
			'updated_on' => 'datetime',
			'last_login' => 'datetime COMMENT "The date the user last logged in"',
			'logins' => 'int COMMENT "Inremented on every successfull login"'
		]);
		$this->createTable(AuthRule::tableName(), [
			'name' => $this->string(64)->notNull(),
			'data' => $this->text(),
			'created_at' => $this->integer(),
			'updated_at' => $this->integer(),
			'PRIMARY KEY (name)',
		]);
		$this->createTable(AuthItem::tableName(), [
			'name' => $this->string(64)->notNull(),
			'type' => $this->integer()->notNull(),
			'description' => $this->text(),
			'rule_name' => $this->string(64),
			'data' => $this->text(),
			'created_at' => $this->integer(),
			'updated_at' => $this->integer(),
			'PRIMARY KEY (name)',
			'FOREIGN KEY (rule_name) REFERENCES ' . neon()->authManager->ruleTable . ' (name) ON DELETE SET NULL ON UPDATE CASCADE'
		]);
		$this->createIndex('idx-auth_item-type', AuthItem::tableName(), 'type');
		$this->createTable(AuthItemChild::tableName(), [
			'parent' => $this->string(64)->notNull(),
			'child' => $this->string(64)->notNull(),
			'PRIMARY KEY (parent, child)',
			'FOREIGN KEY (parent) REFERENCES ' . neon()->authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
			'FOREIGN KEY (child) REFERENCES ' . neon()->authManager->itemTable  . ' (name) ON DELETE CASCADE ON UPDATE CASCADE'
		]);
		$this->createTable(AuthAssignment::tableName(), [
			'item_name' => $this->string(64)->notNull(),
			'user_id' => $this->string(64)->notNull(),
			'created_at' => $this->integer(),
			'PRIMARY KEY (item_name, user_id)',
			'FOREIGN KEY (item_name) REFERENCES ' . neon()->authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
		]);
		$this->createTable(Session::tableName(), [
			'id' => 'CHAR(64) NOT NULL PRIMARY KEY',
			'expire' => 'int',
			'data' => 'BLOB'
		]);
	}

	/**
	 * @inheritdoc
	 * Drop the User, AuthRule, AuthItem, AuthItemChild, AuthAssignment tables
	 */
	public function safeDown()
	{
		$this->dropTable(User::tableName());
		$this->dropTable(AuthAssignment::tableName());
		$this->dropTable(AuthItemChild::tableName());
		$this->dropTable(AuthItem::tableName());
		$this->dropTable(AuthRule::tableName());
		$this->dropTable(Session::tableName());
	}
}
