<?php

namespace neon\user\services;

use neon\user\interfaces\IUserManagement;
use neon\user\models\User as UserModel;
use neon\core\helpers\Hash;
use neon\core\helpers\Iterator;

/**
 * Class UserManager
 * @package neon\user\web
 * Provides simple management methods for ladder operations for users
 */
class UserManager implements IUserManagement
{
	/**
	 * @inheritdoc
	 */
	public function listUserRoles()
	{
		return neon()->authManager->getRoles();
	}

	/**
	 * @inheritdoc
	 */
	public function addUserRole($role)
	{
		$auth = neon()->authManager;
		$authRole = $auth->createRole($role);
		if ($authRole)
			$auth->add($authRole);
	}

	/**
	 * @inheritdoc
	 */
	public function destroyUserRole($role)
	{
		$auth = neon()->authManager;
		$authRole = $auth->getRole($role);
		if ($authRole)
			$auth->remove($authRole);
	}

	/**
	 * @inheritdoc
	 */
	public function listUsers(Iterator &$iterator=null)
	{
		return $this->findUsers(null, $iterator);
	}

	/**
	 * @inheritdoc
	 */
	public function searchUsers($searchString, Iterator &$iterator=null)
	{
		return $this->findUsers(['like', 'username', $searchString], $iterator);
	}

	/**
	 * @inheritdoc
	 */
	public function addUser($username, $email, $password, $roles=[], $superuser=false)
	{
		$user = new UserModel;
		$user->uuid = Hash::uuid64();
		$user->username = $username;
		$user->email = $email;
		$user->setPassword($password);
		if ($superuser && neon()->user->isSuper())
			$user->super = $superuser;
		$user->generateAuthKey();
		$success = $user->save();
		if ($success) {
			if (count($roles)) {
				// requires the user to be created before roles can be assigned
				$user->setRoles($roles);
			}
			return $user->uuid;
		}
		return $user->errors;
	}

	/**
	 * @inheritdoc
	 */
	public function getUser($uuid)
	{
		$user = $this->findUserModel($uuid);
		if ($user) {
			return [
				'uuid'=>$user->uuid,
				'username'=>$user->username,
				'email'=>$user->email,
				'superuser'=>(boolean)$user->super,
				'status' => $user->status,
				'roles'=>$user->getRoles()
			];
		}
		return null;
	}

	/**
	 * Edit a user's parameters
	 * @param string $uuid  the uuid of the user you are changing
	 * @param array $changes - any of
	 *   'email', 'password', 'roles' and 'superuser'
	 */
	public function editUser($uuid, $changes)
	{
		$user = $this->findUserModel($uuid);
		if ($user) {
			$newValues = array_intersect_key($changes, array_flip(['email','password','roles']));
			foreach ($newValues as $key=>$value) {
				$user->$key = $value;
			}
			if (isset($changes['superuser']))
				$user->__set_super($changes['superuser']);
			$success = $user->save();
			return $success ? true : $user->errors;
		}
		return false;
	}

	/**
	 * Destroy a user
	 * @param string $uuid
	 */
	public function destroyUser($uuid)
	{
		$user = $this->findUserModel($uuid);
		if ($user)
			$user->delete();
	}


	/**
	 * Get hold of the user model from the uuid
	 * @param string $uuid
	 * @return neon\user\models\User
	 */
	private function findUserModel($uuid)
	{
		return UserModel::find()->where(['uuid'=>$uuid])->one();
	}

	/**
	 * Method to find users.
	 * @param array $where  if set, then this is the where parameter
	 *   for the query.
	 * @param Iterator $iterator  an iterator to go over the query
	 */
	private function findUsers($where, Iterator &$iterator=null) 
	{
		$iterator = $iterator ? $iterator : new Iterator;

		// set up our pagination
		if ($iterator->shouldReturnTotal()) {
			$iterator->total = UserModel::find()->count();
		}

		// get the rows
		$query = UserModel::find()->select(['uuid','username'])
			->limit($iterator->length)
			->offset($iterator->start)
			->orderBy('username');

		if ($where)
			$query->where($where);
		
		$userRows = $query->all();

		// and return in required format
		$users = [];
		foreach ($userRows as $ur)
			$users[$ur['uuid']] = $ur['username'];
		return $users;
	}


}
