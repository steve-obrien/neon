<?php
namespace neon\user\forms;

use neon\user\models\User;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequest extends Model
{
	public $email;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'exist',
				'targetClass' => '\neon\user\models\User',
				'filter' => ['status' => User::STATUS_ACTIVE],
				'message' => 'There is no user with such email.'
			],
		];
	}

	/**
	 * Sends an email with a link, for resetting the password.
	 * @throws \Exception if the From email address is not set
	 * @return boolean whether the email was send
	 */
	public function sendEmail()
	{
		/* @var $user User */
		$user = call_user_func(
			[neon()->getUser()->identityClass, 'findOne'],
			[
				'status' => User::STATUS_ACTIVE,
				'email' => $this->email,
			]
		);

		if ($user) {
			$isValidPasswordResetToken = call_user_func(
				[neon()->getUser()->identityClass, 'isPasswordResetTokenValid'],
				$user->password_reset_token
			);
			if (!$isValidPasswordResetToken) {
				$user->generatePasswordResetToken();
			}
			if ($user->save()) {
				$fromName = setting('admin', 'fromEmailName', neon()->name);
				$fromAddress = setting('admin', 'fromEmailAddress', 'system@'.neon()->request->getHostName());
				if (!$fromAddress)
					throw new \Exception('The From Address needs to be set in the admin section for emails');

				$templates = [
					'html' => '@neon/user/views/mail/passwordResetToken-html',
					'text' => '@neon/user/views/mail/passwordResetToken-text'
				];

				$resetLink = neon()->urlManager->createAbsoluteUrl(['user/account/reset-password', 'token' => $user->password_reset_token]);
				$templateData = [
					'user' => $user,
					'resetLink' => $resetLink,
					'expiresIn' => User::passwordResetTokenExpiresIn($user->password_reset_token)
				];

				$siteName = setting('core', 'site_name', neon()->name) ?: 'Neon';

				return neon()->mailer->compose($templates, $templateData)
					->setFrom([$fromAddress => $fromName])
					->setTo($this->email)
					->setSubject('Password reset for ' . $siteName)
					->send();
			}
		}
		return false;
	}
}
