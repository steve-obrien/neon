<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 27/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\services\accountManager;

use yii\base\Component;

/**
 * This class is designed to encapsulate specific functionality involved with managing a multi  tenant system
 * Neon encapsulates multi tenant concepts by creating a one to one mapping between a domain property and the users of that property (the tenants)
 * The majority of the system does then not need to understand multi tenant concepts they just request the database from the tenant manager
 *
 * Class TenantManager
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\services
 */
class AccountManager extends Component implements IAccountManager
{
	public $domainDbPrefix = 'neon_';
	public $bannedSubDomains = ['www'];
	public $dbHost = 'localhost';
	public $db = [
		'class' => 'neon\core\db\Connection',
		'enableSchemaCache' => true,
		'schemaCacheDuration' => 3600,
		'username' => 'root',
		'password' => '',
		'charset' => 'utf8',
		'tablePrefix' => '',
	];

	public $hostname = 'local.newicon.org';

	/**
	 * @return string
	 */
	public function getDbName()
	{
		$subDomain = neon()->request->getSubDomain();
		return $this->domainDbPrefix.$subDomain;
	}

	/**
	 * @return null|object
	 */
	public function getTenantDb()
	{
		// if the application does not support multi tenancy then simply return the main database
		if ( ! neon()->multiTenant || ! $this->isOnMasterDomain()) {
			return neon()->get('db');
		}

		// does account exists with domain?

		// get the database name specific to this account
		if ($this->_db === null) {
			$config = array_merge($this->db, ['dsn' => "mysql:host={$this->dbHost};dbname={$this->getDbName()}"]);
			$this->_db = \Neon::createObject($config);
		}
		return $this->_db;
	}

	/**
	 * @var \neon\core\db\Connection
	 */
	private $_db;

	/**
	 * Whether we are on the master domain - the main domain for the neon application
	 * @return bool
	 */
	public function isOnMasterDomain()
	{
		return (neon()->request->getSubDomain() == '');
	}
}