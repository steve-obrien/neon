<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2020 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\controllers;

use neon\daedalus\controllers\common\DaedalusController;

/**
 * This controller manages the listing, creation, editing, deletion etc of
 * database objects. It also covers the change log and data dictionary.
 *
 * For table creation and editing see the corresponding section in Phoebe
 */
class IndexController extends DaedalusController
{
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
	 * List all objects within a class type specified by $type
	 *
	 * @param string $type the class type
	 * @return array|string
	 */
	public function actionList($type)
	{
		$grid = new \neon\daedalus\grid\Table([
			'classType' => $type,
			'showUuidColumn' => true,
			'showCreatedColumn' => true,
			'useActiveScope' => true,
			'useDeletedScope' => true
		]);
		return $this->render('list.tpl', [
			'grid' => $grid,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop(),
		]);
	}

	/**
	 * Add an object to the daedalus class defined by $type
	 * @param string $type the daedalus class_type
	 * @return array|string
	 * @throws
	 */
	public function actionAddObject($type)
	{
		$form = $this->getPhoebeForm($type);
		if (neon()->request->getIsAjax()) {
			return $form->ajaxValidation();
		}
		if ($form->processRequest()) {
			$this->ddo()->addObject($type, $form->getData());
			return $this->redirect(['/daedalus/index/list', 'type' => $type]);
		}
		return $this->render('edit.tpl', [
			'form' => $form,
			'class' => $this->getClassAsArray($type),
			'object' => [],
			'can_develop' => $this->canDevelop()
		]);
	}

	public function actionViewObject($type, $id)
	{
		$form = $this->getPhoebeForm($type);
		$object = $this->ddo()->getObject($id);
		$form->loadFromDb($object);
		$form->printOnly = true;
		return $this->render('view.tpl', [
			'form' => $form,
			'object' =>  $object,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop()
		]);
	}

	/**
	 * Edit a daedalus object - very similar indeed to add
	 * @param string $type  the daedalus class type
	 * @param $id  the object uuid
	 * @return array|string
	 * @throws
	 */
	public function actionEditObject($type, $id)
	{
		$form = $this->getPhoebeForm($type);
		$object = $this->ddo()->getObject($id);
		$form->loadFromDb($object);
		if (neon()->request->getIsAjax())
			return $form->ajaxValidation();

		if ($form->processRequest()) {
			//try {
			 $result = $this->ddo()->editObject($id, $form->getData());
			 if ($result === true) {
				
				// successful save
				return $this->redirect(['/daedalus/index/list', 'type' => $type]);
			 }
			 // This is likely an SQL or Daedalus error
			 $form->addError('Could not save update record - this is likely an SQL error');
		}
		return $this->render('edit.tpl', [
			'form' => $form,
			'object' =>  $object,
			'class' => $this->getClassAsArray($type),
			'can_develop' => $this->canDevelop()
		]);
	}

	/**
	 * Soft delete an object (daedalus row)
	 * @param string $type the daedalus class type the object belongs to
	 * @param string $id the Object id
	 * @return String
	 */
	public function actionDeleteObject($type, $id, $redirect = true)
	{
		$this->ddo()->deleteObject($id);
		if ($redirect)
			$this->redirect(['/daedalus/index/list', 'type' => $type]);
	}

	/**
	 * Undelete a phoebe object
	 * @param string $type - phoebe class type
	 * @param string $id - uuid
	 * @return boolean | array - true on success or array of errors on failure
	 */
	public function actionUndeleteObject($type, $id, $redirect = true)
	{
		$this->ddo()->undeleteObject($id);
		if ($redirect)
			$this->redirect(['/daedalus/index/list', 'type' => $type]);
	}

	/**
	 * Destroy a phoebe object
	 * @param string $type - phoebe class type
	 * @param string $id - uuid
	 * @return boolean | array - true on success or array of errors on failure
	 */
	public function actionDestroyObject($type, $id, $redirect = true)
	{
		$this->ddo()->destroyObject($id);
		if ($redirect)
			$this->redirect(['/daedalus/index/list', 'type' => $type]);
	}


