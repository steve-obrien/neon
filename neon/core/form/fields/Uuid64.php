<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Hash;

/**
 * Class Text
 * @package neon\core\form
 */
class Uuid64 extends Text
{
	public $ddsDataType = 'uuid64';

	/**
	 * Whether to auto generate a uuid when empty
	 * @var bool
	 */
	public $autoGenerate = false;

	/**
	 * Will auto generate a uuid if $this->autoGenerate is true and no value is set
	 * @inheritdoc
	 */
	public function getUnsafeValue()
	{
		$value = parent::getUnsafeValue();
		if ($this->autoGenerate && empty($value)) {
			$value = Hash::uuid64();
		}
		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return '<code>' . $this->getValue() . '</code>';
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		$field = $this->toArray();
		$field['autoGenerate'] = false;
		return Arr::except($field, ['name', 'label', 'hint']);
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return Hash::uuid64();
	}

	public function getComponentDetails()
	{
		return [
			'label' => 'Uuid', 'icon' => 'fa fa-flag-checkered', 'group' => 'Data', 'order'=>810
		];
	}
}