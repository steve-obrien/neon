<?php

namespace neon\phoebe\services\adapters\appforms;
use neon\core\helpers\Hash;

trait PhoebeTrait
{
	/**
	 * Run through the definition and pull it into the format required for phoebe.
	 * . Convert all uuids into our format
	 * . extract out data that may be hidden
	 * . work out any changes made by the user over the default definition
	 *
	 * @param type $definition
	 */
	protected function prepareDefinition($definition)
	{
		$this->sanitiseDefinition($definition);
		$this->convertAllUuidsToUuid64s($definition);
		$classes = $this->getDdsClassDefinitions($definition);
		$this->extractDefinitionChanges($classes, $definition);
		$this->setRelationsAsArray($definition);
		return $definition;
	}

	/**
	 * Update the class definition from Daedalus, so that any not overridden
	 * values can be changed if they were changed in the Daedalus definition
	 * @param type $definition
	 */
	protected function updateDefinitionFromDds($definition, $includeAll=false)
	{
		if (!empty($definition['renderables'])) {
			$classes = $this->getDdsClassDefinitions($definition);
			foreach ($definition['renderables'] as &$renderable) {
				if ($this->isMember($renderable)) {
					if (isset($classes[$renderable['class_type']]['fields'][$renderable['member_ref']])) {
						// get the daedalus definition
						$memberDefinition = $classes[$renderable['class_type']]['fields'][$renderable['member_ref']];
						// set the definition to be the fields from Daedalus but only using the keys in the renderable definition
						$newDef = array_intersect_key($memberDefinition['definition'], $renderable['definition']);
						$newDef = array_merge($newDef, $renderable['changes']);
						$renderable['definition'] = $newDef;
					}
				}
			}
		}
		return $definition;
	}

	/**
	 * Replace the renderable definitions with the definition taken from daedalus
	 * @param type $definition
	 */
	protected function replaceDefinitionFromDds($definition)
	{
		if (empty($definition))
			return [];

		$classes = $this->getDdsClassDefinitions($definition);
		foreach ($definition['renderables'] as &$renderable) {
			if ($this->isMember($renderable)) {
				if (isset($classes[$renderable['class_type']]['fields'][$renderable['member_ref']])) {
					// get the daedalus definition
					$memberDefinition = $classes[$renderable['class_type']]['fields'][$renderable['member_ref']];
					$newDef = $memberDefinition;
					$newDef = array_merge($newDef['definition'], $renderable['changes']);
					$renderable['definition'] = [$renderable['member_ref'] => $newDef];
				}
			}
		}
		return $definition;
	}

	protected function setMemberFieldNames(&$definition)
	{
		foreach ($definition['renderables'] as $key=>&$renderable) {
			if ($this->isMember($renderable)) {
				$itemKey = key($renderable['definition']);
				$newDef = $renderable['definition'][$itemKey];
				$newDef['name'] = $renderable['member_ref'];
				$newDef['dataKey'] = $renderable['member_ref'];
				unset($renderable['definition'][$itemKey]);
				$renderable['definition'][$key] = $newDef;
			}
		}
	}

	/**
	 * Code to convert older style relation information into an array format
	 * Should become unnecessary once all appForms updated
	 *
	 * @param type $definition
	 */
	protected function setRelationsAsArray(&$definition)
	{
		$renderableKeys = array_keys($definition['renderables']);
		foreach ($definition['renderables'] as $key=>&$r) {
			if (isset($r['relationName']) && !isset($r['relations'])) {
				$parentClassId = null;
				if (isset($r['parentClassId'])) {
					$parentClassId = $r['parentClassId'];
					if (!in_array($r['parentClassId'], $renderableKeys))
						$parentClassId = $this->findParentClassIdFromType($r['parentClassId'], $definition['renderables']);
				}
				$r['relations'] = [
					[
						'classId' => $parentClassId,
						'memberRef' => $r['relationName'],
						'method' => 'append'
					]
				];
			}
		}
	}

	protected function findParentClassIdFromType($type, $renderables)
	{
		foreach ($renderables as $r) {
			if ($this->isClass($r) && $r['class_type'] == $type)
				return $r['id'];
		}
		// can't find it so return the original so as not to break anything
		return $type;
	}

