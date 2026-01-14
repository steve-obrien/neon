<?php
namespace neon\user\forms;

use neon\user\App as UserApp;
use neon\user\App;
use neon\user\models\User;
use neon\user\models\UserInvite as UserInviteModel;
use neon\user\services\UserInvite as UserInviteService;
use yii\base\Event;
use yii\base\Model;
use Yii;

/**
 * User invitation form for use by Administrators
 */
class UserInvite extends UserCreate
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->setName('UserInvite');

		$emailValidators = [
				['required'],
				'filter' => ['filter' => 'trim'],
				'string' => ['min'=>5, 'max' => 255],
			];
		$this->addFieldEmail('email')
			->setLabel('Email')
			->setValidators($emailValidators);

		if (neon()->user->isSuper()) {
			$this->addFieldSwitch('super')
				->setLabel('Is Superuser?')
				->setOnLabel('Yes')
				->setOffLabel('No');
		}

		$this->addFieldCheckList('roles')
			->setLabel('Assign Roles')
			->setItems($this->getRoles());

		$this->getUserApp()->triggerUserInviteFormInit($this);

		$this->addFieldSubmit('Save', ['label' => 'Send Invitation']);
	}

	/**
	 * @inheritdoc
	 */
	public function getEmail()
	{
		return $this->getField('email')->getValue();
	}

	/**
	 * Create a new user_user (if the user matching the email doesn't already existt)
	 *  with status PENDING and create a new user_invite, 
	 *  and email the invitation to the new user
	 * @return boolean  whether the user was created and the invitation was sent successfully
	 * @throws \yii\base\Exception
	 * @throws \yii\base\UnknownPropertyException
	 */
	public function inviteUser()
	{
		if ($this->validate()) {
			if (!$user = User::findByUsername($this->getField('email')->getValue(), false)) {
				$user = new User;
				$user->email = $this->getField('email')->getValue();
				$user->status = User::STATUS_PENDING;
				if (neon()->user->isSuper())
					$user->super = $this->getField('super')->getValue();
				$user->setPassword(neon()->security->generateRandomString(32));
				if (!$user->save()) {
					return false;
				}
			}
			/**
			 * Do we want to restrict invitation sending to just pending users?
			 */
			// if ($user->status != User::STATUS_PENDING)
			// 	return false;
			$user->setRoles($this->getField('roles')->getValue());
			$userInvite = new UserInviteModel;
			$userInvite->user_id = $user->getUuid();
			$userInvite->generateUserInviteToken();
			if (!$userInvite->save()) {
				return false;
			}
			$result = $this->sendEmail($userInvite->getToken());

			// We could create a custom Event class here which could have a success property
			$this->getUserApp()->triggerUserInvited($this);

			return $result;
		}
		return false;
	}

	/**
	 * Get the User app instance
	 * @return \neon\user\App
	 */
	public function getUserApp()
	{
		return neon('user');
	}

	/**
	 * Sends an email with a link for completing the account registration
	 * @param  string  $inviteToken  the invite token to send to the user
	 * @throws \Exception if the From email address is not set
	 * @return boolean whether the email was sent successfully
	 */
	public function sendEmail($inviteToken)
	{
		return UserInviteService::send($inviteToken);
	}

}
