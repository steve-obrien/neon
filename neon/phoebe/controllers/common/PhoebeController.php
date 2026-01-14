<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 11/10/2016 12:20
 * @package neon/phoebe
 */

namespace neon\phoebe\controllers\common;

use neon\core\web\AdminController;

/**
 * Base controller across Phoebe builders
 */
class PhoebeController extends AdminController
{

	public $layout = '/admin-workbench';

	/**
	 * @var string the phoebe type e.g. daedalus
	 */
	public $phoebeType = null;

	/**
	 * Check if the user can do development
	 * @return boolean
	 */
	protected function canDevelop()
	{
		// development is only allowed when we are in dev mode
		return neon()->env == 'dev';
	}

	/**
	 * Get hold of the appropriate IPhoebeType
	 * @return \neon\phoebe\interfaces\IPhoebeType
	 */
	protected function phoebe()
	{
		if (!$this->_IPhoebeType) {
			$this->_IPhoebeType = neon('phoebe')->getService()->getPhoebeType($this->phoebeType);
		}
		return $this->_IPhoebeType;
	}
	private $_IPhoebeType = null;

	/**
	 * Get a Phoebe form
	 * @param string $type the phoebe class type
	 * @return \neon\core\form\Form
	 */
	protected function getPhoebeForm($type, &$isNewForm=null, $addExtras=true, $builder=false)
	{
		$form = null;
		$isNewForm = false;
		$fieldCount = 0;
		if ($type) {
			$class = $this->getClass($type);
			if ($builder) {
				$definition = $class->getClassBuilderDefinition();
			} else
				$definition = $class->getClassFormDefinition();
			$fieldCount = count($definition['fields']);
			$isNewForm = count($definition) == 0 ? true : false;
			$form = new \neon\core\form\Form($definition);
		} else {
			$form = new \neon\core\form\Form();
		}
		if ($addExtras) {
			$form->addFieldSubmit('Save', ['order'=>$fieldCount++, 'label'=>'Save Object']);
			//$form->attributes = ['class' => 'neon-form neon-form--fancy'];
			$form->enableAjaxValidation = true;
		}
		return $form;
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
	 * Get hold of the IPhoebeClass
	 * @param string $classType type
	 * @return \neon\phoebe\interfaces\IPhoebeClass
	 */
	protected function getClass($classType)
	{
		return $this->phoebe()->getClass($classType);
	}

	/**
	 * Returns the Phoebe class information for the specified class type as an array
	 * @param string $classType the class_type required
	 * @return array|null
	 */
	protected function getClassAsArray($classType)
	{
		$class = $this->phoebe()->getClass($classType);
		return $class ? $class->toArray() : null;
	}

	/**
	 * @param string $classType
	 * @return array
	 */
	protected function getBuilderDefinition($classType)
	{
		$class = null;
		if ($classType)
			$class = $this->getClass($classType);
		return $class ? $class->getClassBuilderDefinition() : [];
	}

	/**
	 * Extract the definition from a class
	 * @param string $classType
	 * @return array
	 */
	protected function getClassDefinition($classType)
	{
		$class = null;
		if ($classType)
			$class = $this->getClass($classType);
		return $class ? $class->definition : [];
	}

	/**
	 * Add an object of type classType
	 * @param string $classType
	 * @return \neon\phoebe\interfaces\IPhoebeObject
	 */
	protected function addObject($classType)
	{
		return $this->phoebe()->addObject($classType);
	}

	/**
	 * Get hold of an object
	 * @param string $id uuid
	 * @return \neon\phoebe\interfaces\IPhoebeObject
	 * @throws \yii\web\HttpException
	 */
	protected function getObject($id)
	{
		$object = $this->phoebe()->getObject($id);
		if (!$object)
			throw new \yii\web\HttpException(404);
		return $object;
	}

	/**
	 * Delete an object
	 * @param string $id uuid
	 * @return mixed
	 */
	protected function deleteObject($id)
	{
		$object = $this->phoebe()->getObject($id);
		if ($object)
			return $object->deleteObject();
		return null;
	}

	/**
	 * Undelete an object
	 * @param string $id uuid
	 * @return mixed
	 */
	protected function undeleteObject($id)
	{
		$object = $this->phoebe()->getObject($id);
		if ($object)
			return $object->undeleteObject();
		return null;
	}

	/**
	 * Destroy an object
	 * @param string $id uuid
	 * @return mixed
	 */
	protected function destroyObject($id)
	{
		$object = $this->phoebe()->getObject($id);
		if ($object)
			return $object->destroyObject();
		return null;
	}
}
