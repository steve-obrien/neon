<?php

namespace neon\install\forms;

use neon\install\helpers\InstallHelper;

class InstallDatabase extends \yii\base\Model
{
	public $name='';
	public $username;
	public $password;
	public $host = 'localhost';
	public $table_prefix;
	public $create_database;
	public $error_message;
	public $environment = 'dev';
	public $debug = 'true';
	public $timezone = 'Europe/London';

	public $isAjaxValidating = false;

	public function rules()
	{
		return [
			[['create_database', 'password', 'table_prefix'], 'safe'],
			[['name', 'username', 'host', 'environment', 'debug', 'timezone'], 'required'],
			[['name', 'username', 'host', 'password'], 'validateDatabase'],
		];
	}

	/**
	 * Sets the global neon()->db to the config defined in this object
	 * @return void
	 */
	public function setGlobalNeonDbConfig()
	{
		\Neon::configure(neon()->db, $this->getDbConfig());
		neon()->db->open();
	}

	/**
	 * checks if the database connection details are valid,
	 * attempts to connect to the databse, and interpret the error message if any
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateDatabase($attribute, $params)
	{
		$connection = new \neon\core\db\Connection($this->getDbConfig());
		$result = InstallHelper::checkDatabaseConnection($connection);
		if ($result == 0) {
			// there are no errors!
			return;
		} else {
			if ($result == InstallHelper::ERROR_DB_UNKNOWN_DATABASE) {
				// Attempt to create the database (if asked to do so)
				if ($this->create_database == 1 ) {
					if (! $this->createDatabase($exception)) {
						$this->error_message = 'cant_create_database';
						$this->addError('name', 'Failed to create database "' . $this->name . '" Database error: ' . $exception);
					}
				}  else {
					// This is useful information - but not an error therefore in
					if (! $this->isAjaxValidating) {
						$this->error_message = 'unknown_database';
						$this->addError('name', 'Unknown database name "' . $this->name . '"');
					}
				}
			} else if ($result == InstallHelper::ERROR_DB_UNKNOWN_HOST) {
				$this->error_message = 'unknown_host';
				$this->addError('host', 'Unknown host name "' . $this->host . '"');
			} else if ($result == InstallHelper::ERROR_DB_ACCESS_DENIED) {
				$this->error_message = 'access_denied';
				$this->addError('username', 'The username either does not exist or does not work with the given password');
				$this->addError('password', 'The password does not work with the given username');
			} else {
				// Some other error occurred as it did not return 0
				$this->addError('error_message', 'Error establishing a database connection, check your database details');
			}
		}
	}

	/**
	 * Will attempt to create the database if the create_database form flag has been set
	 * @param string &$exception
	 * @return boolean  - false indicates that it failed to create the database
	 */
	public function createDatabase(&$exception)
	{
		if ($this->create_database == 1) {
			$config = $this->getDbConfig();
			$config['dsn'] = 'mysql:host='.$this->host.';';
			$connection = new \neon\core\db\Connection($config);
			try {
				$connection->createCommand('CREATE DATABASE ' . $this->name)->execute();
			} catch (\yii\db\Exception $e) {
				// could not create the database
				$exception = $e->getMessage();
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the database details the form generates
	 * in the array config format appropriate for the \yii\db\Connection object
	 * @return array
	 */
	public function getDbConfig()
	{
		return [
			'dsn' => 'mysql:host='.$this->host.';dbname='.$this->name,
			'username' => $this->username,
			'password' => $this->password,
			'charset' => 'utf8',
			'tablePrefix' => $this->table_prefix
		];
	}
}