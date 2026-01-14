<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 11/10/2016 12:20
 * @package neon/phoebe
 */

namespace neon\phoebe\controllers\database;

use neon\core\helpers\Arr;
use neon\core\helpers\Hash;
use neon\core\helpers\Str;
use neon\phoebe\controllers\common\PhoebeController;
use neon\phoebe\controllers\common\PhoebeValidator;
use yii\web\HttpException;

/**
 * The Phoebe Database controller manages the manipulation of Daedalus
 * definitions using the Phoebe UI
 */
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
			'roles' => ['?', '@'] // anyone for validate form
		];
		return $rules;
	}

	/**
	 * Set this up as a daedalus phoebe adapter
	 * @param array $config
	 */
	public function __construct($id, $module, $config = [])
	{
		$config['phoebeType'] = 'daedalus';
		parent::__construct($id, $module, $config);
	}

	/**
	 * Edit a table definition
	 * @param string $type - the class type
	 * @return String
	 * @throws HttpException
	 */
	public function actionEdit($type = null)
	{
		if (!$this->canDevelop())
			throw new HttpException(403, 'This operation is not permitted here');

		$form = $this->getPhoebeForm($type, $isNewForm, false, true);
		return $this->render('edit-fields', [
			'definition' => $form->toJson(),
			'data' => [
				'urls' => [
					'list' => url('/daedalus/index/list'),
					'save' => url('/phoebe/database/api/table/save'),
				]
			],
			'fields' => $this->getAvailableFormFields(),
			'dds' => [
				'data_map_info' => neon()->app('core')->getDataMapProviders(),
				'map_types' => neon()->dds->getDataMapTypes(),
				'class_type' => $type,
				'exists' => !$isNewForm,
				'can_develop' => $this->canDevelop(),
			]
		]);
	}

	/**
	 * Returns an array of the available phoebe components
	 * This also importantly registers the necessary javascript and dependencies certain components require
	 *
	 * @return array
	 */
	public function getAvailableFormFields()
	{
		$exclude = ['neon\core\form\fields\filters\File'];
		$appClasses = neon()->getAppClasses('', \neon\core\interfaces\IComponent::class);
		$out = [];
		foreach($appClasses as $app => $classes) {
			foreach($classes as $class) {
				/** @var \neon\core\form\fields\Field $f */
				$f = new $class(uuid(), []);
				// register all form field js scripts
				$f->registerScripts(neon()->view);
				$class = substr($class, 1);
				if (in_array($class, $exclude)) continue;
				$details = $f->getComponentDetails();
				if ($details === false) continue;
				$defaultField = Arr::except($f->toArray(), ['dataKey']);
				$cmp = Arr::merge($defaultField, [
					'class' => $class,
					'dataType' => $f->ddsDataType,
					'classLabel' => Str::titleize(Str::basename($class), true),
					// the data name used as default for the form field name
					'name' => strtolower(Str::basename($class)),
					// the default value to use for a the fields label
					'label' => Str::titleize(Str::basename($class), true),
					'app' => $app,
				], $details);
				if (method_exists($f, 'getComponentIcon')) {
					$cmp['icon'] = $f->getComponentIcon();
				}
				$out[] = $cmp;
			}
		}
		return $out;
	}

	/**
	 * Validation of a daedalus form object
	 * @param string $classType
	 * @return type
	 */
	public function actionValidateForm($classType, $name = '')
	{
		return PhoebeValidator::validateForm('daedalus', $classType, $name);
	}

	/**
	 * Create a table from its raw definition
	 */
	public function actionCreateTableRaw()
	{
		$formDefinition = [
			'name' => 'create-table',
			'fields' => [
				'name' => [
					'class' => 'text',
					'label' => 'Table Name',
					'hint' => 'Set the database table name',
					'required' => true
				],
				'label' => [
					'class' => 'text',
					'label' => 'Table User Label',
					'hint' => 'Set the table user friendly label',
					'required' => true
				],
				'fields' => [
					'class' => 'json',
					'label' => 'Field Definitions',
					'hint' => 'Enter the table fields. These should be in JSON format e.g. [ '.
						'{ "class": "text", "name": "field_name", "label":"Field Name", "hint":"Something about this" }, {etc} ]',
					'required' => true
				],
				'submit' => [
					'class' => 'submit',
					'label' => 'Submit Defintion'
				]
			]
		];
		$form = new \neon\core\form\Form($formDefinition);
		if (neon()->request->isAjax)
			return $form->ajaxValidation();
		if ($form->processRequest()) {
			$data = $form->getData();
			$definition = $this->canonicaliseDefinition($data);
			if ($definition) {
				neon('phoebe')
					->getService()
					->getPhoebeType('daedalus')
					->saveClassDefinition($data['name'], $definition, 'phoebe');
				return $this->redirect(['/daedalus/index']);
			}
		}
		return $this->render('create-table-raw.tpl', [
			'class' => [ 'label' => 'Create Table'],
			'form'=>$form
		]);
	}

	/**
	 * Canonicalise a raw definition by adding any missing parameters that
	 * might be needed (e.g. classLabel)
	 * @param array $data
	 * @return array
	 */
	protected function canonicaliseDefinition($data)
	{
		$definition = [
			'name' => $data['name'],
			'label' => $data['label'],
			'fields' => []
		];
		$fields = json_decode($data['fields'],true);
		if (is_array($fields)) {
			foreach ($fields as $field) {
				$field['classLabel'] = isset($field['classLabel']) ? $field['classLabel'] : ucwords($field['class']);
				$field['label'] = isset($field['label']) ? $field['label'] : ucwords(str_replace(['_','-'],' ',$field['name']));
				$definition['fields'][] = $field;
			}
			return $definition;
		}
		return null;
	}



}
