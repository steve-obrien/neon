<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2019 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @package neon
 */

namespace neon\daedalus\controllers\api;

use neon\core\web\ApiController;
use yii\web\HttpException;

class MapController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function verbs()
	{
		return [
			'classes' => ['get'],
			'objects' => ['get']
		];
	}
	/**
	 * Get the set of classes that have a map field defined
	 */
	public function actionClasses()
	{
		return neon('dds')->getDataMapTypes();
	}


	/**
	 * Get the map of uuid to object map field with optional filtering on
	 * an additional field e.g a linking field with a uuid.
	 *
	 * @param string $class  the class_type of the object you want the map on
	 * @param string $filter  a filter on the map field which will be used in
	 *   a like comparison
	 * @param integer $length  the number of items you want returned (defaults to 100)
	 * @param string $member  an additional member that you want to filter on
	 * @param string $value  the value for that member
	 * @param integer $start  where to start from in the list (defaults to beginning)
	 * @return array uuid => map field
	 * @throws
	 */
	public function actionObjects($class, $filter='', $length=100, $member='', $value='', $start=0)
	{
		$mapFilter = [];
		if (!$class)
			throw new HttpException(404, "No class name provided");
		if ($member && $value)
			$mapFilter[$member]=$value;
		if ($filter && is_string($filter))
			$mapFilter['_map_field_']=$filter;
		return neon()->getDds()->IDdsObjectManagement->getObjectMap($class, $mapFilter, [], $start, $length);
	}

	public function actionUserTypes()
	{
		return neon('user')->getDataMapTypes();
	}

	public function actionUsers($filter='')
	{
		return neon('user')->getDataMap('users', $filter);
	}

	public function actionPages($filter='')
	{
		return neon('cms')->getDataMap('pages', $filter);
	}

	public function actionPhoebe($type='', $filter='')
	{
		$filters = [];
		if (!$filter) {
			if ($type === 'phoebe_class') {
				$filters = ['or',
					['like', 'label', $filter],
					['like', 'class_type', $filter]
				];
			}
			else if ($type === 'phoebe_object') {
				$filters = ['like', 'uuid', $filter];
			}
		}
		return neon('phoebe')->getDataMap($type, $filters);
	}
}
