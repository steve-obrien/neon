<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017-2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 10/10/2017 16:20
 * @package neon/phoebe
 */

namespace neon\phoebe\controllers\appforms\api;

use neon\core\web\ApiController;
use yii\web\HttpException;
use neon\core\helpers\Hash;

/**
 * This handles form definition changes via the Phoebe Builder
 */
class FormController extends ApiController
{

	/**
	 * @inheritdoc
	 */
	public function verbs()
	{
		return [
			'save' => ['post'],
		];
	}

	/**
	 * Save an applicationForm definition
	 * @param string $class the application form class_type
	 * @return array|null json
	 */
	public function actionSave($class)
	{
		$phoebe = $this->phoebe();

		// temporary hack to sort out uuids being passed in rather than
		// a human readable definition.
		if ($this->is_uuid($class))
			$class=str_replace(['-','_'], ['a','Z'],Hash::uuid2uuid64($class));
		else
			$class = $phoebe->canonicaliseRef($class);

		$definition = neon()->request->getBodyParams();
		if (!isset($definition['name'])) {
			throw new HttpException(400, 'You must supply a form name');
		}

		// start a migration for the changes
		$this->startMigration('appform_'.$class.'_edit');

		// save the class information
		$phoebeClass = $phoebe->getClass($class);
		if (!$phoebeClass)
			$phoebeClass = $phoebe->addClass($class, 'phoebe');
		$phoebeClass->editClass([
			'definition' => $definition,
			'label' => $definition['name']
		]);

		// close the migration
		$this->endMigration();

		$phoebeClass =  $phoebe->getClass($class);
		if ($phoebeClass == null) {
			$phoebeClass = ['error' => "No class exists with a type of '$class'"];
		}

		return $phoebeClass->toArray();
	}

	// ------------------------------------- //
	// ----------- Private Methods ----------//

	/**
	 * Temporary hack to see if the string is a uuid - not an exhaustive test
	 */
	private function is_uuid($str)
	{
		return (strlen($str)==36 && substr_count($str, '-')==4);
	}

	/**
	 * Create a migration with the provided label
	 * @param type $label
	 */
	private function startMigration($label)
	{
		neon('dds')->getIDdsAppMigrator('phoebe')->openMigrationFile($label);
	}

	/**
	 * close the currently open migration
	 */
	private function endMigration()
	{
		neon('dds')->getIDdsAppMigrator('phoebe')->closeMigrationFile();
	}

	private function phoebe()
	{
		return neon('phoebe')->getService()->getPhoebeType('applicationForm');
	}

}
