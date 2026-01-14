<?php

namespace neon\user\interfaces;

use neon\core\helpers\Iterator;


/**
 * Interface iUserManager
 * Defines the interface for creating and getting hold of Users
 */
interface IUserManagement
{
	/**
	 * list available user roles
	 * @return array[string]  [role,...]
	 */
	public function listUserRoles();

	/**
	 * add a user role
	 * @param string $role  the new user role
	 */
	public function addUserRole($role);

	/**
	 * destroy a user role
	 * @param string $role  the role to be destroyed
	 */
	public function destroyUserRole($role);


	/**
	 * List available users
	 * @param Iterator &$iterator
	 *   @see Iterator for use
	 * @return array  users as
	 *   [['uuid'=>'username']...]
	 */
	public function listUsers(Iterator &$iterator=null);

	/**
	 * Find a set of users based on a partial search string
	 * @param array $search  the search string
	 * @param Iterator &$iterator
	 *   @see Iterator for use
	 * @return array  users as
	 *   [['uuid'=>'username']...]
	 */
	public function searchUsers($search, Iterator &$iterator=null);

	/**
	 * Create a new User
	 * @param string $username
	 * @param string $email
	 * @param string $password
	 * @param array[string] $roles
	 * @param boolean $superuser  - whether this person is a superuser. The person
	 *   calling this must also be a superuser
	 * @return string  - the users uuid
	 */
	public function addUser($username, $email, $password, $roles=[], $superuser=false);

	/**
	 * Get hold of an User given the user uuid
	 * @param string $uuid  the uuid of the user you are after
	 * @return array [
	 *   'uuid', 'username', 'email', 'status', 'roles'
	 * ]
	 */
	public function getUser($uuid);

	/**
	 * Edit a user's parameters
	 * @param string $uuid  the uuid of the user you are changing
	 * @param array $changes - any of
	 *   'email', 'password', 'roles' and 'superuser'
	 * @return boolean|errors  true on success, false if not found and otherwise
	 *   any errors
	 */
	public function editUser($uuid, $changes);

	/**
	 * Destroy a user
	 * @param string $uuid
	 */
	public function destroyUser($uuid);
}