	/**
	 * Populate the daedalus table with fake rows
	 * @param string $type - the dds class type
	 */
	public function actionPopulate($type)
	{
		$form = neon()->phoebe->getForm('daedalus', $type, ['id'=>'fake', 'name'=>'fake']);
		$rows = neon()->request->post('rows', 10);
		while ($rows > 0) {
			$count = min($rows, 100);
			$data = [];
			for ($i = 0; $i < $count; $i++)
				$data[] = $form->fake();
			$this->ddo()->addObjects($type, $data);
			$rows -= $count;
		}
		return ['status' => 'success'];
	}

	/**
	 * Get the total count of class defined by $type
	 * @param string $type - the dds class type
	 * @return int - total count
	 */
	public function actionPopulateProgress($type)
	{
		$class = neon()->dds->getIDdsClassManagement()->getClass($type);
		return $class['count_total'];
	}

	/**
	 * @experimental - note this is still experimental
	 * Enables a request (typically from a Javascript UI) to get details of a form that can be used to add
	 * a new object as defined by phoebe type
	 * @see actionAddObjectPop
	 * @param string $type - the phoebe class type
	 * @return array
	 */
	public function actionPopFormDefinition($type)
	{
		$form = $this->getPhoebeForm($type);
		$array = $form->toArray();
		$array['action'] = \neon\core\helpers\Url::toRoute(['/daedalus/index/pop-form-add', 'type'=>$type]);
		$array['validationUrl'] = \neon\core\helpers\Url::toRoute(['/daedalus/index/pop-form-validation', 'type'=>$type]);
		return $array;
	}

	/**
	 * @param string $type - the daedalus type
	 * @return array
	 * @throws \yii\base\UnknownPropertyException
	 */
	public function actionPopFormValidation($type)
	{
		$form = $this->getPhoebeForm($type);
		return $form->ajaxValidation();
	}

	/**
	 * @experimental - note this is still experimental
	 * Form action to save a new object and return its map
	 * This enables objects linked via map fields to be added in context and appear in drop downs
	 * without leaving the page.  This action process the post request from a form.
	 * @see actionGetFormDefinition - provides the form definition and action necessary to enable an object to be added
	 * to a map
	 * @param string $type - the phoebe class type
	 * @return array
	 */
	public function actionPopFormAdd($type)
	{
		$form = $this->getPhoebeForm($type);
		if ($form->processRequest()) {
			$uuid = neon()->dds->getIDdsObjectManagement()->addObject($type, $form->getData());
			return neon()->dds->getIDdsObjectManagement()->getObjectMap($type, ['_uuid' => $uuid]);
		}
	}

	/**
	 * Get a data dictionary for the database derived from the
	 * @return string
	 */
	public function actionDataDictionary()
	{
		$dictionary = $this->dataDictionaryData();
		return $this->render('dataDictionary.tpl', [
			'project' => neon()->name,
			'definitions' => $dictionary['definitions'],
			'dataTypes' => $dictionary['dataTypes'],
			'neonDatabase' => $this->getNeonDatabaseOverview()
		]);
	}

	/**
	 * Enable fetching dictionary data about a specific dds class
	 * @param string $classType
	 * @return mixed
	 */
	public function actionDataDictionaryFor($classType)
	{
		$data = $this->dataDictionaryData();
		return $this->renderPartial('tableDictionary.tpl', ['class' => $data['definitions'][$classType]]);
	}

