<?php

namespace neon\user\forms;

use neon\user\models\User as User;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class Login extends Model
{
	public $username;
	public $password;
	public $rememberMe = true;

	private $_user = false;


	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			// username and password are both required
			[['username', 'password'], 'required'],
			// rememberMe must be a boolean value
			['rememberMe', 'boolean'],
			// Check the user is active (not suspended or deleted)
			['username', 'validateActive'],
			// password is validated by validatePassword()
			['password', 'validatePassword'],
			
		];
	}

	public function attributeLabels()
	{
		return [
			'rememberMe' => 'Keep me logged in'
		];
	}

	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			if (!$user || !$user->validatePassword($this->password)) {
				$this->addError($attribute, 'Incorrect username or password.');
			}
		}
	}
	
	/**
	 * Validate that the users account is active
	 * 
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateActive($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			if ($user && $user->status !== User::STATUS_ACTIVE) {
				switch ($user->status) {
					case User::STATUS_SUSPENDED:
						// check if too many log in attempts
						if ($user->hasTooManyFailedLoginAttempts()) {
							$this->addError($attribute, 'You have made too many failed log in attempts. Your account has been suspended');
						}
						$this->addError($attribute, 'Your account has been suspended');
					break;
//					case User::STATUS_DELETED:// no such status currently defined!
//						$this->addError($attribute, 'Your account has been deleted');
					break;
					default:
						$this->addError($attribute, 'Your account is not currently active');
					break;
				}
				// increment failed log in attempts (if we fail at this stage the password wil not be checked, hence the failed attempt not recorded)
				// but we still want to record the failed login attempt
				$user->failed_logins = $user->failed_logins + 1;
				$user->save();
			}
		}
	}

	/**
	 * Logs in a user using the provided username and password.
	 * @return boolean whether the user is logged in successfully
	 */
	public function login()
	{
		$user = $this->getUser();
		if ($this->validate()) {
			$duration = $this->rememberMe ? neon()->user->rememberMeDuration : 0;
			$loggedIn = neon()->user->login($user, $duration);
			return $loggedIn;
		}
		// an else statement here could catch all failed log in attempts.
		// however I have for now added the failure count at a lower level to ensure that programatic 
		// attempts also get recorded.
		return false;
	}
	
	/**
	 * Get the number of login attempts (tries) the user has made
	 * @return int
	 */
	public function tries()
	{
		$user = $this->getUser();
		if ($user) {
			return $user->failed_logins;
		}
		return 0;
	}

	/**
	 * Finds user by [[username]]
	 * @param boolean $activeOnly whether to fetch only active users
	 * @return User|null
	 */
	public function getUser($activeOnly=false)
	{
		if ($this->_user === false) {
			$this->_user = call_user_func(
				[neon()->getUser()->identityClass, 'findByUsername'],
				$this->username,
				$activeOnly
			);
		}
		return $this->_user;
	}

	/**
	 * Allow inheriting classes to set the user
	 */
	protected function setUser($user)
	{
		$this->_user = $user;
	}
}
