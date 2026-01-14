<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\appforms;

use neon\phoebe\services\adapters\common\PhoebeObjectBase;
use neon\phoebe\interfaces\forms\IPhoebeFormObject;
use neon\core\helpers\Hash;

/**
 * Provides the applicationForm phoebe object
 */
class PhoebeObject extends PhoebeObjectBase
implements IPhoebeFormObject
{
	/**
	 * The set of dataSource Ids as 'key'=>'uuid'
	 * @var array
	 */
	protected $dataSourceIds = [];

	/**
	 * The set of data source objects
	 * @var array
	 */
	protected $dataSourceObjects = [];

	/**
	 * The set of new objects in the data
	 * @var array
	 */
	private $newObjects = [];

	/**
	 * @inheritdoc
	 */
	public function editObject($changes)
	{
		// check if there's anything to do
		if (empty($changes) || $changes == $this->data)
			return true;

		// extract the data and save appropriately to
		// the associated database tables ... if any
		$this->newObjects = [];
		$this->setObjectUuids($changes);
		$this->saveToDaedalus($changes);
		// now do the basic Phoebe saving
		return parent::editObject($changes);
	}

	/**
	 * @inheritdoc
	 */
	public function setDataSources($sources)
	{
		foreach ($sources as $key=>$value) {
			if (empty($value))
				unset($sources[$key]);
		}
		$this->dataSourceIds = $sources;
	}

	/**
	 * @inheritdoc
	 */
	public function updateFromDds()
	{
		if ($this->isVolatile()) {
			$data = $this->data;
			$this->updateDataFromDds($data);
			$this->data = $data;
		}
	}


	/**
	 * @inheritdoc
	 */
	public function initialiseFromDds($dataObjects)
	{
		if (!$this->data && $this->isVolatile()) {
			// create some stubs, update from Dds, and then create the data hiearchy
			$ddsObjects = $this->createDdsObjectStubs($dataObjects);
			$this->updateDataFromDds($ddsObjects);
			$definition = $this->getDefinition();
			$this->data = $this->createRenderablesFromDdsData($definition['rootNode'], $ddsObjects);
		}
	}

	/**
	 * Get hold of any data source objects
	 */
	protected function getDataSources()
	{
		foreach ($this->dataSourceIds as $key=>$id)
			$this->dataSourceObjects[$key] = $this->getDds()->getObject($id);
	}

	/**
	 * Update all of the data from the database.
	 *
	 * @param type $objects
	 */
	protected function updateDataFromDds(&$objects, $isTree=true)
	{
		// It may be better to flatten the data and make a more intelligent
		// set of selects from the database, or use the idea of requests and
		// do this in two passes
		$dds = neon('dds')->getIDdsObjectManagement();
		foreach($objects as &$object) {
			if (!empty($object['_uuid'])) {
				// update the object definition to pick up any new fields from the definition
				$object = array_merge($this->getAllowedFieldsForClass($object['_classType']), $object);
				// now update the object with the data from daedalus
				$data = $dds->getObject($object['_uuid']);
				if ($data)
					$object = array_replace($object, array_intersect_key($data, $object));
			}
			// recursively update if this is in a tree structure
			if ($isTree && is_array($object)) {
				$this->updateDataFromDds($object, true);
			}
		}
	}

	/**
	 * Set uuids on any new objects.
	 * @param array $objects
	 */
	protected function setObjectUuids(&$objects)
	{
		$renderables = $this->getRenderables();
		$renderableKeys = array_keys($renderables);
		foreach ($objects as &$object) {
			if (!empty($object['_renderable'])) {
				if ($this->objectCanBeSaved($object)) {
					$isNew = empty($object['_uuid']);
					if ($isNew) {
						$object['_uuid'] = Hash::uuid64();
						$this->newObjects[] = $object['_uuid'];
					}
					// find any subRenderables in the definition
					$subRenderables = array_intersect($renderableKeys, array_keys($object));
					foreach ($subRenderables as $subRenderable) {
						$this->setObjectUuids($object[$subRenderable]);
					}
				}
			}
			// and if not go down the next depth
			else if (is_array($object)) {
				$this->setObjectUuids($object);
			}
		}
	}

	/**
	 * Save the changes to Daedalus. The data will be altered during
	 * this call as required to match data with Daedalus
	 * @param array $data
	 */
	protected function saveToDaedalus($data)
	{
		$objectsByClassType = $this->extractObjects($data);
		$relations = !empty($objectsByClassType['__relations']) ? $objectsByClassType['__relations'] : [];
		unset($objectsByClassType['__relations']);
		$dds = $this->getDds();
		foreach ($objectsByClassType as $classType => $objects) {
			if (!empty($objects['new'])) {
				$dds->addObjects($classType, $objects['new']);
			}
			if (!empty($objects['existing'])) {
				foreach ($objects['existing'] as $object) {
					$dds->editObject($object['_uuid'], $object);
				}
			}
		}
		if (count($relations))
			$this->saveLinkedObjects($relations);
	}

	/**
	 * Go through all of the data and collate together the objects
	 * by the class id involved. The objects will have their uuid's set
	 * if these were not already set.
	 *
	 * @param array &$objects
	 * @return array
	 */
	protected function extractObjects(&$objects, &$extractedObjectIds=null)
	{
		//
		// TODO - this code is particularly messy - refactor
		//
		$extracted = [];
		$extractedObjectIds = [];
		$renderables = $this->getRenderables();
		$renderableKeys = array_keys($renderables);
		$this->getDataSources();
		foreach ($objects as $key => &$object) {
			// see if this is a class data object
			if (!empty($object['_renderable'])) {
				if ($this->objectCanBeSaved($object)) {
					$this->setObjectDataFromDataSource($object);
					$objId = $object['_uuid'];
					// find any subRenderables in the definition
					$subRenderables = array_intersect($renderableKeys, array_keys($object));
					foreach ($subRenderables as $subRenderable) {
						$newObjects = $this->extractObjects($object[$subRenderable], $newObjectIds);
						// and work out what their relationship is to their parent
						if (!empty($renderables[$subRenderable]['relations'])) {
							$relations = $renderables[$subRenderable]['relations'];
							foreach ($relations as $relation) {
								$memberRef = $relation['memberRef'];
								if (!isset($extracted['__relations'][$objId][$memberRef]))
									$extracted['__relations'][$objId][$memberRef] = [];
								$extracted['__relations'][$objId][$memberRef] = array_merge($extracted['__relations'][$objId][$memberRef], $newObjectIds);
							}
						}
						unset($object[$subRenderable]);
						$extracted = array_merge_recursive(
							$extracted,
							$newObjects
						);

					}
					$objectClassType = $object['_classType'];
					unset($object['_renderable'], $object['_classType']);
					$isNew = in_array($object['_uuid'], $this->newObjects) ? 'new' : 'existing';
					$extracted[$objectClassType][$isNew][] = $object;
					$extractedObjectIds[] = $objId;
				} else {
					unset($objects[$key]);
				}
			}
			// and if not go down the next depth
			else if (is_array($object)) {
				$extracted = array_merge_recursive($extracted, $this->extractObjects($object));
			}
		}
		return $extracted;
	}

	/**
	 * Check what fields are allowed to be set in a class from the
	 * current definition. Objects can get out of step with form definitions
	 * so this helps to keep them up to date.
	 *
	 * @param string $classType  the type of class you want to check again
	 * @return array
	 */
	protected function getAllowedFieldsForClass($classType)
	{
		static $fieldsForClass = [];
		if (empty($fieldsForClass[$classType])) {
			$definition = $this->getDefinition();
			foreach ($definition['renderables'] as $r) {
				if ($r['type'] == 'MemberComponent')
					$fieldsForClass[$r['class_type']][$r['member_ref']] = null;
			}
		}
		return $fieldsForClass[$classType];
	}


	/**
	 * Save objects to any link members that they need to be saved to
	 */
	private function saveLinkedObjects($relations)
	{
		$dds = $this->getDds();
		foreach ($relations as $parentUuid => $members) {
			$parent = $dds->getObject($parentUuid);
			if ($parent) {
				$changes = [];
				foreach ($members as $memberRef => $objectIds) {
					$changes[$memberRef] = array_unique(array_merge($parent[$memberRef], $objectIds));
				}
				$dds->editObject($parentUuid, $changes);
			}
		}
	}

	/**
	 * Convert a set of Daedalus objects into the correct structure for the
	 * form to import them.
	 *
	 * @param type $ddsObjects
	 */
	private function createRenderablesFromDdsData($renderableKey, $ddsObjects)
	{
		$data = [];
		$renderables = $this->getRenderables();
		$renderable = $renderables[$renderableKey];
		if (isset($renderable['items'])) {
			foreach ($renderable['items'] as $k) {
				if (isset($ddsObjects[$k])) {
					$data[$k] = array_merge($ddsObjects[$k], $this->createRenderablesFromDdsData($k, $ddsObjects));
				}
			}
		}
		return $data;
	}

	/**
	 * Link the data objects from Daedalus into the object data
	 * - the actual data is pulled in later, but here we create a blank
	 * version of the data
	 *
	 * @param array $dataObjects  set of phoebeKey=>ddsUuid's
	 * @return array  created blank objects as renderableKey=>array
	 */
	private function createDdsObjectStubs($dataObjects) {
		$definition = $this->getDefinition();
		$renderables = $definition['renderables'];
		$ddsObjects = [];
		foreach ($dataObjects as $phoebeKey=>$objectUuid) {
			if (isset($renderables[$phoebeKey])) {
				// check the renderable exists and is a class component
				$ren = $renderables[$phoebeKey];
				if ($ren['type'] != 'ClassComponent')
					continue;

				// create the object and any required data params
				$object = [
					"_renderable"=>$phoebeKey,
					"_classType" => $ren['class_type'],
					"_uuid" => $objectUuid
				];
				foreach ($ren['items'] as $item) {
					if (isset($renderables[$item])) {
						$iRen = $renderables[$item];
						if ($iRen['type'] != 'MemberComponent')
							continue;
						$object[$iRen['member_ref']] = null;
					}
				}

				// if it's a repeater put it as subarray
				if (isset($ren['definition']['isRepeater']) && $ren['definition']['isRepeater']) {
					$ddsObjects[$phoebeKey][Hash::uuid64()] = $object;
				} else {
					$ddsObjects[$phoebeKey] = $object;
				}
			}
		}
		return $ddsObjects;
	}

	/**
	 * Determine if an object should be saved. This is if it is new and has data or is a
	 * preexisting object regardless of data.
	 * @param array $object
	 * @return boolean
	 */
	private function objectCanBeSaved($object)
	{
		$uuid = !empty($object['_uuid']) ? $object['_uuid'] : null;
		//dp('The UUID is', $uuid);
		unset($object['_uuid'], $object['_classType'], $object['_renderable']);
		foreach ($object as $k => $v) {
			if (!empty($v)) {
				//dp('The object has got some data so should be saved', $v);
				return true;
			}
		}
		if (!$uuid) {
			//dp('the object had no uuid and no data so nothing to do');
			return false;
		}
		//dp($uuid, 'The object has no data (aah) but does have a uuid. Does it exist?');
		// in this case it has a uuid but does the object exist in the database
		$data = neon('dds')->IDdsObjectManagement->getObject($uuid);
		//if ($data) {
		//	dp($uuid, 'the object already exists therefore save over it');
		//}
		//dp('The object doesnt exist and has no data so dont save');
		return $data !== null;

	}

	/**
	 * Sets any object data that is provided from external data sources.
	 * @param array &$object  the object you want to set data on
	 */
	private function setObjectDataFromDataSource(&$object)
	{
		$objRend = $this->getRenderable($object['_renderable']);
		// see if we have any data to be stored
		if (!empty($objRend['dataSourceMap'])) {
			foreach ($objRend['dataSourceMap'] as $member => $dsMap) {
				$dsDef = $this->getDataSourceDefinition($dsMap['id']);
				if (($dsObj = $this->getDataSourceObject($dsDef['label']))!==null) {
					$object[$member] = $dsObj[$dsMap['memberRef']];
				}
			}
		}
	}

	/**
	 * Get a data source object from its key
	 * @param string $key  the data source object key
	 */
	private function getDataSourceObject($key)
	{
		return isset($this->dataSourceObjects[$key]) ? $this->dataSourceObjects[$key] : null;
	}

	/**
	 * Get the data source definition by its id
	 * @param string $id
	 * @return array|null  the data source definition if found or null otherwise
	 */
	private function getDataSourceDefinition($id)
	{
		$definition = $this->getDefinition();
		return (!empty($definition['dataSources'][$id]) ? $definition['dataSources'][$id] : null);
	}

	private $_definition = null;
	private function getDefinition()
	{
		if (!$this->_definition) {
			$class = $this->getClass();
			$this->_definition = $class->definition;
		}
		return $this->_definition;
	}

	private function getFormDefinition()
	{
		return $this->getClass()->getClassFormDefinition();
	}

	private $_renderables = null;
	private function getRenderables()
	{
		if (!$this->_renderables) {
			$definition = $this->getDefinition();
			$this->_renderables = $definition['renderables'];
		}
		return $this->_renderables;
	}

	private function getRenderable($key)
	{
		$renderables = $this->getRenderables();
		return $renderables[$key];
	}

	private $_class = null;
	private function getClass()
	{
		if (!$this->_class)
			$this->_class = neon('phoebe')->getIPhoebeType($this->phoebeType)->getClass($this->classType);
		return $this->_class;
	}

	private function isVolatile()
	{
		return $this->getClass()->volatile;
	}


	private $_dds = null;
	private function getDds()
	{
		if (!$this->_dds)
			$this->_dds = neon('dds')->getIDdsObjectManagement();
		return $this->_dds;
	}

}