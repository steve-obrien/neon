<?php

/**
 * Manage an Daedalus table form. This allows you to use a form for a single
 * Daedalus table, either filling in some or all of the data.
 *
 * Usage:
 * a) creating a new form of type 'abc' which will set a $form and $submitted
 * parameter for use in the template
 *
 * ```
 *    {ddsForm classType='abc'}
 *    {if ($submitted)}
 *        // do something
 *    {else}
 *        {$form->run()}
 *    {/if}
 * ```
 * You can change the form and submitted parameter names using assignForm
 * and assignSubmitted respectively.
 *
 * b) editing an existing form of type 'abc' and UUID of $uuid
 *
 * ```
 *    {ddsForm classType='abc' uuid=$uuid}
 * ```
 *
 * c) changing the form id & the name of the assigned form and submitted parameters
 *
 * ```
 *    {ddsForm classType='abc' formId='formId' assignForm='myForm' assignSubmitted='myFormSubmitted'}
 *    {if ($myFormSubmitted)}
 *        // do something
 *    {else}
 *        {$myForm->run()}
 *    {/if}
 * ```
 *
 * d) ReadOnly: You can set whether or not the form can be edited by passing in readOnly. This
 * defaults to false.
 * ```
 *    {ddsForm classType='abc' readOnly=true}
 * ````
 *
 * e) Selecting Fields: You can set the fields you want to show on the form and in what order by setting
 * the 'fields' parameter to either [fieldName,...] or ['fieldName'=>'label',...] (or
 * any combination) to either get a label derived from the fieldName (underscore
 * replaced by spaces and all words capitalised) or to set the label
 * ```
 *    {ddsForm classType='abc' fields=['field1', 'field2', 'field3'=>'field 3 label'...]}
 * ```
 *
 * f) Filtering Maps: some fields get their data from other tables as a set of key=>value pairs
 * or map - for example, dynamic select lists. There are occasions when you need to filter those
 * lists say by project or client or combination of map fields.
 *
 * In these cases you can pass in a *mapFilters* to the form which will be applied to the
 * other table to return a subset of values. mapFilters are a set of srcField => [mappedField=>value,...].
 * Each srcField is on this dds table and the mappedFields are fields on the destination map table.
 * Filters are all applied as mapField=value and are all ANDed together.
 * ```
 *    {ddsForm classType='abc' mapFilters=[
 *       'formField1'=>[mapField1=>value1, mapField2=>value2,...],
 *       ...
 *    ]}
 * ```
 *
 * g) Formatting Maps: @see f) above. Each Daedalus table has a default map field that is
 * returned for e.g. select lists. There are times when you may want to change which field or
 * fields are returned and specify a format for them. In this case you can pass in a
 * *mapFields* parameter. These are a set of formField=>[mappedField,..., 'format'=>[x]] where
 * each field required is listed in order, and the format key is used to define how you want
 * the returned values to be formatted. This can have keys of prefix, postfix and concat each
 * taking a string. e.g. myField=>[field1, field2, 'format'=>['concat'=>': ']]
 * ```
 *    {ddsForm classType='abc' mapFilters=[
 *       'formField1'=>[mapField1=>value1, mapField2=>value2,...],
 *       ...
 *    ]}
 * ```
 *
 * @param $params
 *   Required parameters
 *   -------------------
 *     'classType'  => the class type of the form in Phoebe
 *
 *   Optional Parameters
 *   -------------------
 *     'uuid' => the form UUID if this is not a new form
 *     'id' => an HTML id for the form. If not set the UUID is used, or failing
 *       that the form type.
 *     'label' => the main title of the form if not the default
 *     'cssClass' => set additional css classes for the form
 *     'fields' => the set of fields (and order) you want displaying on the form
 *       or leave out to get all the available fields
 *     'mapFilters' => @see f) above - a set of ['formField' => ['tableField'=>'value',...]]
 *       to filter dynamic maps extracted from other tables
 *     'mapFields' => @see g) above - a set of formField=>['tableField's] that you want
 *       returned in a dynamic map if not the default mapped field. You can add a 'format'
 *       key with sub array ['concat', 'prefix', 'postfix'] to format how the
 *       fields will be combined into the map.
 *     'fieldDefaults' => set default values for fields as
 *       ['field'=>'default']
 *     'fieldProperties' => set provided properties for the form fields as
 *       ['field'=>['property'=>'value']]
 *     'assignForm' => set this to change the name of the assigned form parameter
 *       (default 'form')
 *     'assignSubmitted' => set this to use a different submitted parameter
 *       (default 'submitted')
 *     'assignSaved' => set this to use a different saved parameter
 *       (default 'saved')
 *     'assignErrors' => set this to use a different form errors parameter
 *       (default 'errors')
 *     'submitLabel' => a submit label or 'Save'
 *     'buttons' => an array of buttons as [
 *         ['name'=>'abc', 'label'=>'Abc', 'class'=>'additional classes'],...]
 *       ]. These will override the default submit button.
 *     'action' => URL to go to if you don't want to handle the form in the same page
 *     'readOnly' => set to true if you want the form to be read only
 *     'printOnly' => set to true if you want the form to be printable
 *     'dataSources' => these allow you to set particular fields on a form so that their values
 *       are fixed by the system and not editable by the user. The fields do not have to
 *       be displayed to the user in order for them to be set. Set these to an array of
 *       'formField'=>value pairs.
 *     'enableAjaxValidation' => boolean  whether or not to use AJAX validation. If you set this
 *       you need to check $errors to know if a submission was successful or not
 *     'enableAjaxSubmission' => boolean  whether or not to use AJAX submission
 *     'ajaxValidationUrl' => set to the URL for AJAX validation if not the default
 *     'returnUrl' => set the URL to redirect to after saving if required
 *     'name' => the name of the form if not the default
 *     'debug' => if true output some debug information about the form behaviour
 * @param $template  the smarty template
 *
 * @return the following parameters will be set on return. The
 *   'form' - the form object (this will be the value of assignForm if set)
 *   'submitted' - if the form was submitted (this will be the value of assignSubmitted if set)
 *   'errors' - whether or not there were any submission errors (this will be the value of assignErrors if set)
 *   'uuid' - the UUID of the created object if successful or null otherwise
 *   'changeLogUuid' - if the item was submitted, and the corresponding dds table has
 *     a change log, then the change log entry uuid will be returned here.
 */
