<?php

namespace neon\user\services;

use neon\user\models\User as UserModel;
use neon\user\models\UserInvite as UserInviteModel;

class UserInvite
{

	/**
	 * Sends an email with a link for completing the account registration
	 * @param  string  $token  the invite token to send to the user
	 * @throws \Exception if the From email address is not set
	 * @return boolean whether the email was sent successfully
	 */
	public static function send($token)
	{
		$userInvite = UserInviteModel::findOne([
			'token' => $token
		]);
		if ($userInvite) {
			$user = UserModel::findOne([
				'uuid' => $userInvite->user_id
			]);
			if ($user) {
				$fromName = setting('admin', 'fromEmailName', neon()->name);
				$fromAddress = setting('admin', 'fromEmailAddress', 'system@'.neon()->request->getHostName());
				
				if (!$fromAddress)
					throw new \Exception('The From Address needs to be set in the admin section for emails');

				$templates = [
					'html' => '@neon/user/views/mail/userInviteToken-html',
					'text' => '@neon/user/views/mail/userInviteToken-text'
				];

				$inviteLink = neon()->urlManager->createAbsoluteUrl(['user/account/register', 'token' => $userInvite->token]);

				$templateData = [
					'user' => $user,
					'inviteLink' => $inviteLink,
					'expiresIn' => $userInvite->inviteTokenExpiresIn(),
					'siteName' => (setting('core', 'site_name', neon()->name) ?: 'Neon')
				];

				$siteName = setting('core', 'site_name', neon()->name) ?: 'Neon';

				return neon()->mailer->compose($templates, $templateData)
					->setTo($user->email)
					->setFrom([$fromAddress => $fromName])
					->setSubject($siteName.' invitation for ' . $user->email)
					->send();
			}
		}
		return false;
	}

	/**
	 * @param  string  $token  the invite token to resend
	 * @return  boolean
	 */
	public static function resend($token)
	{
		return self::send($token);
	}

	/**
	 * Hard delete a user invitation
	 * @param  string  $token  the invite token to delete
	 * @return  boolean
	 */
	public static function revoke($token)
	{
		return UserInviteModel::findInviteByInviteToken($token, true)->destroy();
	}
}