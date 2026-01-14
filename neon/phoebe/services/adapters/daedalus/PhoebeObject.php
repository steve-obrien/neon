<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\daedalus;

use neon\daedalus\interfaces\IDdsObjectManagement;
use neon\phoebe\services\adapters\common\PhoebeObjectBase;
use neon\phoebe\interfaces\forms\IPhoebeFormObject;


/**
 * Provides the Daedalus object adapter for phoebe
 */
class PhoebeObject extends PhoebeObjectBase
implements IPhoebeFormObject
{
	/**
	 * The change log uuid for this object
	 * @var string
	 */
	public $changeLogUuid = null;


	/** ----------------------------------- **/
	/** -------- IPhoebeFormObject -------- **/

	protected $dataSources = [];

	/**
	 * @inheritdoc
	 */
	public function setDataSources($sources)
	{
		foreach ($sources as $key=>$value) {
			if (empty($value))
				unset($sources[$key]);
		}
		$this->dataSources = $sources;
	}

	/**
	 * @inheritdoc
	 */
	public function updateFromDds()
	{
		// do nothing
		return;
	}

	/**
	 * @inheritdoc
	 */
	public function initialiseFromDds($dataObjects)
	{
		// do nothing
		return;
	}


	/** ----------------------------------- **/
	/** ---------- IPhoebeObject ---------- **/

	/**
	 * @inheritdoc
	 */
	public function editObject($changes)
	{
		foreach ($this->dataSources as $key=>$value) {
			$changes[$key] = $value;
		}
		return $this->ddo()->editObject($this->uuid, $changes, $this->changeLogUuid);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteObject()
	{
		return $this->ddo()->deleteObject($this->uuid, $this->changeLogUuid);
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteObject()
	{
		return $this->ddo()->undeleteObject($this->uuid, $this->changeLogUuid);
	}

	/**
	 * @inheritdoc
	 */
	public function destroyObject()
	{
		return $this->ddo()->destroyObject($this->uuid, $this->changeLogUuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getChangeLogUuid()
	{
		return $this->changeLogUuid;
	}

	protected $_ddo = null;

	/**
	 * @return IDdsObjectManagement
	 */
	protected function ddo()
	{
		if ($this->_ddo == null)
			$this->_ddo = neon('dds')->IDdsObjectManagement;
		return $this->_ddo;
	}


}
