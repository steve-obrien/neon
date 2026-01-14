<?php
namespace neon\user\forms;

use neon\core\form\Form;
use yii\base\Model;
use Yii;

/**
 * User editing form for use by Administrators
 */
class UserEdit extends UserCreate
{
	protected $rolesField = null;

	public function init()
	{
		$this->editing = true;
		parent::init();
		$this->setName('UserEdit');
	}


	public function updateExistingUser($user)
	{
		$success = false;
		if ($this->validate()) {
			$user->username = $this->getField('username')->getValue();
			$user->email = $this->getField('email')->getValue();
			$password = $this->getField('password')->getValue();
			if (strlen($password) >= setting('user', 'minimumPasswordLength', self::MIN_PASSWORD_LENGTH))
				$user->setPassword($password);
			$user->generateAuthKey();
			$user->status = $this->getField('status')->getValue();
			if (neon()->user->isSuper())
				$user->super = $this->getField('super')->getValue();
			$success = $user->save();
			if ($success)
				$user->setRoles($this->getField('roles')->getValue());
		}
		return $success;
	}

	public function load($user = null)
	{
		parent::load($user);
		if ($user)
			$this->getField('roles')->setValue($user->getRoles());
	}
}
