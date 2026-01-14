<?php

/**
 * Manage an application form
 *
 * Usage:
 * a) creating a new form of type abc which will set a $form and $submitted
 * parameter for use in the template
 *
 * ```
 *    {appForm classType='abc' }
 *    {if ($submitted)}
 *        // do something
 *    {else}
 *        {$form->run()}
 *    {/if}
 * ```
 * You can change the form and submitted parameter names using assignForm
 * and assignSubmitted respectively.
 *
 * b) editing an existing form of type 'abc' and uuid of $uuid
 *
 * ```
 *    {appForm classType='abc' uuid=$uuid}
 * ```
 *
 * c) changing the name of the assigned form parameter and submitted results
 *
 * ```
 *    {appForm classType='abc' assignForm='myForm' assignSubmitted='myFormSubmitted'}
 *    {if ($myFormSubmitted)}
 *        // do something
 *    {else}
 *        {$myForm->run()}
 *    {/if}
 * ```
 *
 * d) Assign required data sources to a form. If sources are required, this will
 * complain if they are not set.
 *
 * ```
 *    {appForm classType='abc' dataSources=['sourceKey'=>'soureUuid',...]}
 * ```
 *
 * e) You can set whether or not the form can be edited by passing in readOnly. This
 * defaults to false.
 * ```
 *    {appForm classType='abc' readOnly=true}
 * ````
 *
 * @param $params
 *   Required parameters
 *   -------------------
 *     'classType'  => the class type of the form in Phoebe
 *
 *   Optional Parameters
 *   -------------------
 *     'uuid' => the form uuid if this is not a new form
 *     'id' => an html id for the form. If not set the uuid is used, or failing
 *       that the form type.
 *     'label' => the form label if not using the default
 *     'cssClass' => set additional css classes for the form
 *     'formData' => any injected form data that the form needs to
 *       draw itself regardless of new or old
 *     'submitLabel' => a submit label or 'Save'
 *     'buttons' => an array of buttons as [
 *         ['name'=>'abc', 'label'=>'Abc', 'class'=>'additional classes'],...]
 *       ]. These will override the default submit button.
 *     'assignForm' => set this to change the name of the assigned form parameter
 *       (default 'form')
 *     'assignSubmitted' => set this to use a different submitted parameter
 *       (default 'submitted')
 *     'action' => url to go to if you don't want to handle the form in the same page
 *     [NOT YET AVAILABLE] 'fieldDefaults' => set default values for fields as
 *       ['field'=>'default']
 *     [NOT YET AVAILABLE] 'mapFilters' => a set of ['formField' => ['tableField'=>'value',...]] to filter
 *       dynamic maps extracted from other tables
 *     [NOT YET AVAILABLE] 'mapFields' => a set of form field = ['tableField's] that you want returned
 *       in a dynamic map if not the default mapped field. You can add a 'format'
 *       key with sub array ['concat', 'prefix', 'postfix'] to format how the
 *       fields will be combined into the map
 *     'enableAjaxValidation' => bool  whether or not to use ajax validation
 *     'enableAjaxSubmission' => bool  whether or not to use ajax submission
 *     'ajaxValidationUrl' => set to the URL for AJAX validation if not the default
 *     'dataSources' => set of sourceKey sourceUuid pairs for all required data sources
 *       These would have been defined in the application form builder
 *     'initialiseFromDds' => set of classKey=>ddsUuid pairs for any classes that need
 *       to point to a preexisting Daedalus table row rather than create a new one
 *     'readOnly' => set to true if you want the form to be read only
 *     'printOnly' => set to true if you want to print the form
 *     'name' => the name of the form if not the default
 *
 * @param $template  the smarty template
 * @return null
 * @throws
 */
