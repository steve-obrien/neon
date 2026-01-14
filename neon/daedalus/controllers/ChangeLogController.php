<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2020 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\controllers;

use neon\daedalus\controllers\common\DaedalusController;

/**
 * This controller manages the change log
 */
class ChangeLogController extends DaedalusController
{
	/**
	 * Change log
	 * @var neon\daedalus\interfaces\IDdsChangeLogManagement
	 */
	private $cl = null;
	public function __construct($id, $module, $config = [])
	{
		parent::__construct($id, $module, $config);
		$this->cl = neon('dds')->IDdsChangeLogManagement;
	}

	public function actionListAll()
	{
		$goBackTo = url(['index/index']);
		$grid = new \neon\daedalus\grid\ChangeLogGrid();
		$classesWithChangeLog = $this->cl->listClassesWithChangeLog();
		$availableChangeLogs = implode(', ', array_column($classesWithChangeLog, 'label'));
		return $this->render('listAll.tpl', [
			'hasChangeLogs' => (count($classesWithChangeLog) > 0),
			'availableChangeLogs' => $availableChangeLogs,
			'grid' => $grid,
			'goBackTo' => $goBackTo
		]);
	}

	public function actionList($type, $uuid=null)
	{
		// sort out where we're going back to
		$goBackTo = url(['index/list', 'type'=>$type]);
		if ($uuid)
			$goBackTo = url(['index/list', 'type'=>$type, 'uuid' => $uuid]);

		// check the class has a change log
		$class = ['class_type' => $type, 'label'=>ucwords(str_replace('_',' ',$type))];
		$hasChangeLog = $this->cl->hasChangeLog($type);
		if (!$hasChangeLog) {
			return $this->render('list.tpl', [
				'class'=>$class,
				'goBackTo' => $goBackTo,
				'hasChangeLog'=>$hasChangeLog,
			]);
		}

		$grid = new \neon\daedalus\grid\ChangeLogGrid([
			'classType'=>$type,
			'objectUuid'=>$uuid
		]);

		return $this->render('list.tpl', [
			'hasChangeLog'=>$hasChangeLog,
			'class'=>$class,
			'grid' => $grid,
			'goBackTo' => $goBackTo
		]);
	}

	/**
	 * Show a grid of all the content types currently in the system
	 * @return string
	 */
	public function actionViewObject($uuid, $logUuid)
	{
		$logEntry = $this->cl->getLogEntry($logUuid);
		if (!$logEntry)
			return 'Log not found';
		$type = $logEntry['class_type'];

		$class = $this->getClassAsArray($type);
		$goBackTo = url(['change-log/list', 'type'=>$type, 'uuid' => $uuid]);
		if ($uuid)
			$headerTitle = "$class[label] [Object '$uuid'] Change Log";
		else
			$headerTitle = "$class[label] Change Log";

		$title = "Status of $uuid on ".date("d-m-Y \\a\\t H:i:s", strtotime($logEntry['when']));
		$render = [
			'headerTitle'=>$headerTitle,
			'title' => $title,
			'goBackTo' => $goBackTo,
			'history' => [],
			'class' => $class,
			'object' => null,
			'associated_objects' => null
		];
		switch ($logEntry['change_key']) {
			case 'COMMENT':
				if (!$uuid)
					$render['title'] = "Comment added on ".date("d-m-Y \\a\\t H:i:s", strtotime($logEntry['when']));
				if (is_array($logEntry['associated_objects']) && count($logEntry['associated_objects'])>0)
					$render['associated_objects'] = implode(', ', $logEntry['associated_objects']);
			case 'DESTROY':
			case 'DELETE':
				$renderFile = 'viewLogEntry.tpl';
				$render['logEntry'] = $logEntry;
				$render['object'] = ['_uuid'=>$uuid];
			break;
			case 'ADD':
			case 'EDIT':
			case 'UNDELETE':
				$renderFile = 'view.tpl';
				$object = $this->cl->getObjectAtLogPoint($uuid, $logUuid);
				if ($object) {
					$form = neon()->phoebe->getForm('daedalus', $type, []);
					$form->label = $title;
					$form->loadFromDb($object);
					$form->printOnly = true;
					$render['form'] = $form;
					$render['object'] = $object;
				}
			break;
		}
		return $this->render($renderFile, $render);

	}

	/**
	 * Turn on or off the change log
	 */
	public function actionSet($type, $to)
	{
		$this->cl->setChangeLog($type, $to);
	}

}
