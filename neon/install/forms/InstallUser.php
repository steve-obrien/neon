<?php
namespace neon\install\forms;

use neon\user\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class InstallUser extends Model
{
	public $username;
	public $email;
	public $password;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'unique', 'targetClass' => '\neon\user\models\User', 'message' => 'This username has already been taken.'],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'string', 'max' => 255],
			['email', 'unique', 'targetClass' => '\neon\user\models\User', 'message' => 'This email address has already been taken.'],

			['password', 'required'],
			['password', 'string', 'min' => 6],
		];
	}

	/**
	 * Signs user up.
	 * @param  User $user
	 * @return boolean whether save was successful
	 */
	public function createUser(&$user)
	{
		if (!$this->validate())
			return false;
		$user = new User();
		$user->username = $this->username;
		$user->email = $this->email;
		$user->setPassword($this->password);
		$user->generateAuthKey();
		// use setAttribute as not superuser
		$user->setAttribute('super', 1);
		$success = $user->save();
		if ($success) {
			$user->setRoles(['neon-administrator', 'neon-developer']);
		}
		return $success;
	}
}