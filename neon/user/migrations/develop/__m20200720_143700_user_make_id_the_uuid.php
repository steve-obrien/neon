<?php

use neon\core\db\Migration;

use \neon\user\models\User;
use \neon\user\services\apiTokenManager\models\UserApiToken;

class m20200720_143700_user_make_id_the_uuid extends Migration
{
	public function safeUp()
	{
		// get all the users
		$users = User::find()->all();

		// create a map of user int id to uuid
		$idToUuuid = collect($users)->pluck('uuid', 'id');

		// make the id column a uuid64 id
		$this->alterColumn(User::tableName(), 'id', $this->uuid64());
		$this->addColumn(User::tableName(), 'old_id', $this->integer());
		$this->alterColumn(UserApiToken::tableName(), 'user_id', $this->uuid64());

		$apiTokens = UserApiToken::find()->all();
		$tableApiToken = UserApiToken::tableName();
		foreach($apiTokens as $apiToken) {
			$this->execute("UPDATE $tableApiToken SET user_id = :uuid where user_id = :id", [
				':uuid' =>  $idToUuuid->get($apiToken->user_id),
				':id' =>  $apiToken->user_id
			]);
		}
		$tableUser = User::tableName();
		foreach($users as $user) {
			$this->execute("UPDATE $tableUser SET id = :uuid, old_id = :id where id = :id", [
				':uuid' => $idToUuuid->get($user->id),
				':id' => $user->id
			]);
		}

		$authTable = \neon\user\models\AuthAssignment::tableName();
		$authAssignments = \neon\user\models\AuthAssignment::find()->all();
		foreach($authAssignments as $auth) {
			$this->execute("UPDATE $authTable SET user_id = :uuid where user_id = :id", [
				':uuid' => $idToUuuid->get($auth->user_id),
				':id' => $auth->user_id
			]);
		}

		// update firefly created_by and updated_by
		$ffs = \neon\firefly\services\fileManager\models\FileManager::find()->all();
		$tableFfs = \neon\firefly\services\fileManager\models\FileManager::tableName();
		$this->alterColumn($tableFfs, 'created_by', $this->uuid64());
		$this->alterColumn($tableFfs, 'updated_by', $this->uuid64());
		foreach($ffs as $f) {
			$this->execute("UPDATE $tableFfs SET created_by = :uuid where created_by = :id", [
				':uuid' => $idToUuuid->get($f->created_by),
				':id' => $f->created_by
			]);
			$this->execute("UPDATE $tableFfs SET updated_by = :uuid where updated_by = :id", [
				':uuid' => $idToUuuid->get($f->updated_by),
				':id' => $f->updated_by
			]);
		}

	}

	public function safeDown()
	{
		// get all the users
		$users = User::find()->all();
		$tableUser = User::tableName();
		// create a map of user int id to uuid
		$uuidToId = collect($users)->pluck('old_id', 'id');

		$apiTokens = UserApiToken::find()->all();
		$tableApiToken = UserApiToken::tableName();
		foreach($apiTokens as $apiToken) {
			$this->execute("UPDATE $tableApiToken SET user_id = :id where user_id = :uuid", [
				':id' =>  $uuidToId->get($apiToken->user_id),
				':uuid' =>  $apiToken->user_id
			]);
		}

		foreach($users as $user) {
			$this->execute("UPDATE $tableUser SET id = :id where id = :uuid", [
				':id' => $uuidToId->get($user->id),
				':uuid' => $user->id
			]);
		}

		$authTable = \neon\user\models\AuthAssignment::tableName();
		$authAssignments = \neon\user\models\AuthAssignment::find()->all();
		foreach($authAssignments as $auth) {
			$this->execute("UPDATE $authTable SET user_id = :id where user_id = :uuid", [
				':id' => $uuidToId->get($auth->user_id),
				':uuid' => $auth->user_id
			]);
		}

		// update firefly created_by and updated_by
		$ffs = \neon\firefly\services\fileManager\models\FileManager::find()->all();
		$tableFfs = \neon\firefly\services\fileManager\models\FileManager::tableName();
		foreach($ffs as $f) {
			$this->execute("UPDATE $tableFfs SET created_by = :id where created_by = :uuid", [
				':id' => $uuidToId->get($f->created_by),
				':uuid' => $f->created_by
			]);
			$this->execute("UPDATE $tableFfs SET updated_by = :id where updated_by = :uuid", [
				':id' => $uuidToId->get($f->updated_by),
				':uuid' => $f->updated_by
			]);
		}
		$this->alterColumn($tableFfs, 'created_by', $this->integer());
		$this->alterColumn($tableFfs, 'updated_by', $this->integer());
		$this->alterColumn(User::tableName(), 'id', $this->integer());
		$this->alterColumn(UserApiToken::tableName(), 'user_id', $this->integer());
		$this->dropColumn(User::tableName(), 'old_id');
	}
}
