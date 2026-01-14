<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe;

use neon\core\helpers\Arr;
use neon\core\interfaces\IDataMapProvider;
use neon\phoebe\services\PhoebeService;
use neon\phoebe\services\adapters\common\models\PhoebeClass as PhoebeClassModel;
use neon\phoebe\services\adapters\common\models\PhoebeObject as PhoebeObjectModel;
use neon\core\helpers\Html;

/**
 * The neon phoebe app class
 */
class App extends \neon\core\BaseApp
implements IDataMapProvider
{
	public $requires = 'dds';

	/**
	 * The phoebe service reference. One per app creation.
	 * @var neon\phoebe\services\PhoebeService
	 */
	public $phoebeService = null;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Phoebe';
	}

	/**
	 * Override in config with an array to change menu options
	 * for e.g:
	 * ```php
	 * 'menu' => [
	 *     [
	 *         'label' => 'App Forms',
	 *         'order' => 1020,
	 *         'url' => ['/phoebe/appforms/index/index'],
	 *         'visible' => function() { // rules },
	 *     ],
	 * ]
	 * ```
	 * @see getMenu
	 * @see setMenu
	 * @var null
	 */
	protected $_menu = null;

	/**
	 * Enable default phoebe menu to be overridden
	 * @param $menu
	 */
	public function setMenu($menu)
	{
		$this->_menu = $menu;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{
		return [
			[
				'label' => 'Forms',
				'order' => 1400,
				'url' => ['/phoebe/appforms/index/index'],
				'visible' => neon()->user->hasRole('neon-administrator'),
				'active' => is_route('/phoebe/appforms/*'),
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function getDataMap($key, $query='', $filters=[], $fields=[], $start=0, $length=100)
	{
		// convert generic form builder filters to phoebe class ones
		$filters = Arr::replaceKeys($filters, ['uuid'=> 'class_type']);
		switch ($key) {
			// changed from Class to phoebe_class but need both for backwards compatibility
			case 'Class': case 'phoebe_class':
				return PhoebeClassModel::listClassTypes($query, $filters);
			break;
			// changed from Object to phoebe_object but need both for backwards compatibility
			case 'Object': case 'phoebe_object':
				return PhoebeObjectModel::listObjects($query, $filters);
			break;
			default:
				return [];
			break;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getDataMapTypes()
	{
		return [
			'phoebe_class' => 'Phoebe Class',
			'phoebe_object' => 'Phoebe Object'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getMapResults($requestKey)
	{
		// TODO: Implement getMapResults() method.
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($key, $ids, $fields = [])
	{
		// TODO: Implement makeMapLookupRequest() method.
	}

	/**
	 * Get the phoebe service
	 * @return \neon\phoebe\interfaces\IPhoebeService
	 */
	public function getService()
	{
		if (!$this->phoebeService) {
			$this->phoebeService = new PhoebeService();
		}
		return $this->phoebeService;
	}

	/**
	 * return neon\phoebe\interfaces\IPhoebeType
	 */
	public function getIPhoebeType($phoebeType)
	{
		return $this->getService()->getPhoebeType($phoebeType);
	}

	/**
	 * TODO - 20180507 - Tidy some of the form functions away into
	 * somewhere else - e.g. the PhoebeObject/Class/Type. That way you can make the
	 * methods specific to the type of form that is created
	 */

	/**
	 * Get hold of a form definition given the phoebe and class type
	 * Applies a cache per php request (cacheArray - a php in memory component)
	 * @param string $phoebeType  e.g. 'daedalus'
	 * @param string $classType  e.g. 'mytable'
	 * @param array $fields  if available for the phoebeType, the set of fields
	 *   that you want to display and in what order
	 * @return array
	 */
	public function getFormDefinition($phoebeType, $classType, array $fields=[])
	{
		$cacheKey = md5(serialize(func_get_args()));
		return neon()->cacheArray->getOrSet($cacheKey, function() use ($classType, $phoebeType, $fields) {
			$class = $this->getService()->getPhoebeType($phoebeType)->getClass($classType);
			return $class ? $class->getClassFormDefinition($fields) : [];
		});
	}

	/**
	 * Strings used for setting form meta data
	 * @var string
	 */
	private $formMetaPrefix = '_mt';
	private $formMetaDataSourceString = 'ds_';
	private $formMetaReturnUrlString = 'ru_';
	private $formMetaUuidString = 'ud_';

	/**
	 * Get a Phoebe form
	 *
	 * TODO - 13/07/2018 NJ - Move these into a more appropriate place
	 *
	 * Depending on settings, this can return an empty form or, so long as this
	 * is not a posted request, a form filled with the object from the database
	 * Note: if you are using a different name for the form, you must pass this
	 * in in the options
	 *
	 * @param string $phoebeType  the phoebe type required for e.g. 'daedalus'
	 * @param string $classType the phoebe class type
	 * @param array $options  a set of options to set on the form
	 *   'cssClass' => string - set additional css classes for the form
	 *   'id' => string - an id for the form
	 *   'label' => the form label. Omit for the default, or set to '' to remove,
	 *     or to the new label string.
	 *   'fields' => if available for the phoebe type, the set of fields that you want to
	 *     display and in what order as ['field1',...]
	 *   'fieldDefaults' => any defaults for the form as ['field'=>default]
	 *   'mapFilters' => if available for the phoebe type, filters for any fields that get
	 *     their data through a dataMapProvider. These should be as
	 *     ['formField1'=>['tableField1'=>'value1', ...], 'formField2'=>...]
	 *   'fieldProperties' => if available for the phoebe type, set provided properties for
	 *     the form fields as ['field'=>['property'=>'value']]
	 *   'save' => ['label' - the default submit button label]
	 *   'buttons' => an array of buttons as [['name', 'label', 'class'],...]
	 *     These will override the default submit button.
	 *   'enableAjaxValidation' => boolean  whether or not to use AJAX validation
	 *   'enableAjaxSubmission' => boolean  whether or not to use AJAX submission
	 *   'ajaxValidationUrl' => the URL to use for AJAX validation
	 *   'readOnly' => [true|false]  if set and true the form is read only
	 *   'printOnly' => [true|false]  if set and true the form is printable
	 *
	 * Additional meta information options can be passed.
	 *   'name' => pass in the form name if not defined by the classType
	 *   'action' => where the form should go to for processing
	 *   'uuid' => if the instance object already exists, set the UUID here. If this isn't
	 *      a posted form, the form will be loaded from the database
	 *   'dataSources' => if required, a set of data sources that the object needs
	 *      for saving itself. These are [key=>value] pairs
	 *   'returnUrl' => the return URL after processing the form
	 * @param array $action  if set then set this action on the form
	 * @return \neon\core\form\Form
	 * @throws
	 */
	public function getForm($phoebeType, $classType, $options)
	{
		$form = null;
		$fieldCount = 0;
		$readOnly = empty($options['readOnly']) ? false : $options['readOnly'];
		$printOnly = empty($options['printOnly']) ? false : $options['printOnly'];
		$fields = empty($options['fields']) ? [] : $options['fields'];

		// see if we this is for an existing object and if so determine if it
		// had any overrides we need to take care of
		$phoebe = $this->getIPhoebeType($phoebeType);
		$object = null;
		$formDefinition = [];
		if (!empty($options['uuid'])) {
			$object = $phoebe->getObject($options['uuid']);
			if (!$object)
				throw new \RuntimeException('The requested form object was not found. Uuid='.$options['uuid']);
			$class = $object->getIPhoebeClass();
			$formDefinition = $class->getClassFormDefinition($fields);
		} else {
			// otherwise get a clean class
			if ($classType) {
				$formDefinition = $this->getFormDefinition($phoebeType, $classType, $fields);
				if (!count($formDefinition))
					throw new \RuntimeException('The requested form type was not found. Type='.$classType);
			}
		}
		$fieldCount = count($formDefinition['fields']);
		$form = new \neon\core\form\Form($formDefinition);


		// temporary code to cover problems with differences between appForms and ddsForms
		if ($phoebeType !== 'applicationForm' && !empty($options['initialiseFromDds']))
			throw new \RuntimeException('Only applicationForms work with initialiseFromDds');

		// set the form class
		if (!empty($options['cssClass']))
			$form->setAttributes(['class'=>$options['cssClass']]);

		// set the form id
		if (!empty($options['id']))
			$form->setId($options['id']);
		$formId = $form->getId();
		if ($printOnly) {
			$form->printOnly = true;
			$form->setId('PrintOnly'.$formId);
		} else if ($readOnly) {
			$form->readOnly = true;
			$form->setId('ReadOnly'.$formId);
		}

		// set the forms label - allow clearing of it via empty string
		if (isset($options['label']))
			$form->setLabel($options['label']);


		// check to see if we are not posting from the client, populate it from the database
		// !set the form name before checking data set!
		if (!empty($options['name']))
			$form->setName($options['name']);
		if (!empty($options['initialiseFromDds']) && (!$object || !$object->data)) {
			if (!$object)
				$object = $phoebe->createStubObject($classType);
			$object->initialiseFromDds($options['initialiseFromDds']);
		}
		if ($object && !$form->hasRequestData()) {
			$form->loadFromDb($object->data);
		}

		if (!($printOnly || $readOnly)) {
			if (!empty($options['uuid']))
				$form->addFieldHidden("{$this->formMetaPrefix}{$this->formMetaUuidString}", ['value'=>$options['uuid']]);

			// set any additional data sources
			if (!empty($options['dataSources'])) {
				foreach ($options['dataSources'] as $key=>$value) {
					$form->addFieldHidden("{$this->formMetaPrefix}{$this->formMetaDataSourceString}$key", ['value'=>$value]);
					// for dds forms, set the values on the form too for initial display
					if ($phoebeType == 'daedalus') {
						if ($form->hasField($key))
							$form->getField($key)->setValue($value);
					}
				}
			}

			// set any map filters
			// NJ 20180617 - I think this should change the definition above and not be here
			// so that way it can work on appForms as well as ddsForms
			if (!empty($options['mapFilters'])) {
				foreach ($options['mapFilters'] as $key=>$filters) {
					$f = $form->getField($key);
					if ($f && isset($f->dataMapFilters))
						$f->dataMapFilters = $filters;
				}
			}

			// set any map fields
			// NJ 20180617 - I think this should change the definition above and not be here
			// so that way it can work on appForms as well as ddsForms
			if (!empty($options['mapFields'])) {
				foreach ($options['mapFields'] as $key=>$mapFields) {
					$f = $form->getField($key);
					if ($f && isset($f->dataMapFields))
						$f->dataMapFields = $mapFields;
				}
			}

			if (!empty($options['fieldDefaults'])) {
				foreach ($options['fieldDefaults'] as $field=>$default) {
					$f = $form->getField($field);
					if ($f) {
						$f->value = $default;
					}
				}
			}

			if (!empty($options['fieldProperties'])) {
				foreach ($options['fieldProperties'] as $field=>$properties) {
					$f = $form->getField($field);
					if ($f) {
						foreach ($properties as $property => $value) {
							$f->$property = $value;
						}
					}
				}
			}

			// set the forms action
			if (!empty($options['action']))
				$form->setAction($options['action']);

			// set whether or not ajax validation is required
			if (isset($options['enableAjaxValidation']))
				$form->enableAjaxValidation = (boolean) $options['enableAjaxValidation'];

			// set whether or not ajax submission is required
			if (isset($options['enableAjaxSubmission']))
				$form->enableAjaxSubmission = (boolean) $options['enableAjaxSubmission'];

			// set where the form will check itself via ajaxValidation
			if (!empty($options['ajaxValidationUrl']))
				$form->validationUrl = $options['ajaxValidationUrl'];

			// set where the browser will go to after completion of the form
			if (!empty($options['returnUrl'])) {
				$form->addFieldHidden("{$this->formMetaPrefix}{$this->formMetaReturnUrlString}", ['value'=>$options['returnUrl']]);
			}

			if (!empty($options['buttons'])) {
				foreach ($options['buttons'] as $button) {
					$btnOptions = [
						'order' => $fieldCount++,
						'label' => !empty($button['label']) ? $button['label'] : 'Label Required',
						'attributes' => [
							'class' => !empty($button['class']) ? $button['class'] : null
						]
					];
					$form->addFieldSubmit($button['name'], $btnOptions);
				}
			} else {
				// sort out the default submit button
				$submitOptions = [
					'order' => $fieldCount++,
					'label' => !empty($options['save']['label']) ? $options['save']['label'] : _t('Submit Form')
				];
				$form->addFieldSubmit('Save', $submitOptions);
			}
		}
		return $form;
	}

	/**
	 * Save a phoebe form. This saves generically to daedalus and applicationForm forms.
	 *
	 * TODO - 13/07/2018 NJ - Move these into a more appropriate place
	 *
	 * @param string $phoebeType - the phoebe type of the form e.g. daedalus
	 * @param string $classType - the name of the form class type e.g. myTable
	 * @param object $form - the form if already created or null if it will be here
	 * @param string $formName - the form name if not the same as the class type
	 * @param string $metaInfo - any additional information that should be used in
	 *   the saving. The values would have been saved on the form during creation
	 *   if passed in then (@see getForm) and the fields were rendered.
	 *   These values if provided override and are any of [
	 *     'uuid' => the object uuid if editing
	 *     'dataSources' => any data sources that should be set on the saved object
	 *     'returnUrl' => where the form should go to after saving
	 *   ].
	 * @param string &$uuid - the saved object uuid either passed in or set on return
	 * @param array &$changeLogUuids - the object change log uuids that occured during the
	 *   saving. This will be 'edit' and possibly 'add' if the object was new
	 * @return bool - returns true if saved correctly or the set of errors if the
	 *   form couldn't validate
	 */
	public function saveForm($phoebeType, $classType, $form=null, $formName=null, $metaInfo=null, &$uuid=null, &$changeLogUuids=[])
	{
		$changeLogUuids = [];
		if (!$form) {
			$definition = $this->getFormDefinition($phoebeType, $classType);
			$form = new \neon\core\form\Form($definition);
		}
		if (!empty($formName))
			$form->setName($formName);
		if ($form->processRequest()) {
			$data = $form->getData();
			// prevent multiple renders resulting in multiple saves of the form
			static $alreadySubmitted = [];
			// allow odd case of submissions of different types of form within one post
			// from a controller using this directly. Don't include the data in the key as
			// this can change between submission and rerenders of the template.
			$submittedCheck = md5($phoebeType.$classType);
			if (!array_key_exists($submittedCheck, $alreadySubmitted)) {
				$meta = $this->formMergeMetaInformation($form, $metaInfo);
				$phoebe = $this->getIPhoebeType($phoebeType);
				$object = null;
				if (!empty($meta['uuid'])) {
					$object = $phoebe->getObject($meta['uuid']);
				} else if ($phoebeType == 'daedalus' && $form->hasField('_uuid')) {
					$object = $phoebe->getObject($form->getField('_uuid')->getValue());
				} else {
					$object = $phoebe->addObject($classType);
					$changeLogUuids['add'] = $object->getChangeLogUuid();
				}
				$uuid = $object->uuid;
				if (!empty($meta['dataSources']))
					$object->setDataSources($meta['dataSources']);
				if ($object->editObject($data) === true) {
					$alreadySubmitted[$submittedCheck] = $uuid;
					$changeLogUuids['edit'] = $object->getChangeLogUuid();
					if (!empty($meta['returnUrl']))
						neon()->response->redirect(html_entity_decode($meta['returnUrl']));
					return true;
				}
			} else {
				$uuid = $alreadySubmitted[$submittedCheck];
				return true;
			}
		}
		return $form->getErrors();
	}

	/**
	 * Get hold of an object grid definition given the phoebe and class type
	 * @param string $phoebeType  e.g. 'daedalus'
	 * @param string $classType  e.g. 'mytable'
	 * @param boolean $includeData  set to true to include the data field (can be big)
	 * @param boolean $includeDeleted  set to true to include the deleted column
	 * @return array
	 */
	public function getGridDefinition($phoebeType, $classType, $additionalColumns = [])
	{
		$class = $this->getService()->getPhoebeType($phoebeType)->getClass($classType);
		return $class ? $class->getObjectGridDefinition($additionalColumns) : [];
	}


	/**
	 * Extract out any form meta information added. Any non empty meta information
	 * passed in via the method takes precedence over anything returned from the form.
	 * This is to allow for extra security in the cases where the data can be derived
	 * on the server too.
	 * @param Form $form
	 * @param array $metaInfo - an array of any of 'uuid', 'returnUrl', 'dataSources'
	 * @return array
	 */
	protected function formMergeMetaInformation($form, $meta)
	{
		$data = $form->getData();
		foreach ($data as $k=>$v) {
			if (strpos($k, $this->formMetaPrefix) === 0) {
				$key = substr($k, strlen($this->formMetaPrefix));
				if ($key == $this->formMetaUuidString && empty($meta['uuid'])) {
					$meta['uuid'] = $v;
				} else if (strpos($key, $this->formMetaDataSourceString) === 0) {
					$itemKey = substr($key, strlen($this->formMetaDataSourceString));
					if (empty($meta['dataSources'][$itemKey]))
						$meta['dataSources'][$itemKey] = $v;
				} else if ($key==$this->formMetaReturnUrlString && empty($meta['returnUrl'])) {
					$meta['returnUrl'] = $v;
				}
			}
		}
		// capture the case where the form posted a uuid but the server didn't know
		// to create the uuid field as it didn't know the uuid at that point
		if (empty($meta['uuid'])) {
			$postedUuid = $form->getRawRequestData($this->formMetaPrefix.$this->formMetaUuidString);
			$meta['uuid'] = $postedUuid ? Html::sanitise($postedUuid) : null;
		}
		return $meta;
	}

}
