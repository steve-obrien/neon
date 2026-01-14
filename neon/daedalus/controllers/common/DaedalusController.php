<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2020 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\controllers\common;

use neon\core\web\AdminController;
use neon\daedalus\interfaces\IDdsClassManagement;
use neon\daedalus\interfaces\IDdsObjectManagement;

/**
 * This contains common methods for the Daedalus controllers
 */
class DaedalusController extends AdminController
{

	// ----------------------------------------- //
	// ---------- Phoebe Interactions ---------- //

	/**
	 * Get hold of the appropriate IPhoebeType
	 * @return \neon\phoebe\interfaces\IPhoebeType
	 */
	protected function phoebe()
	{
		if (!$this->_IPhoebeType) {
			$this->_IPhoebeType = neon('phoebe')->getService()->getPhoebeType('daedalus');
		}
		return $this->_IPhoebeType;
	}
	private $_IPhoebeType = null;

	/**
	 * Get a Phoebe daedalus form
	 * @param string $type the phoebe class type
	 * @param array $options additional options for the form
	 * @return \neon\core\form\Form
	 */
	protected function getPhoebeForm($type, $options=[])
	{
		return neon()->phoebe->getForm('daedalus', $type, $options);
	}

	/**
	 * Returns the Phoebe class information for the specified class type as an array
	 * @param string $classType the class_type required
	 * @return array|null
	 */
	protected function getClassAsArray($classType)
	{
		return $this->ddc()->getClass($classType);
	}


	/**
	 * List the classes for the phoebe type
	 * @return array
	 */
	protected function listClasses()
	{
		return $this->phoebe()->listClasses();
	}

	/**
	 * Check if the user can do development
	 * @return boolean
	 */
	protected function canDevelop()
	{
		// development is only allowed when we are in dev mode
		return neon()->env == 'dev';
	}

	// ---------- Short cuts to Daedalus ---------- //

	private $_ddo = null;
	private $_ddc = null;
	private $_ddt = null;

	/**
	 * @return IDdsObjectManagement
	 */
	protected function ddo()
	{
		if ($this->_ddo == null)
			$this->_ddo = neon('dds')->IDdsObjectManagement;
		return $this->_ddo;
	}

	/**
	 * @return IDdsClassManagement
	 */
	protected function ddc()
	{
		if ($this->_ddc == null)
			$this->_ddc = neon('dds')->IDdsClassManagement;
		return $this->_ddc;
	}

	/**
	 * @return IDdsObjectManagement
	 */
	protected function ddt()
	{
		if ($this->_ddt == null)
			$this->_ddt = neon('dds')->IDdsDataTypeManagement;
		return $this->_ddt;
	}

}
