<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/09/2017
 * @package neon
 */

namespace neon\daedalus\controllers\api;

use neon\core\helpers\Html;
use neon\core\web\ApiController;
use yii\web\HttpException;

class ClassController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function verbs()
	{
		return [
			'*' => ['get', 'post']
		];
	}

	/**
	 * List all DDS class types
	 *
	 * @url /daedalus/api/classes
	 *
	 * @return array
	 */
	public function actionClasses()
	{
		$pages = new \neon\core\data\Pagination(['pageSize' => 100, 'validatePage' => false]);
		$results = neon()->getDds()->IDdsClassManagement->listClasses(null, $total, true, $pages->getOffset(), $pages->getLimit());
		$pages->totalCount = $total;
		$return = [
			'data' => $results,
			'pagination' => $pages->toArray(),
			'links' => $pages->getLinks(),
		];
		return $return;
	}

	/**
	 * List out the objects for a specified class
	 *
	 * @url /daedalus/api/classes/{{class}}
	 * @param string $class - the DDS class type
	 * @return array
	 * @throws HttpException
	 */
	public function actionList($class)
	{
		$start = microtime(true);
		$class = Html::sanitise($class);
		try {
			$pages = new \neon\core\data\Pagination(['validatePage' => false]);
			$filter = json_decode(neon()->request->getBodyParam('filter', '[]'));
			$results = neon()->getDds()->IDdsObjectManagement->runSingleObjectRequest($class, $filter, [], ['total' => true, 'length' => $pages->getPageSize(), 'start' => $pages->getOffset()]);
			$pages->totalCount = $results['total'];
		} catch (\Exception $e) {
			// error occured lets see if it's because the class does not exist
			if (neon()->getDds()->IDdsClassManagement->getClass($class) === null) {
				throw new HttpException(404, 'No class exists with name ' . $class);
			}
			throw new HttpException(500, $e->getMessage());
		}
		$definition = $this->actionDefinition($class);
		return [
			'data' => $results['rows'],
			'definition' => $definition['data'],
			'pagination' => $pages->toArray(),
			'links' => $pages->getLinks(),
			'time' => [
				'total' => neon()->getExecutionTime(),
				'action' => microtime(true) - $start
			]
		];
	}

	/**
	 * Get the definition of a class and its members
	 *
	 * @url /daedalus/api/classes/{{class}}/definition
	 * @param string $class
	 * @return array
	 * @throws HttpException if the class does not exist
	 */
	public function actionDefinition($class)
	{
		$class = Html::sanitise($class);
		$ddsClassManager = neon()->dds->getIDdsClassManagement();
		$classInfo = $ddsClassManager->getClass($class);
		if ($classInfo === null) {
			throw new HttpException(404, "No class exists with name '$class'");
		}
		$classInfo['members'] = $ddsClassManager->listMembers($class);
		return [
			'data' => $classInfo
		];
	}

	/**
	 * Get an object by its class and uuid
	 *
	 * @url /deadalus/api/classes/{{class}}/{{uuid}}
	 * @route [/daedalus/api/class/object, 'uuid' => '...', 'class' => '...']
	 * @param string $class
	 * @param string $uuid
	 * @return array
	 */
	public function actionObject($uuid, $class = null)
	{
		$uuid = Html::sanitise($uuid);
		return neon()->getDds()->getIDdsObjectManagement()->getObject($uuid);
	}

	/**
	 *
	 */
	public function actionObjectAdd($class)
	{
		$class = Html::sanitise($class);
		$data = neon()->request->getBodyParams();
		return neon()->getDds()->getIDdsObjectManagement()->addObject($class, $data);
	}
}