	/**
	 * Determine if the renderable is a member or not
	 * @param array $renderable
	 * @return boolean
	 */
	protected function isMember($renderable)
	{
		return (
			(!empty($renderable['type']) && ($renderable['type'] == 'MemberElement' || $renderable['type'] == 'MemberComponent')) ||
			(!empty($renderable['tagName']) && $renderable['tagName'] == 'MemberElement')
		);
	}

	protected function isClass($renderable)
	{
		return $renderable['type'] == 'ClassComponent';
	}

	/**
	 * Get the Dds class definitions for the set of provided classes
	 * @param type $definition
	 * @return array  the classes
	 */
	protected function getDdsClassDefinitions($definition)
	{
		// don't keep getting the same classes each time as they don't change
		// during a set of phoebe class request
		static $requestedClasses = [];
		$requiredClassTypes = []; // the class types needed
		$newClassTypes = []; // any ones that haven't already been got
		foreach ($definition['renderables'] as $renderable) {
			if (isset($renderable['type']) && $renderable['type'] == 'ClassComponent') {
				$requiredClassTypes[] = $renderable['class_type'];
				if (!array_key_exists($renderable['class_type'], $requestedClasses)) {
					$newClassTypes[$renderable['class_type']] = $renderable['class_type'];
				}
			}
		}
		if (count($newClassTypes)>0) {
			$newClasses = $this->ddsPhoebe()->getClasses($newClassTypes);
			foreach ($newClasses as $newClass) {
				$requestedClasses[$newClass->class_type] = $newClass;
			}
		}
		$definitions = [];
		foreach ($requiredClassTypes as $rct)
			$definitions[$rct] = isset($requestedClasses[$rct]) ? $requestedClasses[$rct]->definition : null;
		return $definitions;
	}

	protected function extractDefinitionChanges($classes, &$definition)
	{
		foreach ($definition['renderables'] as &$renderable) {
			if ($this->isMember($renderable)) {
				$renderDefinition = $renderable['definition'];
				$memberDefinition = $classes[$renderable['class_type']]['fields'][$renderable['member_ref']];
				$differences = [];
				foreach ($renderDefinition as $key=>$value) {
					if (!isset($memberDefinition['definition'][$key]) || ($memberDefinition['definition'][$key] != $value))
						$differences[$key] = $value;
				}
				$renderable['changes']=$differences;
			}
		}
	}

	/**
	 * Sanitise the definition in case there are bad things in there. E.g. orphaned
	 * items can occur during deletion of items from within Phoebe.
	 * @param type $definition
	 */
	protected function sanitiseDefinition(&$definition)
	{
		/** Remove Orphaned Renderables **/
		$items = [ $definition['rootNode'] ];
		foreach ($definition['renderables'] as $renderable) {
			if (isset($renderable['items'])) {
				$items = array_merge($items,$renderable['items']);
			}
		}
		// remove renderables not linked via rootNode
		foreach ($definition['renderables'] as $k=>$r) {
			if (!in_array($k, $items)) {
				unset($definition['renderables'][$k]);
			}
		}
	}

	/**
	 * Convert any longer format uuids to their uuid64 cousins. This is to keep everything
	 * server side in keeping with the rest of the system, and also will allow us to
	 * combine uuids together more neatly for human reading
	 * @param array $definition
	 * @return array  the modified definition
	 */
	protected function convertAllUuidsToUuid64s(&$definition)
	{
		$keys = array_keys($definition['renderables']);
		$serialised = json_encode($definition);
		$uuid64s = [];
		foreach ($keys as $key) {
			$uuid64s[] = Hash::uuid2uuid64($key);
		}
		$serialised = str_replace($keys, $uuid64s, $serialised);
		$definition = json_decode($serialised,true);
	}

	private function ddsPhoebe()
	{
		if (!$this->_ddsPhoebe)
			$this->_ddsPhoebe = neon('phoebe')->getIPhoebeType('daedalus');
		return $this->_ddsPhoebe;
	}
	private $_ddsPhoebe = null;
}
