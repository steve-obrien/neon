<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\user;

use neon\core\helpers\Hash;
use neon\core\interfaces\IDataMapProvider;
use neon\user\forms\UserInvite;
use neon\user\models\User;
use yii\base\Event;

/**
 * The neon core user class
 */
class App extends \neon\core\BaseApp
implements IDataMapProvider
{
	/**
	 * Event triggered when a user has successfully been invited
	 * This event is useful for attaching additional information to the
	 * pending user record (the user who has been invited).
	 * For example associating the user with application specific details
	 */
	const EVENT_ON_USER_INVITED = 'EVENT_ON_USER_INVITED';

	/**
	 * This event is fired when the user invite forms 'init' method is called
	 * This is useful for adding additional
	 */
	const EVENT_ON_USER_INVITE_FORM_INIT = 'EVENT_ON_USER_INVITE_FORM_INIT';

	/**
	 * @inheritdoc
	 */
	public function configure() {
		$this->set('IUserManagement', [
			'class' => '\neon\user\services\UserManager'
		]);
	}

	/**
	 * request an IUserManagement interface
	 * @return \neon\user\interfaces\iUserManagement
	 */
	public function getIUserManagement() {
		return $this->get('IUserManagement');
	}

	/**
	 * @inheritdoc
	 */
	public function getMenu()
	{
		return [
			'visible' => !neon()->getUser()->isGuest && neon()->user->isSuper(),
			'label' => 'Users',
			'order' => 1600,
			'url' => ['/user/index/index'],
			'active' => is_route('/user*')
		];
	}

	/**
	 * Trigger the user has successfully been invited event
	 * This event is useful for attaching additional information to the
	 * pending user record (the user who has been invited).
	 * For example associating the user with application specific details
	 * The passed event will have its $sender property set to the $userInvite form
	 * @param UserInvite $userInvite - the user invite form
	 * @return Event
	 */
	public function triggerUserInvited(UserInvite $userInvite)
	{
		$event = new Event(['sender' => $userInvite]);
		neon('user')->trigger(self::EVENT_ON_USER_INVITED, $event);
		return $event;
	}

	/**
	 * This event is fired when the user invite forms 'init' method is called
	 * This is useful for adding additional form fields to the form such as company or project drop downs etc
	 * @param UserInvite $userInvite
	 * @return Event
	 */
	public function triggerUserInviteFormInit(UserInvite $userInvite)
	{
		$event = new Event(['sender' => $userInvite]);
		neon('user')->trigger(self::EVENT_ON_USER_INVITE_FORM_INIT, $event);
		return $event;
	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		// set up rules
		neon()->urlManager->addRules([
		 	'login' => '/user/account/login',
		 	'logout' => '/user/account/logout'
		]);
	}

# ---------------------------------------------------------------------------------------------------------------
# region: IDataMapProvider Functions
# ---------------------------------------------------------------------------------------------------------------
# Functions to satisfy the map interface
# The user App provides a system users map showing a map of all users in the
#

	/**
	 * @inheritdoc
	 */
	public function getDataMapTypes()
	{
		return [
			'users' => 'User'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getDataMap($key, $query='', $filters=[], $fields=[], $start=0, $length=100)
	{
		if ($key === 'users') {
			return User::getUserList($query, $filters, $fields, $start, $length);
		} else {
			return [];
		}
	}

	/**
	 * @var string[] - string requestKey to array of makeMapLookupRequest arguments
	 */
	private $_mapRequestKey = null;

	/**
	 * @inheritdoc
	 */
	public function getMapResults($requestKey)
	{
		if (!isset($this->_mapRequestKey[$requestKey]))
			throw new \InvalidArgumentException('The provided request key "'.$requestKey.'" does not exist');
		list($key, $ids, $fields) = $this->_mapRequestKey[$requestKey];
		if ($key === 'users') {
			return User::getUserList('', ['uuid' => $ids]);
		}
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($key, $ids, $fields = [])
	{
		$requestKey = md5(serialize(func_get_args()));
		$this->_mapRequestKey[$requestKey] = func_get_args();
		return $requestKey;
	}

# endregion
# ---------------------------------------------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function install()
	{
		parent::install();
		// set up default roles:
		$defaultRoles = ['neon-administrator', 'neon-developer'];
		$authManager = \Neon::$app->authManager;
		foreach ($defaultRoles as $role) {
			$admin = $authManager->getRole($role);
			if ($admin === null) {
				$admin = $authManager->createRole($role);
				$authManager->add($admin);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [
			'failedLoginAttempts' => [
				'name' => 'failedLoginAttempts',
				'class' => '\neon\core\form\fields\Integer',
				'label' => 'Number of Failed Login Attempts Allowed',
				'value' => neon()->user->getFailedLoginAttempts(),
				'hint' => 'Set the number of failed login attempts allowed until the account is suspended'
			],
			'rememberMeDuration' => [
				'name' => 'rememberMeDuration',
				'class' => '\neon\core\form\fields\Select',
				'label' => 'Web Session (Remember Me) Duration',
				'items' => [
					3600 => '1 hour',
					14400 => '4 hour',
					28800 => '8 hour',
					43200 => '12 hour',
					86400 => '24 hour',
					604800 => '7 days',
					1209600 => '14 days',
					2592000 => '30 days',
					7776000 => '90 days',
				],
				'value' => neon()->user->rememberMeDuration,
				'hint' => 'Set the duration that the web session stays valid - also known as the remember me duration. A new duration setting applies when the user signs out or when the current session expires.'
			],
			'minimumPasswordLength' => [
				'class' => '\neon\core\form\fields\Integer',
				'label' => 'Minimum Password Length',
				'value' => setting('user', 'minimumPasswordLength', 8),
			],
			'allowUserInvitations' => [
				'class' => '\neon\core\form\fields\SwitchButton',
				'label' => 'Allow user invitations from within Neon',
				'value' => setting('user', 'allowUserInvitations', false)
			]
		];
	}

	/** ---------- Roles and Permissions ---------- **/

	private $_roles = [];
	private $_routes2Roles = [];
	private $_routes = [];

	/**
	 * Set the roles for the site from the configuration files
	 * @param boolean $inFull  if true then you get all information related to
	 *   the roles. If false then you get the list of roles
	 * @return array
	 */
	public function getRoles($inFull = false)
	{
		$roles = $this->_roles;
		// remove the guest role
		unset($roles['?']);
		// return the rest
		return $inFull ? $roles : array_keys($roles);
	}

	/**
	 * Set the roles for the site from the config
	 */
	public function setRoles(array $roles)
	{
		foreach ($roles as $role => $details) {
			if (!array_key_exists($role, $this->_roles))
				$this->_roles[$role] = ['routes'=>[]];
			if (array_key_exists('routes', $details)) {
				foreach ($details['routes'] as $r) {
					$this->_roles[$role]['routes'][$r] = $r;
					$this->_routes2Roles[$r][$role] = $role;
				}
				if (array_key_exists('homeUrl', $details))
					$this->_roles[$role]['homeUrl'] = $details['homeUrl'];
				$this->_roles[$role]['label'] = (array_key_exists('label', $details) ? $details['label'] : $role);
			}
		}
		$this->_routes = array_keys($this->_routes2Roles);
	}

	/**
	 * see if a route is restricted to a set of roles
	 * @param string $route  the route you want to match
	 * @param array &$roles  the roles that matched
	 * @return boolean  whether it matched any roles
	 */
	public function routeHasRoles($route, &$roles=null)
	{
		$roles = [];
		foreach ($this->_routes as $r) {
			if (preg_match("@$r@", $route)) {
				$roles = array_merge($roles, $this->_routes2Roles[$r]);
			}
		}
		// allow guest access if this is the only allowed role
		if (count($roles)==1 && isset($roles['?']))
			return false;
		// otherwise restrict to requiring a role if one matched
		return (count($roles) > 0);
	}

	/**
	 * Get the details for a particular role
	 * @param string $role
	 * @return null | array
	 */
	public function getRoleDetails($role)
	{
		return array_key_exists($role, $this->_roles) ? $this->_roles[$role] : null;
	}

}
