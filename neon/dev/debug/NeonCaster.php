<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 24/11/2016 18:18
 * @package neon
 */

namespace neon\dev\debug;

use Symfony\Component\VarDumper\Caster\Caster;
use \neon\core\helpers\Collection;
use \yii\base\Model;

/**
 * Class NeonCaster - helps display particular objects in a friendly format
 * @package neon\dev\console
 */
class NeonCaster
{
	/**
	 * Get an array of Neon tailored casters.
	 *
	 * @return array
	 */
	public static function getCasters()
	{
		return [
			'neon\core\helpers\Collection' => '\neon\dev\debug\NeonCaster::castCollection',
			'yii\base\Model'               => '\neon\dev\debug\NeonCaster::castModel',
		];
	}

	/**
	 * Get an array representing the properties of a collection.
	 *
	 * @param  \neon\core\helpers\Collection $collection
	 * @return array
	 */
	public static function castCollection(Collection $collection)
	{
		return [
			Caster::PREFIX_VIRTUAL . 'all' => $collection->all(),
		];
	}

	/**
	 * Get an array representing the properties of a model.
	 *
	 * @param  \yii\base\Model $model
	 * @return array
	 */
	public static function castModel(Model $model)
	{
		$attributes = $model->toArray();

		$results = [];

		foreach ($attributes as $key => $value) {
			$results[(Caster::PREFIX_VIRTUAL) . $key] = $value;
		}


		return $results;
	}
}