function smarty_function_appForm($params, $template)
{
	// As this can be called multiple times within the rendering of pages we
	// need to ensure we don't fetch items from the database more than necessary
	// or handle the form more than once otherwise we can create additional entries
	static $handledObjects = [];
	static $forms = [];
	static $appFormClasses = [];
	static $appFormObjects = [];

	$appForm = 'applicationForm';
	// process the parameters
	if (!isset($params['classType']))
		throw new \RuntimeException("You must pass in the form's classType otherwise we don't which form it is. You passed in: ".print_r($params,true));
	$classType = $params['classType'];
	$uuid = !empty($params['uuid']) ? $params['uuid'] : null;
	$id = !empty($params['id']) ? $params['id'] : null;
	$cssClass = !empty($params['cssClass']) ? $params['cssClass'] : null;
	$formName = !empty($params['name']) ? $params['name'] : null;
	$saveOptions = !empty($params['submitLabel']) ? ['label'=>$params['submitLabel']] : [];
	$buttons = !empty($params['buttons']) ? $params['buttons'] : null;
	$label = !empty($params['label']) ? $params['label'] : null;
	$action = !empty($params['action']) ? $params['action'] : null;
	$assignForm = !empty($params['assignForm']) ? $params['assignForm'] : 'form';
	$assignSubmitted = !empty($params['assignSubmitted']) ? $params['assignSubmitted'] : 'submitted';
	$dataSources = !empty($params['dataSources']) ? $params['dataSources'] : [];
	$readOnly = isset($params['readOnly']) ? (bool)$params['readOnly'] : false;
	$printOnly = isset($params['printOnly']) ? (bool)$params['printOnly'] : false;
	$enableAjaxValidation = isset($params['enableAjaxValidation']) ? (bool)$params['enableAjaxValidation'] : true;
	$enableAjaxSubmission = isset($params['enableAjaxSubmission']) ? (bool)$params['enableAjaxSubmission'] : false;
	$ajaxValidationUrl = !empty($params['ajaxValidationUrl']) ? $params['ajaxValidationUrl'] : '/phoebe/appforms/index/validate-form';
	$initialiseFromDds = !empty($params['initialiseFromDds']) ? $params['initialiseFromDds'] : null;
	//$mapFilters = !empty($params['mapFilters']) ? $params['mapFilters'] : [];
	//$mapFields = !empty($params['mapFields']) ? $params['mapFields'] : [];

	// get hold of the form class
	$phoebe = neon('phoebe')->getIPhoebeType('applicationForm');
	if (array_key_exists($classType, $appFormClasses)) {
		$class = $appFormClasses[$classType];
	} else {
		$class = $phoebe->getClass($classType);
		$appFormClasses[$classType] = $class;
	}
	if (!$class)
		throw new \RuntimeException('Error - cannot get hold of the form class '.$classType);

	$requiredDataSources = array_keys($class->getRequiredDataSources());
	$missingDataSources = array_diff($requiredDataSources, array_keys($dataSources));
	if (count($missingDataSources)) {
		throw new \RuntimeException('Error - you need to provide uuids for the following classes: '.implode(', ',$missingDataSources));
	}

	// prefix invalid (HTML5) ids with a letter
	$id = ($id ? $id : ($uuid ? $uuid : null));
	if ($id) {
		if (is_numeric($id[0]) || $id[0] == '-' || $id[0] == '_')
			$id = 'z' . $id;
	}

	// get hold of the form definition
	$formKey = md5(serialize($params));
	if (array_key_exists($formKey, $forms)) {
		$form = $forms[$formKey];
	} else {
		// creating a form is expensive so do it only once during a render
		$form = neon('phoebe')->getForm($appForm, $classType, [
			'id' => $id,
			'name' => $formName,
			'uuid' => $uuid,
			'cssClass' => $cssClass,
			'label'=>$label,
			'save'=>$saveOptions,
			'buttons' => $buttons,
			'readOnly' => $readOnly,
			'printOnly' => $printOnly,
			'action'=>$action,
			'enableAjaxValidation' => $enableAjaxValidation,
			'enableAjaxSubmission' => $enableAjaxSubmission,
			'ajaxValidationUrl' => url([$ajaxValidationUrl, 'name'=>$formName, 'classType' => $classType]),
			'initialiseFromDds' => $initialiseFromDds,
			//'mapFilters'=>$mapFilters,
			//'mapFields'=>$mapFields
		]);

		$forms[$formKey] = $form;
	}

	// handle the form processing saving or displaying
	$object = null;
	if (array_key_exists($uuid, $appFormObjects)) {
		$object = $appFormObjects[$uuid];
	} else if ($uuid) {
		$phoebe = neon('phoebe')->getIPhoebeType($appForm);
		$object = $phoebe->getObject($uuid);
		if (!$object)
			throw new \yii\web\NotFoundHttpException('The requested form was not found. Uuid='.$uuid);
		$appFormObjects[$uuid] = $object;
	}
	$template->assign($assignSubmitted, false);

	// is this a submitted form ready for saving
	if (!$readOnly) {
		$request = neon()->request;
		if ($request->isPost && !in_array($uuid, $handledObjects) && $form->processRequest()) {
			if (!$object && !$uuid) {
				$object = $phoebe->addObject($classType);
				$uuid = $object->_uuid;
			}
			$object->setDataSources($dataSources);
			$object->editObject($form->getData());
			// load the data back again as it will have changed
			$object = $phoebe->getObject($uuid);
			$appFormObjects[$uuid] = $object;
			$template->assign($assignSubmitted, true);
			$handledObjects[] = $uuid;
		}
	}
	// is this a form we should load from the database
	if ($object) {
		$form->loadFromDb($object->data);
	}

	// assign the resulting form
	$template->assign($assignForm, $form);
}

