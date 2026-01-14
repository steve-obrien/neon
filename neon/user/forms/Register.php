<?php
namespace neon\user\forms;

use neon\user\models\User;
use neon\user\models\UserInvite;
use yii\base\Model;
use Yii;

/**
 * Register form
 */
class Register extends \neon\core\form\Form
{
	public function init()
	{
		$userNameValidators = [
			['required'],
			'filter' => ['filter' => 'trim'],
			'match'  => ['pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'],
			'unique' => ['targetClass' => '\neon\user\models\User', 'message' => 'This username has already been taken.'],
			'string' => ['min' => 2, 'max' => 255]
		];
		$this->addFieldText('username')
			->setPlaceholder('Username')
			->setValidators($userNameValidators);

		$passwordValidators = ['string' => ['min' => setting('user', 'minimumPasswordLength', 8), 'message' => 'The password must be at least 8 characters long']];
		$this->addFieldPassword('password')
			->setPlaceholder('Password')
			->setValidators($passwordValidators);

		$this->addFieldSubmit('register', ['label'=>'Register', 'attributes'=>['class' => 'btn-block btn-lg']]);
	}

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

			['password', 'required'],
			['password', 'string', 'min' => setting('user', 'minimumPasswordLength', 6)]
		];
	}

	public function getUsername()
	{
		return $this->getField('username')->getValue();
	}

	/**
	 * Signs user up.
	 *
	 * @return User|false the saved model or false if saving fails
	 */
	public function register($token)
	{
		$data = $this->getData();
		if ($this->validate()) {
			$user = call_user_func(
				[neon()->getUser()->identityClass, 'findIdentityByInviteToken'],
				$token
			);
			$user->username = $data['username'];
			$user->setPassword($data['password']);
			$user->status = User::STATUS_ACTIVE;
			if ($user->save()) {
				UserInvite::findInviteByInviteToken($token, true)->destroy();
				return $user;
			}
		}
		return false;
	}
}
