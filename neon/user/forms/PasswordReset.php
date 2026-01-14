<?php
namespace neon\user\forms;

use neon\user\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Password reset form
 */
class PasswordReset extends Model
{
	/**
	 * @var string - message to show on success
	 */
	public static $successResetMessage = 'Your password was successfully reset';

	/**
	 * @var string
	 */
	public $password;

	/**
	 * @var User
	 */
	private $_user;

	/**
	 * Creates a form model given a token.
	 *
	 * @param  string  $token
	 * @param  array   $config name-value pairs that will be used to initialize the object properties
	 * @throws \yii\base\InvalidParamException if token is empty or not valid
	 */
	public function __construct($token, $config = [])
	{
		if (empty($token) || !is_string($token)) {
			throw new InvalidArgumentException('Password reset token cannot be blank.');
		}
		$this->_user = call_user_func(
			[neon()->getUser()->identityClass, 'findByPasswordResetToken'],
			$token
		);

		$hasExpired = call_user_func(
			[neon()->getUser()->identityClass, 'hasPasswordResetTokenExpired'],
			$token
		);
		if ($hasExpired) {
			throw new InvalidArgumentException('This password reset token has expired.');
		}
		if ( !$this->_user ) {
			throw new InvalidArgumentException('Wrong password reset token.');
		}
		parent::__construct($config);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['password', 'required'],
			['password', 'string', 'min' => 6],
		];
	}

	/**
	 * Resets password.
	 *
	 * @return boolean if password was reset.
	 */
	public function resetPassword()
	{
		$user = $this->_user;
		$user->setPassword($this->password);
		$user->removePasswordResetToken();

		return $user->save(false);
	}
}
