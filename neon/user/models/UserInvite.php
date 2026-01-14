<?php

namespace neon\user\models;

use Carbon\Carbon;
use neon\core\helpers\Hash;
use neon\core\helpers\Html;
use neon\core\db\ActiveRecord;
use neon\user\interfaces\iUserInvite;
use neon\user\models\User;

/**
 * User Invite model
 *
 * @property string $token
 * @property string $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $expires_at
 */
class UserInvite extends ActiveRecord
{
	/**
	 * @var integer  time limit after which the token will expire (default 7 days)
	 */
	public $userInviteTokenExpire = 604800;

	/**
	 * @inheritdoc
	 */
	public static $blame = true;

	/**
	 * @inheritdoc
	 */
	public static $timestamps = true;

	/**
	 * @var string
	 */
	public static $invalidTokenMessage = 'This invitation is invalid';

	/**
	 * @var string
	 */
	public static $userAlreadyRegisteredMessage = 'You have already finished registering your account';

	/**
	 * @var string
	 */
	public static $tokenExpiredOnMessage = 'This invite expired on {{expired_at}}. Contact {{admin}} to request a new one.';
		
	/**
	 * @var string
	 */
	public static $successfulRegistrationMessage = 'Your account is now registered. You can login at any time with the password you set.';
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_invite}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = [
			['user_id', 'string'],
			[['token', 'user_id'], 'required']
		];
		return $rules;
	}

	/**
	 * Generates a new user invite token
	 */
	public function generateUserInviteToken()
	{
		$this->token = neon()->security->generateRandomString();
		$this->expires_at = date('Y-m-d H:i:g', time() + $this->userInviteTokenExpire);
	}

	/**
	 * Get the invite token
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Get a human readable string of time until the invite token expires
	 * for e.g. '24 hours'
	 * @return string
	 */
	public function inviteTokenExpiresIn()
	{
		return Carbon::now()->diffForHumans($this->expires_at, true);
	}

	/**
	 * Get hold of an invitation by its token
	 * @param string $token invite token
	 * @param boolean $ignoreExpiry whether to ignore the token expiry
	 * @return null|UserInvite
	 */
	public static function findInviteByInviteToken($token, $ignoreExpiry = false)
	{
		if (!$ignoreExpiry && self::hasInviteTokenExpired($token))
			return null;
		
		return self::findOne(['token' => $token]);
	}

	/**
	 * Finds out if invite token is valid
	 * @param string $token invite token
	 * @return boolean
	 */
	public static function isInviteTokenValid($token)
	{
		if (empty($token)) { return false; }

		$invite = self::getInvite($token);
		if (!$invite) { return false; }

		if (!User::findOne(['uuid' => $invite->user_id, 'status' => User::STATUS_PENDING])) { return false; }

		return !self::hasInviteTokenExpired($token);
	}

	/**
	 * @param string $token
	 * @return UserInvite|null
	 */
	public static function getInvite($token)
	{
		static $_invite;
		if ($_invite === null){
			$_invite = self::findOne(['token' => $token]);
		}
		return $_invite;
	}

	/**
	 * Check if the invite token has expired
	 * @param string $token invite token
	 * @return boolean - true if expired
	 */
	public static function hasInviteTokenExpired($token)
	{
		$invite = self::getInvite($token);
		if (!$invite) return false;
		return strtotime($invite->expires_at) <= time();
	}

	/**
	 * Get a useful message about when the invite expired and who to contact about it
	 * @param  string  $token
	 * @return string
	 */
	public static function getInvalidInviteMessage($token)
	{
		// If the invite does not exist
		if (!($invite = self::getInvite($token))) {
			return self::$invalidTokenMessage;
		}
		// if the associated user does not exist - then we are invalid
		if (!($user = User::findOne(['uuid' => $invite->user_id]))) {
			return self::$invalidTokenMessage;
		}
		// If the user has already registered return the appropriate message
		if ($user->status !== \neon\user\models\User::STATUS_PENDING) {
			return self::$userAlreadyRegisteredMessage;
		}
		// If the invitation has passed it's expiration date return the appropriate message
		if (self::hasInviteTokenExpired($token)) {
			$inviter = User::findOne(['id' => $invite->created_by]);
			$admin = $inviter ? $inviter->getName() : 'unknown';
			return strtr(self::$tokenExpiredOnMessage, [
				'{{expired_at}}' => date('Y-m-d H:i', strtotime($invite->expires_at)),
				'{{admin}}' => $admin
			]);
		}
		// Otherwise return the generic message
		return self::$invalidTokenMessage;
	}
}