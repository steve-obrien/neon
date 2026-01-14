<?php

namespace neon\tests\unit\user;

use \Codeception\Util\Debug;
use neon\core\helpers\Str;
use Yii;

use \neon\core\test\Unit;
use neon\user\services\UserManager;


/**
 * Class UserManagerTest
 * @package neon\tests\unit\user
 */
class UserManagerTest extends Unit
{
	public function testUserLifeCycle()
	{
		// see how many users we already have
		$userManager = new UserManager;
		$users = $userManager->listUsers();
		$initialUserCount = count($users);

		// add a new one
		$username = $this->randomString();
		$email = $this->randomString().'@newicon.test';
		$password = $this->randomString();
		$userUuid = $userManager->addUser($username, $email, $password);
		$this->assertNotNull($userUuid);

		// check it's listed
		$this->assertCount(($initialUserCount+1), $userManager->listUsers());

		// check we can find it from searching
		$this->assertCount(1, $userManager->searchUsers($username));

		// get it back again
		$user = $userManager->getUser($userUuid);
		$this->assertEquals($username, $user['username']);
		$this->assertEquals($email, $user['email']);
		$this->assertEmpty($user['roles']);
		$this->assertFalse($user['superuser']);
		$this->assertArrayNotHasKey('password', $user);

		// edit it & check values
		$uuid = $this->randomString();
		$newUsername = $this->randomString();
		$newEmail = $this->randomString().'@newicon.test';
		$result = $userManager->editUser($userUuid, ['uuid'=>$uuid, 'username'=>$newUsername, 'email'=>$newEmail]);
		$this->assertTrue($result);
		$user = $userManager->getUser($userUuid);
		$this->assertEquals($username, $user['username']); // cannot change username
		$this->assertEquals($newEmail, $user['email']);

		// delete it
		$userManager->destroyUser($userUuid);
		// check you can't get it back
		$this->assertNull($userManager->getUser($userUuid));
		// check it isn't listed
		$this->assertEquals($initialUserCount, count($userManager->listUsers()));
	}

	public function testUserRoleLifeCycle()
	{
		$userManager = new UserManager;
		$roleList = $userManager->listUserRoles();
		$initialCount = count($roleList);
		$role = $this->randomString();
		$userManager->addUserRole($role);
		$newRoleList = $userManager->listUserRoles();
		$this->assertCount(($initialCount+1), $newRoleList);
		$userManager->destroyUserRole($role);
		$newRoleList = $userManager->listUserRoles();
		$this->assertCount($initialCount, $newRoleList);
	}
}
