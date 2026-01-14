<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\appforms;

use neon\phoebe\services\adapters\common\PhoebeTypeBase;
use neon\phoebe\interfaces\forms\IPhoebeFormType;

/**
 * ApplicationForm phoebe type
 */
class PhoebeType extends PhoebeTypeBase
	//implements IPhoebeFormType
{
	use PhoebeTrait;

	public function __construct($config=[])
	{
		$path = '\neon\phoebe\services\adapters\appforms';
		$config['phoebeType'] = 'applicationForm';
		$config['phoebeClass'] = $path.'\PhoebeClass';
		$config['phoebeObject'] = $path.'\PhoebeObject';

		parent::__construct($config);
	}

	public function getClass($classType, $version=null, $classOverrideCriteria=[])
	{
		$class = parent::getClass($classType, $version, $classOverrideCriteria);
		// update member definitions with the latest from the database and
		// override again with the changes that were defined before
		if ($class)
			$class->definition = $this->updateDefinitionFromDds($class->definition);
		return $class;
	}

	/**
	 * @inheritDoc
	 */
	public function createStubObject($classType)
	{
		$class = $this->getClassFromType($classType);
		// create a model for the class
		$model = $this->createPhoebeObjectModel($classType, false);
		$model->version = $class->version;
		return $this->createIPhoebeObject($model);
	}


	public function getObject($objectId)
	{
		$object = parent::getObject($objectId);
		// update the data from Daedalus if required
		if ($object)
			$object->updateFromDds();
		return $object;
	}

	/**
	 * Make the default for appForms to be volatile
	 * @inheritdoc
	 */
	protected function createPhoebeClassModel($classType, $module)
	{
		$model = parent::createPhoebeClassModel($classType, $module);
		$model->volatile = 1;
		return $model;
	}
}