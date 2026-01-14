<?php
namespace neon\user\forms;

use neon\user\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class Signup extends Model
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
			['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'],
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
	 *
	 * @return User|null the saved model or null if saving fails
	 */
	public function signup()
	{
		if ($this->validate()) {
			$userClass = neon()->getUser()->identityClass;
			$user = new $userClass();
			$user->username = $this->username;
			$user->email = $this->email;
			$user->setPassword($this->password);
			$user->generateAuthKey();
			if ($user->save()) {
				return $user;
			}
		}
		return null;
	}

	/**
	 * return an array of attribute hints
	 */
	public function attributeHints()
	{
		return [
			'username' => 'A unique username'
		];
	}
}
