<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Schema;
use yii\rbac\DbManager;


/**
 * Initializes the User app tables
 */
class m161207_140600_user_session extends \yii\db\Migration
{

	/**
	 * @inheritdoc
	 * Extra session info
	 */
	public function safeUp()
	{
		$table = \neon\user\models\Session::tableName();

		$this->addColumn($table, 'expires', $this->dateTime()
			->comment('This is a datetime representation of the expire field'));

		$this->addColumn($table, 'user_id', $this->integer(11)
			->comment('Store the user id this session belongs to - it will be 0 if this represents the session of a guest'));

		$this->addColumn($table, 'ip_address', $this->string('45')
			->comment('The users ip address'));

		$this->addColumn($table, 'last_active', $this->dateTime()
			->comment('The date and time the user was last active, essentially when this record was written to'));

		$this->addColumn($table, 'user_agent', $this->text()
			->comment('The users user agent - OS and browser as reported by the HTTP user agent string'));

		$this->addColumn($table, 'referrer', $this->text()
			->comment('The referring address - HTTP referrer header'));

		$this->addColumn($table, 'current', $this->dateTime()
			->comment('This current url the user requested in this session'));

	}

	/**
	 * @inheritdoc
	 * Remove extra session info
	 */
	public function safeDown()
	{
		$table = \neon\user\models\Session::tableName();
		$this->dropColumn($table, 'expires');
		$this->dropColumn($table, 'user_id');
		$this->dropColumn($table, 'ip_address');
		$this->dropColumn($table, 'last_active');
		$this->dropColumn($table, 'user_agent');
		$this->dropColumn($table, 'referrer');
		$this->dropColumn($table, 'current');
	}
}