	/**
	 * Get the data for each class type in daedalus in a format that is easily presentable as a dictionary
	 * @return array
	 */
	public function dataDictionaryData()
	{
		$ddc = $this->ddc();
		$ddt = $this->ddt();
		$dataTypeList = $ddt->listDataTypes();
		$dataTypeByRef = array_column($dataTypeList, null, 'data_type_ref');

		// Don't see how this batching helps here - this would have to be paginated and not load additional rows.
		// Probably just set to 1000 for now.
		$start = 0;
		$length = 100;
		$classes = [];
		do {
			$nextSet = $ddc->listClasses(null, $total, false, $start, $length);
			$count = count($nextSet);
			if ($count)
				$classes = array_merge($classes, $nextSet);
			$start += $count;
		} while ($count>0);
		$classesByType = array_column($classes, null, 'class_type');

		// get the full definition of these

		$definitions = [];
		foreach ($classes as $c) {
			$class = $ddc->getClass($c['class_type'], true);
			foreach ($class['members'] as &$member) {
				$member['data_type'] = $dataTypeByRef[$member['data_type_ref']];
				$member['links_to_table'] = null;
				if ($member['link_class'] && isset($classesByType[$member['link_class']]))
					$member['links_to_table'] = $classesByType[$member['link_class']]['label'];
			}
			$definitions[$c['class_type']] = $class;
		}
		return [
			'definitions' => $definitions,
			'dataTypes' => $dataTypeList,
		];
	}

	/**
	 * Temporary placeholder (hmm lets see how long that lasts for Jan 2020 and counting)
	 * for the additional information about the database dictionary
	 * @return array
	 */
	protected function getNeonDatabaseOverview()
	{
		return [
			'cobe' => [
				'name'=>'Cobe',
				'description'=>'</=strong>C</strong>o<strong>b</strong>e is the neon <strong>C</strong>ontent <strong>B</strong>uilder and it controls which pages are rendered generically in the project theme code folder.',
				'tables' => [
					[
						'name'=>'cms_url',
						'description'=>'The set of URLs which can be mapped to templates within the project theme. Any URLs defined here are intercepted by Cobe and rendered using the cobe rendering engine'
					],
					[
						'name'=>'cms_page',
						'description'=>'Each entry in cms_url must map to an entry in cms_page. This table contains meta information about the page (e.g. title) and what template is used to render the page'
					]

				]
			],
			'daedalus' => [
				'name' => 'Daedalus',
				'description' => '<strong>D</strong>ae<strong>d</strong>alu<strong>s</strong> is the neon <strong>D</strong>ynamic <strong>D</strong>ata <strong>S</strong>system.',
				'tables' => [
					[
						'name'=>'dds_class',
						'description' => 'This holds the overall class definition of a database table in daedalus, containing meta information about the class such as it\'s description'
					],
					[
						'name'=>'dds_member',
						'description' => 'This holds the overall member definition of a database table field in daedalus, containing meta information about the field such as it\'s description and allowed values.'
					],
					[
						'name'=>'dds_object',
						'description' => 'This holds generic record information about every entry in the daedalus tables. This includes the creation and update dates of an object and whether or not it has been soft deleted.'
					],
					[
						'name'=>'dds_link',
						'description' => 'One to many linking with Daedalus is handled generically through the dds_link table. This maps a link from one item to many others. Many to many is then managed through a bidirectional link back.'
					]
				]
			],
			'firefly' => [
				'name' => 'Firefly',
				'description' => '<strong>F</strong>ire<strong>f</strong>ly is the neon <strong>F</strong>ile <strong>F</strong>inder. This manages all file uploads.',
				'tables' => [

				]
			],
			'phoebe' => [
				'name' => 'Phoebe',
				'description' => '<strong>P</strong>hoe<strong>b</strong>e is the neon <strong>F</strong>orm <strong>B</strong>uilder. Form here is as in structure and phoebe manages the creating and maintenance of the database tables and user &amp; application forms through graphical user interfaces',
				'tables' => [

				]
			],
			'user' => [
				'name' => 'User',
				'description' => 'The user module handles authentication and authorisation within neon. Authorisation is managed using role based authorisation control (RBAC).',
				'tables' => [

				]
			]
		];
	}
}
