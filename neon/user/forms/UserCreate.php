<?php
namespace neon\user\forms;

use neon\core\form\Form;
use neon\user\models\User;
use yii\base\Model;
use Yii;

/**
 * User creation form for use by Administrators
 */
class UserCreate extends Form
{
	public $editing = false;
	const MIN_PASSWORD_LENGTH = 8;

	public function init()
	{
		$this->setName('UserCreate');

		$userNameValidators = [
				['required'],
				'filter' => ['filter' => 'trim'],
				'match'  => ['pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'],
				'unique' => ['targetClass' => '\neon\user\models\User', 'message' => 'This username has already been taken.'],
				'string' => ['min' => 2, 'max' => 255]
		];
		if ($this->editing)
			unset($userNameValidators['unique']);
		$this->addFieldText('username')
			->setLabel('Username')
			->setValidators($userNameValidators);

		$emailValidators = [
				['required'],
				['email'],
				'filter' => ['filter' => 'trim'],
				'string' => ['min'=>5, 'max' => 255],
				'unique' => ['targetClass' => '\neon\user\models\User', 'message' => 'This email address has already been taken.']
			];
		if ($this->editing)
			unset($emailValidators['unique']);
		$this->addFieldEmail('email')
			->setLabel('Email')
			->setValidators($emailValidators);

		$passwordMinLength = setting('user', 'minimumPasswordLength', self::MIN_PASSWORD_LENGTH);
		$passwordValidators = ['string' => ['min' => $passwordMinLength, 'message' => "The password must be at least $passwordMinLength characters long"]];
		
		$passwordLabel = 'Password';
		if (!$this->editing) {
			$passwordValidators[] = ['required'];
		} else {
			$passwordLabel = 'Password - set only if you are changing it';
		}
		$this->addFieldPassword('password')
			->setLabel($passwordLabel)
			->setValidators($passwordValidators);

		if (neon()->user->isSuper()) {
			$this->addFieldSwitch('super')
				->setLabel('Is Superuser?')
				->setOnLabel('Yes')
				->setOffLabel('No');
		}

		if ($this->editing) {
			$this->addFieldSelect('status')
				->setHint('A users status must be active for them to be able to log in. A users status may become suspended automatically by the system if they have made too many failed log in attempts')
				->setLabel('User Status?')
				->setItems(User::getStatusChoices());
		}

		$this->addFieldCheckList('roles')
			->setLabel('Assign Roles')
			->setItems($this->getRoles());

		$this->addFieldSubmit('Save');
	}

	public function getUsername()
	{
		return $this->getField('username')->getValue();
	}

	public function getEmail()
	{
		return $this->getField('email')->getValue();
	}

	public function getRoles()
	{
		$roles = neon('user')->getRoles(true);
		$roleItems = [];
		foreach ($roles as $role=>$r) {
			$roleItems[$role] = $r['label'];
		}
		return $roleItems;
	}

	public function signupNewUser(&$user=null)
	{
		if ($this->validate()) {
			$userClass = neon()->getUser()->identityClass;
			$user = new $userClass();
			$user->username = $this->getField('username')->getValue();
			$user->email = $this->getField('email')->getValue();
			$user->setPassword($this->getField('password')->getValue());
			if (neon()->user->isSuper())
				$user->super = $this->getField('super')->getValue();
			$user->generateAuthKey();
			$success = $user->save();
			if ($success) {
				// requires the user to be created before roles can be assigned
				$user->setRoles($this->getField('roles')->getValue());
			}
			return $success;
		}
		return false;
	}

}
