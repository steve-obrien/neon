<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\appforms;

use neon\phoebe\services\adapters\common\PhoebeClassBase;
use neon\phoebe\interfaces\forms\IPhoebeFormClass;
use neon\core\helpers\Hash;


/**
 * Provides a applicationForm phoebe class
 */
class PhoebeClass extends PhoebeClassBase
implements IPhoebeFormClass
{
	use PhoebeTrait;

	/**
	 * @inheritdoc
	 */
	public function getClassFormDefinition(array $fields=[])
	{
		// if there is no definition return an empty form
		if (empty($this->definition))
			return [];

		// TODO - NJ20180426 - see if we can get Phoebe to listen to $fields

		// create the form
		$definition = $this->replaceDefinitionFromDds($this->definition);
		$this->setMemberFieldNames($definition);
		return $this->convertToForm($definition);
	}

	public function getClassBuilderDefinition()
	{
		return $this->definition;
	}

	/**
	 * @inheritdoc
	 */
	public function getObjectGridDefinition($additionalColumns = [])
	{
		return parent::getObjectGridDefinition($additionalColumns);
	}

	/**
	 * @inheritdoc
	 */
	public function editClass($changes)
	{
		if (isset($changes['definition']))
			$changes['definition'] = $this->prepareDefinition($changes['definition']);
		parent::editClass($changes);
	}

	/**
	 * @inheritdoc
	 */
	public function getRequiredDataSources()
	{
		$dataSources = isset($this->definition['dataSources']) ? $this->definition['dataSources'] : [];
		$returnedDataSources = [];
		foreach ($dataSources as $dataSource) {
			$key = $dataSource['label'];
			$returnedDataSources[$key] = [
				'class_type'=>$dataSource['class_type'],
				'key'=>$key
			];
		}
		return $returnedDataSources;
	}

	/**
	 * @inheritdoc
	 */
	public function findPhoebeKeysForClassTypesInDefinition($ddsClassTypes)
	{
		$definition = $this->definition;
		$applicationDataPhoebeUuids = [];
		foreach ($ddsClassTypes as $ct)
			$applicationDataPhoebeUuids[$ct] = null;
		foreach ($definition['renderables'] as $k=>$r) {
			if ($r['type'] == 'ClassComponent' && in_array($r['class_type'], $ddsClassTypes)) {
				$applicationDataPhoebeUuids[$r['class_type']] = $k;
			}
		}
		return $applicationDataPhoebeUuids;
	}

	// ---------------------------------------- //
	// ---------- Additional Methods ---------- //

	/**
	 * Set the definition on the model - used when merging the stored definition
	 * with that from Daedalus
	 *
	 * @param array $definition
	 */
	public function setDefinition($definition)
	{
		$this->getModel()->definition = $definition;
	}

	/**
	 * Keep the model's definition here - overrides are applied in convertToForm
	 * level and not in the phoebe class definition
	 * @return array
	 */
	public function getDefinition()
	{
		return $this->getModel()->definition;
	}

	protected function convertToForm($definition)
	{
		$form = [];
		$form['name'] = $this->canonicaliseRef($definition['name']);
		$fields = $this->convertRenderablesToFields($definition['rootNode'], $definition['renderables']);
		$form['fields'] = $fields['fields'];
		// apply any overrides to the form
		if ($this->_overrides)
			$form = array_replace_recursive($form, $this->_overrides);
		return $form;
	}

	protected function convertRenderablesToFields($key, $renderables) {
		if (!isset($renderables[$key])) {
			dd("Key $key doesn't exist in the form definition", $renderables);
		throw new \RuntimeException("Key $key doesn't exist in the form definition");}
		$rend = $renderables[$key];
		$field=[];
		if (!empty($rend['type'])) {
			$field = [];
			switch($rend['type']) {
				case 'FormComponent':
					if (isset($rend['items']))
						$field['fields'] = $this->addItems($rend['items'], $renderables);
				break;
				case 'ClassComponent':
					$classDefinition = isset($rend['definition']) ? $rend['definition'] : [];
					if (count($rend['items'])>0) {
						$field['fields'] = [];
						$field['label'] = '';
						if (isset($classDefinition['showLabel']) && $classDefinition['showLabel'])
							$field['label'] = isset($classDefinition['label']) ? $classDefinition['label'] : $rend['label'];
						$field['description'] = '';
						if (isset($classDefinition['showDescription']) && $classDefinition['showDescription'])
							$field['description'] = isset($classDefinition['description']) ? $classDefinition['description'] : $rend['description'];
						if ($this->isRepeater($rend)) {
							$this->addRepeaterHeader($field, $rend['definition'], $key);
							if (isset($classDefinition['showLabel']) && $classDefinition['showLabel'])
								$field['template']['label'] = isset($classDefinition['label']) ? $classDefinition['label'] : $rend['label'];
							$field['template']['fields'] = $this->addItems($rend['items'], $renderables);
							$field['template']['fields'][] = $this->createHiddenField('_renderable', $key);
							$field['template']['fields'][] = $this->createHiddenField('_classType', $rend['class_type']);
							$field['template']['fields'][] = $this->createHiddenField('_uuid');
						} else {
							$field['class'] = '\\neon\\core\\form\\Form';
							$field['name'] = $key;
							$field['dataKey'] = $field['name'];
							$field['fields'] = $this->addItems($rend['items'], $renderables);
							$field['fields'][] = $this->createHiddenField('_renderable', $key);
							$field['fields'][] = $this->createHiddenField('_classType', $rend['class_type']);
							$field['fields'][] = $this->createHiddenField('_uuid');
						}
					}
				break;
				case 'MemberComponent':
				case 'ElementComponent':
					$field = $rend['definition'];
					// not all components have names but the form requires everything to have one
					if (empty($field['name'])) {
						$field['name'] = $this->createFieldName($field);
					}
				break;
			}
		}
		return $field;
	}

	protected function addItems($items, $renderables)
	{
		$field = [];
		$order = 1;
		foreach ($items as $rKey) {
			$subField = $this->convertRenderablesToFields($rKey, $renderables, 1);
			if (isset($subField[$rKey]))
				$field[$rKey] = $subField[$rKey];
			else
				$field[$rKey] = $subField;
			$field[$rKey]['order'] = $order++;
		}
		return $field;
	}

	protected function isRepeater($renderable)
	{
		return (isset($renderable['definition']['isRepeater']) && $renderable['definition']['isRepeater'] == true);
	}

	protected function addRepeaterHeader(&$field, $repeat, $key)
	{
		// set the rpeater header part
		$field['class'] = "neon\\core\\form\\FormRepeater";
		$field['count'] = isset($repeat['defaultInstances']) ? (integer) $repeat['defaultInstances'] : 1;
		$field['countMax'] = isset($repeat['maxInstances']) ? (integer) $repeat['maxInstances'] : 0;
		$field['countMin'] = isset($repeat['minInstances']) ? (integer) $repeat['minInstances'] : 1;
		$allowChangeOfRepeats = ($field['countMin'] != $field['countMax']);
		$field['allowAdd'] = $allowChangeOfRepeats;
		$field['allowRemove'] = $allowChangeOfRepeats;

		// now set the template header part
		$field['template']['class'] =  "neon\\core\\form\\Form";
	}

	/**
	 * Create a hidden object id field required when sending back the data
	 * so we know what object it belongs to if set
	 * @return array
	 */
	protected function createHiddenField($name, $value=null)
	{
		return [
			'class' => "neon\\core\\form\\fields\\Hidden",
			'name' => $name,
			'order' => 0,
			'value' => $value
		];
	}

	protected function createFieldName($field)
	{
		$uuid = isset($field['id']) ? $field['id'] : Hash::uuid64();
		return str_replace(['-','_'],['a','Z'], $uuid);
	}

}