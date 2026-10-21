<?php

namespace neon\user\models;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use neon\core\helpers\Hash;
use neon\core\helpers\Html;
use neon\core\db\ActiveRecord;
use neon\core\Env;
use neon\user\services\apiTokenManager\JwtTokenAuth;
use yii\web\IdentityInterface;
use neon\user\interfaces\iUser;
use neon\user\services\apiTokenManager\models\UserApiToken;
use neon\user\models\UserInvite;

/**
 * User model
 *
 * @property string $uuid
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $status - A user is only valid to login if their status is User::STATUS_ACTIVE
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property int $failed_logins the number of failed login attempts made by this user
 * @property int $logins the number of times the user has logged in
 * @property string $last_login the string mysql date time format (Y-m-d H:i:s) of the last successful login
 */
class User extends ActiveRecord implements
	IdentityInterface,
	iUser
{
	public static $passwordHashCost = 4;

	/**
	 * @inheritdoc
	 */
	public static $idIsUuid64 = true;
	/**
	 * Id is already taken so create a specific uuid column
	 */
	const UUID = 'uuid';
	/**
	 * @inheritdoc
	 */
	public static $timestamps = true;

	/**
	 * When a use is suspended can be automatically set if a user has too many password attempts
	 */
	const STATUS_SUSPENDED = 'SUSPENDED';
	/**
	 * The only valid user status for successful login
	 */
	const STATUS_ACTIVE = 'ACTIVE';
	/**
	 * A user can be banned from the system and not be allowed to log in again
	 */
	const STATUS_BANNED = 'BANNED';
	/**
	 * A user can be pending if they have not yet registered
	 */
	const STATUS_PENDING = 'PENDING';

	/**
	 * @var callable for checking a password's strength before allowing it to be set
	 */
	private static $passwordPolicyChecker  = null;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_user}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = [
			['username', 'string', 'min' => 2, 'max' => 255],
			['email', 'email'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_BANNED, self::STATUS_PENDING]],
			['password_hash', 'string', 'min' => 6],
			[['password_hash', 'email'], 'required'],
		];
		if ($this->isNewRecord) {
			$rules[] = ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'];
			$rules[] = ['username', 'unique', 'targetClass' => '\neon\user\models\User', 'message' => 'This username has already been taken.'];
			$rules[] = ['email', 'unique', 'targetClass' => '\neon\user\models\User', 'message' => 'This email address has already been taken.'];
		} else {
			// for importing from other places allow a more generous pattern
			$rules[] = ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\- \.@]+$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'];
		}
		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function fields()
	{
		$fields = parent::fields();
		// remove fields that contain sensitive information
		unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);
		return $fields;
	}

	/**
	 * @inheritdoc
	 */
	public function getUuid()
	{
		return $this->uuid;
	}

	/**
	 * Caching of user roles so repeated checks don't result
	 * in lots of database queries
	 * @var array
	 */
	protected static $_userRoles = [];

	/**
	 * @inheritdoc
	 */
	public function getRoles()
	{
		if (!isset(static::$_userRoles[$this->id]))
			static::$_userRoles[$this->id] = array_keys(neon()->authManager->getRolesByUser($this->id));
		return is_array(static::$_userRoles[$this->id]) ? static::$_userRoles[$this->id] : [];
	}

	/**
	 * @inheritdoc
	 */
	public function setRoles(array $roles)
	{
		$authManager = neon()->authManager;
		$authManager->revokeAll($this->id);
		foreach ($roles as $role) {
			$r = $authManager->getRole($role);
			if ($r)
				$authManager->assign($r, $this->id);
		}
		unset(static::$_userRoles[$this->id]);
	}

	/**
	 * @inheritdoc
	 */
	public function hasRole($role)
	{
		return $this->hasRoles([$role]);
	}

	/**
	 * @inheritdoc
	 */
	public function hasRoles(array $roles)
	{
		$userRoles = $this->getRoles();
		return (count(array_intersect($userRoles, $roles)) > 0);
	}

	/**
	 * @inheritdoc
	 */
	public function getHomeUrl($role=null)
	{
		$userRoles = $this->getRoles();
		$availableRoles = neon('user')->getRoles(true);

		// searching in a specific role?
		if (!empty($role)) {
			if (isset($availableRoles[$role]['homeUrl']))
				return $availableRoles[$role]['homeUrl'];
		} else { // or finding any one?
			foreach ($userRoles as $role) {
				if (isset($availableRoles[$role]['homeUrl']))
					return $availableRoles[$role]['homeUrl'];

			}
		}
		return null;
	}


	public static function setPasswordPolicyChecker(callable $checker) {
		self::$passwordPolicyChecker = $checker;
	}

	/**
	 * Get the list of possible status states for this record
	 * @return array
	 */
	public static function getStatusChoices()
	{
		return [
			self::STATUS_ACTIVE => 'Active',
			self::STATUS_SUSPENDED => 'Suspended',
			self::STATUS_BANNED => 'Banned',
			self::STATUS_PENDING => 'Pending'
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		// The id must be a uuid64
		if (!Hash::isUuid64($id))
			return null;
		// The string conversion here is important to ensure the id is correctly quoted as a string
		// otherwise if the $id is a number it will match uuid's that start with that number!
		return self::findOne(['uuid' => (string) $id, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		if ($type === 'neon\user\services\apiTokenManager\JwtTokenAuth') {
			return JwtTokenAuth::findAndValidateUserIdentity($token);
		} else {
			return UserApiToken::getValidUserByToken($token);
		}
	}

	/**
	 * Get hold of the user model associated with the token
	 * @param string $token invite token
	 * @return null|User
	 */
	public static function findIdentityByInviteToken($token)
	{
		$invite = UserInvite::findInviteByInviteToken($token);
		if (!$invite)
			return null;
		return User::findOne(['uuid' => $invite->user_id, 'status' => [User::STATUS_PENDING]]);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @param boolean $activeOnly if true only return active users
	 * @return static|null
	 */
	public static function findByUsername($username, $activeOnly = true)
	{
		$field = (strpos($username, "@")) ? 'email' : 'username';
		$filter = [$field => $username];
		if ($activeOnly) {
			$filter['status'] = self::STATUS_ACTIVE;
		}
		return static::findOne($filter);
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token)
	{
		if (!static::isPasswordResetTokenValid($token)) {
			return null;
		}
		return static::findOne([
				'password_reset_token' => $token,
				'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 * @return boolean
	 */
	public static function isPasswordResetTokenValid($token)
	{
		if (empty($token)) { return false; }
		$timestamp = self::getPasswordResetTokenExpireTime($token);
		return $timestamp >= time();
	}

	/**
	 * Check if the token is expired
	 *
	 * @param  string  $token
	 * @return int timestamp
	 */
	public static function hasPasswordResetTokenExpired($token)
	{
		return !static::isPasswordResetTokenValid($token);
	}

	/**
	 * Get the expire time of
	 *
	 * @param  string  $token
	 * @return int
	 */
	public static function getPasswordResetTokenExpireTime($token)
	{
		$parts = explode('_', $token);
		return (int) end($parts) + neon()->user->passwordResetTokenExpire;
	}

	/**
	 * Get a human readable string of time until the password token expires
	 * for e.g. '5 minutes'
	 * @param  string  $token  The password reset token
	 * @return string
	 */
	public static function passwordResetTokenExpiresIn($token)
	{
		$expires = self::getPasswordResetTokenExpireTime($token);
		return Carbon::now()->diffForHumans(Carbon::createFromTimestamp($expires), true);
	}

	/**
	 * The key for the userlist cache
	 * @see \neon\user\models\User::getUserList()
	 */
	const CACHE_KEY_USER_LIST = 'USER_LIST';

	/**
	 * Get a list of users in a format suitable for a drop down
	 * @param array|string $filters
	 * @param array $fields
	 * @param int $start - the row offset to start at
	 * @param int $length - the total number of rows to return
	 * @see getUserListUncached for details
	 * @return array
	 */
	public static function getUserList($query='', $filters=[], $fields=[], $start=0, $length=100)
	{
		// because we need to cache according to the args we cannot store
		// results in cache rather cacheArray. This is so we can clear cache
		// appropriately in other calls such as edit and delete. Also user
		// information should not be available in cached files
		return neon()->cacheArray->getOrSet(
			[static::CACHE_KEY_USER_LIST,func_get_args()],
			function() use ($query, $filters, $fields, $start, $length) {
				return self::getUserListUncached($query, $filters, $fields, $start, $length);
			}
		);
	}

	/**
	 * Get a list of users in a format suitable for a drop down
	 * This method is uncached. Unless you need an updated list use
	 * @see getUserList
	 * @see \neon\user\form\fields\UserSelector
	 * @see \neon\user\App::getDataMapTypes()
	 * @param array $filters
	 * @param array $fields
	 * @param int $start - the row offset to start at
	 * @param int $length - the total number of rows to return
	 * @return array
	 */
	public static function getUserListUncached($query='',$filters=[], $fields=[], $start=0, $length=100)
	{
		$q = self::find()->select(array_merge(['uuid', 'username', 'email'], $fields));
		if ($q !== '')
			$q->where(['or', ['like', 'username', $query], ['like', 'email', $query]]);
		if (!empty($filters))
			$q->andWhere($filters);
		$q->offset($start)
			->limit($length)
			->orderBy(['username' => SORT_ASC, 'email' => SORT_ASC])
			->asArray();
		return self::formatDataMapItems($q->all(), $fields);
	}

	/**
	 * Formats a list of user rows into appropriate format for use in a map
	 * for e.g. a drop down list
	 * @param array $userRows - user db record rows as assoc arrays
	 * @param array $fields - list of additional fields to get
	 * @return array
	 */
	public static function formatDataMapItems($userRows, $fields)
	{
		return collect($userRows)->mapWithKeys(function($row, $key) use($fields) {
			if (empty($fields))
				return [$row['uuid'] => $row['username'] ? $row['username'] . ' - ' . $row['email'] : $row['email']];
			return [$row['uuid'] => $row];
		})->all();
	}

	/**********************************************************************************
	 * Instance methods
	 */

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->uuid;
	}

	/**
	 * @inheritdoc
	 * For added security we hash the auth_key
	 * Yii provides security on cookies too... When accessed through the request and response
	 * objects (a typical controller based request lifecycle). But just in case we
	 * hash the auth_key so the cookie only ever has an encrypted version
	 * with the auth_key field being the key
	 */
	public function getAuthKey()
	{
		profile_begin('getAuthKey');
		$thing = JWT::urlsafeB64Encode(neon()->security->encryptByKey($this->getAttribute('auth_key'), Env::getNeonEncryptionKey()));
		profile_end('getAuthKey');
		return $thing;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey)
	{
		profile_begin('validateAuthKey');
		$res = ($this->getAttribute('auth_key') === neon()->security->decryptByKey(JWT::urlsafeB64Decode($authKey), Env::getNeonEncryptionKey()));
		profile_end('validateAuthKey');
		return $res;
	}

	/**
	 * Sets a hashed password against the user
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		if (self::$passwordPolicyChecker !== null) {
			$checker = self::$passwordPolicyChecker;
			$result = $checker($password, $this);
			if (!$result) {
				throw new \InvalidArgumentException('Password does not meet policy requirements.');
			}
		}
		$this->password_hash = neon()->user->generatePasswordHash($password);
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		$passwordValid = neon()->security->validatePassword($password, $this->password_hash);
		// keep a record of password incorrect login attempts
		if ($passwordValid) {
			$this->failed_logins = 0;
			$this->save();
		} else {
			$this->failed_logins = $this->failed_logins + 1;
			// suspend the account if we have had too many attempts
			if ($this->hasTooManyFailedLoginAttempts()) {
				$this->status = self::STATUS_SUSPENDED;
			}
			$this->save();
		}
		return $passwordValid;
	}

	/**
	 * Returns true if the user has made too many failed log in attempts
	 *
	 * @return boolean
	 */
	public function hasTooManyFailedLoginAttempts()
	{
		return ($this->failed_logins >= neon()->user->failedLoginAttempts);
	}

	/**
	 * Whether this user record is suspended
	 *
	 * @return bool
	 */
	public function isSuspended()
	{
		return ($this->status == static::STATUS_SUSPENDED);
	}

	/**
	 * Password is readonly
	 * @return null
	 */
	public function getPassword()
	{
		return null;
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey()
	{
		$this->auth_key = neon()->security->generateRandomString(100);
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken()
	{
		$this->password_reset_token = neon()->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken()
	{
		$this->password_reset_token = null;
	}

	/**
	 * Generate an auth key if this is creating a new user
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		neon()->cacheArray->delete(static::CACHE_KEY_USER_LIST);
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->generateAuthKey();
			}
			return true;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		// remove any caches and authorisations against the user
		neon()->cacheArray->delete(static::CACHE_KEY_USER_LIST);
		if ($this->id)
			neon()->authManager->revokeAll($this->id);
		return parent::beforeDelete();
	}

	/**
	 * Called by the web User class after a successful login
	 */
	public function afterLogin()
	{
		$this->logins = $this->logins + 1;
		$this->last_login = date('Y-m-d H:i:s');
		// could log the users IP here too $ip = Yii::$app->getRequest()->getUserIP();
		$this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function getUsername()
	{
		return $this->getAttribute('username');
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		$username = $this->getAttribute('username');
		return $username ? $username : $this->getEmail();
	}

	/**
	 * @inheritdoc
	 */
	public function getEmail()
	{
		return $this->getAttribute('email');
	}

	/**
	 * @inheritdoc
	 */
	public function getPasswordHash()
	{
		return $this->getAttribute('password_hash');
	}

	/**
	 * Set the super property
	 * @param $value
	 */
	public function __set_super($value)
	{
		// only allow other super uses to make super users!
		if (neon()->user->isSuper()) {
			$this->setAttribute('super', $value);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function isSuper()
	{
		return $this->getAttribute('super');
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHints()
	{
		return [
			'username' => 'A unique username',
			'super' => 'Make this user a super user, a super user automatically has permission to do everything'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getImageUrl($size=40)
	{
		return Html::gravatar($this->email, $size, 'mm', 'g');
	}
}
