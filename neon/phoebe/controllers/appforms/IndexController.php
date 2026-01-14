<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 11/10/2016 12:20
 * @package neon/phoebe
 */

namespace neon\phoebe\controllers\appforms;

use neon\phoebe\controllers\common\PhoebeController;
use neon\phoebe\controllers\common\PhoebeValidator;
use yii\web\NotFoundHttpException;

class IndexController extends PhoebeController
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();
		$rules[] = [
			'allow' => true,
			'actions' => ['validate-form'],
			'roles' => ['?', '@'] // any guest user or logged in user
		];
		return $rules;
	}

	/**
	 * The base url for this controller
	 * @var string
	 */
	protected $baseUrl = '/phoebe/appforms/index/';

	/**
	 * Set this up as a applicationForm phoebe adapter
	 * @param array $config
	 */
	public function __construct($id, $module, $config = [])
	{
		$config['phoebeType'] = 'applicationForm';
		parent::__construct($id, $module, $config);
	}

	/**
	 * Show a grid of all the content types currently in the system
	 * @return string
	 */
	public function actionIndex()
	{
		$classes = $this->listClasses();
		return $this->render('index.tpl', [
			'can_develop' => $this->canDevelop(),
			'classes' => $classes
		]);
	}

	/**
	 * Start the appForm Builder for this class
	 * @param string $type  the class type of the object required
	 */
	public function actionEdit($type=null)
	{
		if (!$this->canDevelop())
			throw new \HttpException(403, 'This operation is not permitted here');

		$builderDefinition = $this->getBuilderDefinition($type);
		$isNew = ($type === null || count($builderDefinition) == 0);

		// We get the class data here to allow the form to bootstrap with class and member
		// definitions directly. Alternatively, we could do this over an API.
		//
		// TODO This needs optimising as it is making too many calls to the database
		// Either over an API or a single SQL query
		//
		// This code recreates the old style definition of classes where the members
		// held their definitions. Ideally the builder could be updated to use the
		// new version where the definition is separated to the members
		//
		$dds = neon()->getDds()->IDdsClassManagement;
		$phoebe = neon('phoebe')->getIPhoebeType('daedalus');
		$tables = $phoebe->listClasses($paginator);
		$classes = [];
		foreach ($tables as $table) {
			$pClass = $phoebe->getClass($table['class_type']);
			$dClass = $dds->getClass($table['class_type'], true);
			$tableDef = $pClass->definition;
			$fields = $tableDef['fields'];
			unset($tableDef['fields']);
			$dClass['definition'] = json_encode($tableDef);
			foreach ($fields as $field=>$fieldDef) {
				$dClass['members'][$field]['definition'] = json_encode($fieldDef['definition']);
			}
			$classes[] = $dClass;
		}
		/*$classes = $dds->listClasses(null, $total);
		$classes = array_map(function($class) {
			return array_merge(neon()->getDds()->IDdsClassManagement->getClass($class['class_type']), [
				'members' => neon()->getDds()->IDdsClassManagement->listMembers($class['class_type'])
			]);
		},$classes);*/
		return $this->render('edit-fields', [
			'definition' => json_encode($builderDefinition),
			'classes' => json_encode($classes),
			'class_type' => $type,
			'isNew' => $isNew,
			'can_develop' => $this->canDevelop(),
		]);
	}


	/**
	 * List all objects within a class type specified by $type
	 * @param string $type the class type
	 * @return array|string
	 */
	public function actionListObjects($type)
	{
		$grid = new \neon\core\grid\PhoebeGrid([
			'phoebeType'=>$this->phoebeType,
			'classType'=>$type,
			'additionalColumns' => ['data','deleted']
		]);
		return $this->render('list', [
			'grid' => $grid,
			'type' => $type,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop(),
		]);
	}

	/**
	 * Soft delete an object (applicationForm row)
	 * @param string $type the applicationForm class type the object belongs to
	 * @param string $id the Object id
	 */
	public function actionDeleteObject($type, $id, $redirect=true)
	{
		$this->deleteObject($id);
		if ($redirect)
			$this->redirect([$this->baseUrl.'list-objects', 'type' => $type]);
		return '';
	}

	/**
	 * Validation of an application form object
	 * @param type $classType
	 * @return type
	 */
	public function actionValidateForm($classType, $name=null)
	{
		return PhoebeValidator::validateForm('applicationForm', $classType, $name);
	}

	/**
	 * Add an object to the applicationForm class defined by $type
	 * @param string $type the applicationForm class_type
	 * @return array|string
	 */
	public function actionAddObject($type)
	{
		$form = $this->getPhoebeForm($type);
		if (neon()->request->getIsAjax()) {
			return $form->ajaxValidation();
		}
		if ($form->processRequest()) {
			$object = $this->addObject($type);
			$object->editObject($form->getData());
			$this->redirect([$this->baseUrl.'list-objects', 'type'=>$type]);
		}
		return $this->render('edit.tpl', [
			'form' => $form,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop()
		]);
	}

	/**
	 * Edit a applicationForm object - very similar indeed to add
	 * @param string $type  the applicationForm class type
	 * @param $id  the object uuid
	 *
	 * @return array|string
	 */
	public function actionEditObject($type, $id)
	{
		$form = $this->getPhoebeForm($type);
		if (neon()->request->getIsAjax())
			return $form->ajaxValidation();
		$object = $this->getObject($id);
		if (!$object)
			throw new NotFoundHttpException('The requested form was not found');
		if ($form->processRequest()) {
			$object->editObject($form->getData());
			$this->redirect([$this->baseUrl.'list-objects', 'type'=>$type]);
		}
		$form->loadFromDb($object->data);
		return $this->render('edit.tpl', [
			'form' => $form,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop()
		]);
	}

}
