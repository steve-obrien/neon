<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2019 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @package neon
 */

namespace neon\core\helpers;

use yii\base\Component;

/**
 * Defines a basic iterator for use in paginated database requests.
 *
 * To use it you can simply create one which will have a start value of 0
 * and a length of 100. You can request up to 1000 items back at once. If you
 * need more than 1000, then you should be seriously considering why.
 *
 * To request the total during a call, set returnTotal() on the Iterator.
 * This will, for efficiency reasons, only set this if you are at the
 * beginning of the iteration, i.e. start=0. Counts are costly so should be
 * avoided. This way you can set the request at the beginning and use the
 * total value from then on.
 *
 * @package neon\core\helpers
 */
class Iterator extends Component
{
	/**
	 * The maximum number that can be returned in any one request
	 * @const integer
	 */
	const MAX_LENGTH = 1000;

	/**
	 * Return the total number found
	 * @var integer
	 */
	public $total = 0;

	/**
	 * The starting position
	 * @var integer
	 */
	private $_start = 0;

	/**
	 * The number to return
	 * @var integer
	 */
	private $_length = 100;

	/**
	 * Whether or not to get the total
	 * @var boolean
	 */
	private $_returnTotal = false;

	/**
	 * Set the starting position
	 * @param integer $start
	 */
	public function setStart($start)
	{
		$this->_start = max(0, $start);
	}

	/**
	 * Set the number to return
	 * @param integer $length
	 */
	public function setLength($length)
	{
		$this->_length = min($length, self::MAX_LENGTH);
	}

	/**
	 * Get the starting position
	 * @return integer
	 */
	public function getStart()
	{
		return $this->_start;
	}

	/**
	 * Get the number to return
	 * @return integer
	 */
	public function getLength()
	{
		return $this->_length;
	}

	/**
	 * Use to request a total is calculated. This will only
	 * work if the starting position is 0 for efficiency reasons
	 * or if you force it to return a total (this should be
	 * avoided if possible as counts are very expensive operations)
	 */
	public function returnTotal(bool $force=false)
	{
		$this->_returnTotal=($force? true : ($this->_start == 0));
	}

	/**
	 * Whether or not to calculate and return a total
	 * @return boolean
	 */
	public function shouldReturnTotal()
	{
		return $this->_returnTotal;
	}

	/**
	 * Convert to an array
	 * @return
	 *   start => the start value
	 *   length => the length value
	 *   total => truthy if should return total, otherwise the total
	 */
	public function toArray()
	{
		return [
			'start' => $this->start,
			'length' => $this->length,
			'total' => $this->_returnTotal ? true : $this->total
		];
	}
}