<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017-2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 10/10/2017 16:20
 * @package neon/phoebe
 */

namespace neon\phoebe\controllers\database\api;

use neon\core\web\ApiController;

class TableController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function verbs()
	{
		return [
			'map-field' => ['post'],
			'save' => ['post'],
			'delete-field' => ['post']
		];
	}

	/**
	 * Save a table definition from Phoebe
	 *
	 * @param string $class the DDS class_type
	 * @return array|null  the serialised form of the class
	 */
	public function actionSave($class)
	{
		$definition = neon()->request->getBodyParams();
		$phoebeClass = $this->phoebe()->saveClassDefinition($class, $definition, 'phoebe');
		if ($phoebeClass == null) {
			$phoebeClass = ['error' => "No class exists with a type of '$class'"];
		}
		return $phoebeClass->toArray();
	}


	// ------------------------------------- //
	// ----------- Private Methods ----------//
	/**
	 * @return \neon\phoebe\interfaces\IPhoebeType|null
	 */
	private function phoebe()
	{
		return neon()->phoebe->getService()->getPhoebeType('daedalus');
	}

}
