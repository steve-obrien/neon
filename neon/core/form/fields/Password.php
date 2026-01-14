<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Text;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class Password extends Text
{
	/**
	 * @inheritdoc
	 */
	protected $allowableTags = "";

	/**
	 * Whether to check if the password exists in compromised password databases
	 * @var bool
	 */
	public $checkPasswordDatabases = true;

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['checkPasswordDatabases']);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		// cannot filter by password!
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		// cannot search as a filter
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return '************';
	}

	/**
	 * Returns an encrypted version of the password
	 * @return string
	 * @throws \Exception
	 */
	public function getValueEncrypted()
	{
		return neon()->user->generatePasswordHash(parent::getValue());
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		// ignore the set value from the database as this would be encrypted
		// and we shouldn't be able to show passwords under any circumstances
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->password;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'password' => 'Password',
			'icon' => 'fa fa-lock',
			'group' => 'Text',
			'order' => 140,
		];
	}
}