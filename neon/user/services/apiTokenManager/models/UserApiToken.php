<?php

namespace neon\user\services\apiTokenManager\models;

use Carbon\Carbon;
use neon\core\db\ActiveRecord;
use neon\user\models\User;
use yii\web\IdentityInterface;

/**
 * Represents the user api token table - the token allows the system to validate users - note you should also check the
 * validity of the connected user specifically - their status - are they active? are they suspended and do they have access
 *
 * @property string $token
 * @property bool $active
 * @property int $user_id
 * @property string $name
 * @property string $last_used - MYSQL datetime format Y-m-d H:i:g
 * @property integer $used_count - A simple count tracking the number of times the token was used
 */
class UserApiToken extends ActiveRecord
{
	/**
	 * The length of the token to generate (max 64)
	 * @var int
	 */
	private static $_tokenLength = 64;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_api_token}}';
	}

	/**
	 * Basic token sanity check
	 * @param string $token
	 * @return boolean true if valid false if not
	 */
	public static function isValidToken($token)
	{
		// Check the token is valid. A record must exist and the token needs to be a string of the correct length
		// Testing the length prevents 0 length tokens or easy to guess tokens being found, and valid, if somehow they were created.
		return ($token !== null && strlen($token) >= static::$_tokenLength);
	}

	/**
	 * Returns the valid user by auth token
	 * The user returned can be used to log in
	 * @param string $token
	 * @return IdentityInterface
	 */
	public static function getValidUserByToken($token)
	{
		$validToken = self::isValidToken($token);
		if (!$validToken)
			return null;

		$apiToken = self::findOne(['active' => 1, 'token' => (string) $token]);
		if ($apiToken == null)
			return null;

		// be sure to honour the identity class if one has been specified (for example by maybe extending the neon User model)
		$user = call_user_func(neon()->user->identityClass."::findOne",['id' => $apiToken->user_id, 'status' => User::STATUS_ACTIVE]);
		if ($user == null)
			return null;

		// Usage successful
		$apiToken->touchTokenUsed();

		return $user;
	}

	/**
	 * Updates a token row with last used date and increments the usage count
	 * @return boolean true on success false on failure
	 */
	public function touchTokenUsed()
	{
		$this->last_used = date('Y-m-d H:i:g');
		$this->used_count = $this->used_count + 1;
		return $this->save();
	}

	/**
	 * Generate the authorization token to include in basic auth http request "Authorization" header
	 * @return string
	 */
	public function getHttpBasicAuthorizationBase64()
	{
		return base64_encode($this->user_id . ':' . $this->token);
	}

	/**
	 * Get the token for the specified user
	 *
	 * @param int $userId
	 * @return self|null
	 */
	public static function getTokenRecord($userId)
	{
		return static::findOne(['user_id' => $userId]);
	}

	/**
	 * Generate a token for a specified userId
	 * @param int $userId
	 * @param string $name
	 * @return UserApiToken|null
	 */
	public static function generateTokenFor($userId, $name='')
	{
		$token = UserApiToken::getTokenRecord($userId);
		// extra security:
		// validate the user id points to a valid user
		$user = call_user_func(neon()->user->identityClass."::findOne",['id' => $userId, 'status' => User::STATUS_ACTIVE]);
		if ($user === null) {
			throw new \InvalidArgumentException("Tokens can only be generated for valid users. No active user exists with id '$userId' ");
		}
		$token = ($token === null) ? new UserApiToken() : $token;
		$token->name = $name;
		$token->token = neon()->security->generateRandomString(static::$_tokenLength);
		$token->user_id = $userId;
		$token->active = 1;
		$token->save();
		return $token;
	}

	/**
	 * Generate or refresh a token for the current logged in user.
	 * @return UserApiToken
	 */
	public static function generateToken()
	{
		if (neon()->user->isGuest) {
			throw new \InvalidArgumentException('You can not generate a token for a guest user');
		}
		$userId = neon()->user->id;
		return self::generateTokenFor($userId);
	}
}
