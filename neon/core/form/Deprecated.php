<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 19/07/2018
 * Time: 15:35
 */

namespace neon\core\form;


use neon\core\helpers\Arr;

/**
 * Collection of deprecated functions
 * Class Deprecated
 * @deprecated
 * @package neon\core\form
 */
class Deprecated
{
	/**
	 * The DDS functions could be placed in to a child class to isolate specific DDS functionality
	 * Really the DDS is almost like a form specific backend
	 *
	 * - until this is better understood we have direct access.
	 * - [NJ - 2017-11-19] - wonder if these really belong at this low level. Access to DDS is via Phoebe
	 *   so should these be at the neon/forms level and not at the neon/core/form level?
	 */

	/**
	 * Load a form from a DDS class type
	 * @param \neon\core\form\Form $form
	 * @param string $classType the dds class ref string that we would like to load a form for
	 * @param boolean $includeDeleted include soft deleted fields
	 * @return \neon\core\form\Form
	 * @deprecated should be using phoebe this should be replaced with using the loadFromDefinition via phoebe
	 */
	public static function ddsLoadFrom($form, $classType, $includeDeleted=false)
	{
		\Neon::warning('neon\core\form\Form::ddsLoadFrom is a deprecated function - phoebe is responsible for loading form definitions');
		$class = neon()->getDds()->getIDdsClassManagement()->getClass($classType);
		if ($classType === null) {
			$members = [];
		} else {
			$members = neon()->getDds()->getIDdsClassManagement()->listMembers($classType, $includeDeleted);
		}
		$immutable = [
			// the form name maps directly to the DDS class_type
			'name' => $class['class_type'],
			'label' => $class['label'],
			'hint' => $class['description'],
		];

		$formDefinition = json_decode($class['definition'], true);
		// remove fields information - this will be defined by members
		if (isset($formDefinition['fields'])) { unset($formDefinition['fields']); }
		$formDefinition = Arr::merge($formDefinition, $immutable);
		// remove the class option
		Arr::remove($formDefinition, 'class');
		foreach($formDefinition as $key => $value) {
			$form->$key = $value;
		}
		// Add fields to the form from the individual members
		if (! empty($members) ) {
			foreach ($members as $member) {
				$form->ddsAddField($member);
			}
		}
		$form->orderFields();
		return $form;
	}

	/**
	 * @param \neon\core\form\Form $form
	 * @param null $classType
	 * @param null $ddsMembers
	 * @return void
	 * @deprecated
	 */
	public static function ddsSaveDefinition($form, $classType=null, $ddsMembers=null)
	{
		\Neon::warning('neon\core\form\Form::ddsSaveDefinition is a deprecated function - phoebe is now responsible for saving form definitions');
		// \neon\phoebe\builders\database\builder\Dds::saveDefinition($form);
		$ddsClassManager = neon()->getDds()->getIDdsClassManagement();
		$isNewClass = ($ddsClassManager->getClass($form->getName()) === null);
		$classType = $form->getName();
		$formDefinition = $form->toArray();
		// remove the fields information - only store form properties - the field definitions are stored in each member
		unset($formDefinition['fields']);
		if ($isNewClass) {
			$ddsClassManager->addClass($classType, $form->getLabel(), $form->getHint(), 'deprecated');
		} else {
			$ddsClassManager->editClass($classType, [
				'label' => $form->getLabel(),
				'description' => $form->getHint(),
			]);
		}
		$members = $ddsClassManager->listMembers($classType, true);
		foreach($form->getFields() as $key => $field) {
			// set the order value to be the same as how we received them
			$field->ddsSaveDefinition($classType, $members, $key);
		}
	}