function smarty_function_ddsForm($params, $template)
{
	// need to ensure we don't fetch items from the database more than necessary
	// or handle the form more than once otherwise we can create additional entries
	static $handledObjects = [];
	static $forms = [];

	$phoebeType = 'daedalus';

	// process the parameters
	if (!isset($params['classType']))
		throw new \RuntimeException("You must pass in the form's classType otherwise we don't which form it is. You passed in: ".print_r($params,true));
	$classType = $params['classType'];
	$cssClass = isset($params['cssClass']) ? $params['cssClass'] : null;
	$label = isset($params['label']) ? $params['label'] : null;
	$id = !empty($params['id']) ? $params['id'] : null;
	$saveOptions = !empty($params['submitLabel']) ? ['label'=>$params['submitLabel']] : [];
	$buttons = !empty($params['buttons']) ? $params['buttons'] : null;
	$action = !empty($params['action']) ? $params['action'] : null;
	$assignForm = !empty($params['assignForm']) ? $params['assignForm'] : 'form';
	$assignSaved = !empty($params['assignSaved']) ? $params['assignSaved'] : 'saved';
	$assignErrors = !empty($params['assignErrors']) ? $params['assignErrors'] : 'errors';
	$assignSubmitted = !empty($params['assignSubmitted']) ? $params['assignSubmitted'] : 'submitted';
	$readOnly = isset($params['readOnly']) ? (bool)$params['readOnly'] : false;
	$printOnly = isset($params['printOnly']) ? (bool)$params['printOnly'] : false;
	$uuid = !empty($params['uuid']) ? $params['uuid'] : null;
	$dataSources = !empty($params['dataSources']) ? $params['dataSources'] : null;
	$enableAjaxValidation = isset($params['enableAjaxValidation']) ? (bool)$params['enableAjaxValidation'] : true;
	$enableAjaxSubmission = isset($params['enableAjaxSubmission']) ? (bool)$params['enableAjaxSubmission'] : false;
	$ajaxValidationUrl = !empty($params['ajaxValidationUrl']) ? $params['ajaxValidationUrl'] : '/phoebe/database/index/validate-form';
	$returnUrl = !empty($params['returnUrl']) ? $params['returnUrl'] : null;
	$formName = !empty($params['name']) ? $params['name'] : null;
	$fields = !empty($params['fields']) ? $params['fields'] : [];
	$mapFilters = !empty($params['mapFilters']) ? $params['mapFilters'] : [];
	$mapFields = !empty($params['mapFields']) ? $params['mapFields'] : [];
	$fieldDefaults = !empty($params['fieldDefaults']) ? $params['fieldDefaults'] : [];
	$fieldProperties = !empty($params['fieldProperties']) ? $params['fieldProperties'] : [];
	// prefix invalid (HTML5) ids with a letter
	$id = ($id ? $id : ($uuid ? $uuid : null));
	if ($id) {
		if (is_numeric($id[0]) || $id[0] == '-' || $id[0] == '_')
			$id = 'z' . $id;
	}
	$changeLogUuids = [];

	// get form definition
	$phoebe = neon('phoebe')->getIPhoebeType($phoebeType);
	$formKey = $classType.$uuid.$id;
	if (array_key_exists($formKey, $forms)) {
		$form = $forms[$formKey];
	} else {
		// creating a form is expensive so do it only once per object
		$form = neon('phoebe')->getForm($phoebeType, $classType, [
			'id' => $id,
			'name' => $formName,
			'label' => $label,
			'cssClass' => $cssClass,
			'save'=>$saveOptions,
			'buttons' => $buttons,
			'readOnly' => $readOnly,
			'printOnly' => $printOnly,
			'action'=>$action,
			'enableAjaxValidation' => $enableAjaxValidation,
			'enableAjaxSubmission' => $enableAjaxSubmission,
			'ajaxValidationUrl' => url([$ajaxValidationUrl, 'classType' => $classType, 'name'=>$formName]),
			'uuid' => $uuid,
			'dataSources' => $dataSources,
			'returnUrl' => $returnUrl,
			'fields' => $fields,
			'mapFilters' => $mapFilters,
			'mapFields' => $mapFields,
			'fieldDefaults' => $fieldDefaults,
			'fieldProperties' => $fieldProperties
		]);
		$forms[$formKey] = $form;
	}
	$saveFormResults = [
		$assignSaved => false,
		$assignErrors => null,
		$assignSubmitted => false,
		'uuid' => $uuid,
		'changeLogUuids' => $changeLogUuids
	];

	// is this a submitted form ready for saving and we haven't already handled it
	if (!$readOnly && $form->hasRequestData()) {
		if (!array_key_exists($uuid, $handledObjects)) {
			// pass in any metaInfo that we know about here
			$metaInfo = [
				'uuid' => $uuid,
				'dataSources' => $dataSources,
				'returnUrl' => $returnUrl
			];
			$result = neon('phoebe')->saveForm($phoebeType, $classType, $form, $formName, $metaInfo, $uuid, $changeLogUuids);
			if ($result === true && $uuid) {
				$object = $phoebe->getObject($uuid);
				$form->loadFromDb($object->data);
				$saveFormResults['uuid'] = $uuid;
				$saveFormResults[$assignSaved] = true;
				$saveFormResults['changeLogUuids'] = $changeLogUuids;
			} else {
				$saveFormResults[$assignErrors] = json_encode($result);
			}
			$saveFormResults[$assignSubmitted] = true;
			$handledObjects[$uuid] = $saveFormResults;
		} else {
			$saveFormResults = $handledObjects[$uuid];
		}
	}

	// assign all of the save results to the template
	foreach ($saveFormResults as $k=>$v)
		$template->assign($k,$v);


	// assign the resulting form
	$template->assign($assignForm, $form);
}