	/**
	 * Adds a field to the form from a DDS member definition
	 * @param $form \neon\core\form\Form
	 * @param $member array similar to:
	 * ~~~php
	 * [
	 *     'id' => 3
	 *     'member_ref' => 'name',
	 *     'label' => 'Name',
	 *     'data_type_ref' => 'textshort'
	 *     'description' => 'Handy hint text'
	 *     'definition' => '{}' // json string
	 * )
	 * ~~~
	 * @deprecated should be using phoebe
	 */
	public static function ddsAddField($form, $member)
	{
		\Neon::warning('neon\core\form\Form::ddsAddField is a deprecated function - phoebe is responsible for integration with DDS');
		// \neon\phoebe\builders\database\builder\Dds::loadFieldFromDefinition($member, $this);
		$dataTypeMap = $form->getDefaultTypes();
		// we do not want this to get out of sync - the member ref is used for the form field name
		// the member is the primary source these fields will ignore any contradictory setting in the definition
		$immutable = [
			'name' => $member['member_ref'],
			'dataKey' => $member['member_ref'],
			// this is phoebe and dds specific:
			'deleted' => isset($member['deleted']) ? $member['deleted'] : 0,
			'mapField' => $member['map_field']
		];
		// these are defaults for the member - they can be overridden by the members sub definition json which can give more information
		// about particular front end widgets to use and additional validations etc - however if its not defined at all then
		// we set some sensible defaults
		$default = isset($dataTypeMap[$member['data_type_ref']]) ? $dataTypeMap[$member['data_type_ref']] : [];
		$defaults = Arr::merge($default, [
			'label' => $member['label'], // the DDS label might be different to the desired widget label? Perhaps not? But for now we allow it
			'hint' => $member['description']
		]);
		// the definition might supply more information
		$widgetDefinition = [];
		if (!empty($member['definition'])){
			// if the definition is already array then assume it is decoded - if it is not then assume it is a json string
			$widgetDefinition = is_array($member['definition'])
				? $member['definition']
				: json_decode($member['definition'], true);
		}
		// remove the value from the widget definition as this should be populated by the actual data
		Arr::remove($widgetDefinition, 'value');
		$config = Arr::merge($defaults, $widgetDefinition , $immutable);

		$form->add($config);
	}

	/**
	 * Saves the current field object as a member of the DDS class
	 * The DDS class can be specified by the $classType param,
	 * if not defined it will use the parent form's' name as the classType
	 *
	 * @param $field \neon\core\form\fields\Field
	 * @param string|null $classType
	 * @param array|null $ddsMembers if not specified it will look up the members of the classType
	 * @deprecated
	 */
	public static function ddsSaveDefinitionField($field, $classType=null, $ddsMembers=null)
	{
		\Neon::warning('neon\core\form\field::ddsSaveDefinition is a deprecated function - you should be using phoebe');
		$iDdsClassManagement = neon()->getDds()->getIDdsClassManagement();
		// get the DDS classType from the parent form's name if not defined
		if ($classType === null) {
			$classType = $field->getForm()->getName();
		}
		// get the DDS members for the class type if not defined
		if ($ddsMembers === null) {
			$ddsMembers = $iDdsClassManagement->listMembers($classType);
		}

		// ignore if this is a deleted field
		if ($field->deleted == 1)
			return;

		// check if member exists
		$memberRef = $field->getDdsName();
		$memberExists = isset($ddsMembers[$memberRef]);
		$definition = $field->toJson();

		// get any additional information required
		$choices = $field->extractChoices();
		$linkClass = $field->extractLinkClass();
		if ($memberExists) {
			// edit
			$iDdsClassManagement->editMember(
				$classType,
				$memberRef, [
					'label' => $field->getLabel(),
					'description' => $field->getHint(),
					'definition' => $definition,
					'choices' => $choices,
					'link_class'=>$linkClass
				]
			);
		} else {
			$iDdsClassManagement->addMember(
				$classType,
				$memberRef,
				$field->ddsDataType,
				$field->getLabel(),
				$field->getHint(),
				$definition,
				['choices'=>$choices, 'link_class'=>$linkClass]
			);
		}
		if ($field->mapField) {
			$iDdsClassManagement->setMemberAsMapField($classType, $memberRef);
		}
	}
}