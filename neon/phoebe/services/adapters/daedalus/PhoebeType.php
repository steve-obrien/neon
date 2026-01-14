<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\daedalus;

use neon\phoebe\services\adapters\common\PhoebeTypeBase;
use neon\phoebe\interfaces\daedalus\IPhoebeDaedalusType;


/**
 * Daedalus adaptor for the phoebeType class
 *
 * Notes on Data Integrity:
 *
 * Table structure: Daedalus has ceded control of the form definitions relating
 * to the user interface forms to Phoebe.
 *
 * The situation for objects is different to that of classes. Objects can be expected
 * to be updated through many different processes including via other more complex
 * forms, results of user actions, cron jobs etc etc. This means if Phoebe was to
 * ever duplicate any of this data, it will become out of sync and maintenance
 * would a pointless exercise. For this reason, Phoebe needs to defer to Daedalus
 * for the instance data and not store it locally.
 *
 * However, forms need to use Phoebe to draw themselves so Phoebe will need to
 * behave as though it has objects within its database even though it isn't
 * storing them.
 *
 * In summary, the Daedalus adapter for Phoebe will store the form definition
 * information for the Daedalus tables, and will pretend to store the data for
 * individual table rows by creating phoebe objects on the fly from Daedalus data.
 *
 * On a note regarding creation of migration files - since these are created on
 * the fly when required, there shouldn't be a need to create any separate
 * migration files.
 *
 */
class PhoebeType extends PhoebeTypeBase
implements
	IPhoebeDaedalusType
{
	/**
	 * Set this up as a daedalus type
	 * @param array $config
	 */
	public function __construct($config=[])
	{
		$path = '\neon\phoebe\services\adapters\daedalus';
		$config['phoebeType'] = 'daedalus';
		$config['phoebeClass'] = $path.'\PhoebeClass';
		$config['phoebeObject'] = $path.'\PhoebeObject';

		parent::__construct($config);
	}

	/** -------------------------------------- **/
	/** -------- IPhoebeDaedalusType --------- **/

	/**
	 * @inheritdoc
	 */
	public function saveClassDefinition($class, $definition, $module)
	{
		$class = $this->canonicaliseRef($class);

		// descriptions of a class are stored as hints in the form
		if (isset($definition['description']))
			$definition['hint'] = $definition['description'];

		// pass the definition to a form object and then get back the full definition
		$definition['name'] = $class;
		$form = new \neon\core\form\Form($definition);
		$classDefinition = $form->exportDefinition();

		// start a migration for the changes
		$this->startMigration('table_'.$class.'_edit');

		// save the class information
		$phoebeClass = $this->getClass($class);
		if (!$phoebeClass)
			$phoebeClass = $this->addClass($class, $module);
		$phoebeClass->editClass($classDefinition);

		// close the migration
		$this->endMigration();

		return $phoebeClass;
	}


	/** -------------------------------------- **/
	/** ------------ IPhoebeClass ------------ **/

	/** ------------ Class Methods ----------- **/

	public function listClasses(&$paginator=[], $filters=[], $orderBy=[], $includeDeleted=false)
	{
		$classes = parent::listClasses($paginator, $filters, $orderBy, $includeDeleted);
		$this->mergeDaedalusFields($classes);
		return $classes;
	}

	/**
	 * @inheritdoc
	 */
	public function addClass(&$classType, $module='')
	{
		$classType = $this->canonicaliseRef($classType);
		$class = parent::addClass($classType, $module);

		// set daedalus defaults for phoebe
		$class->editClassParent([
			'version' => 1,
			'volatile' => true,
			'version_locked' => false,
			'object_history' => false
		]);

		$this->addDdsClass($classType, $module);
		return $class;
	}

	/** -------------------------------------- **/
	/** ---------- Instance Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listObjects($classType, &$paginator=[], $filters=[], $orderBy=[], $additionalFields=[])
	{
		$filters = null;
		// get our data directly from daedalus - objects are not stored in phoebe
		$paginator = $this->createPaginator($paginator);
		$inFull = false;
		$includeDeleted = in_array('deleted', $additionalFields);
		return $this->ddo()->listObjects($classType, $paginator['total'], $includeDeleted, $inFull, $paginator['start'], $paginator['length'], $orderBy);
	}

	/**
	 * @inheritdoc
	 */
	public function createStubObject($classType)
	{
		// Daedalus does not implement this method
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function addObject($classType, $overrideCriteria=[])
	{
		// Daedalus doesn't manage overrides currently

		// create an object in daedalus and connect it with a phoebe object
		$changeLogUuid = null;
		$objectId = $this->ddo()->addObject($classType, [], $changeLogUuid);
		$ddsObject = $this->ddo()->getObject($objectId);

		$object = $objectId ? $this->createFauxIPhoebeObject($classType, $objectId, $ddsObject) : null;
		if ($object)
			$object->changeLogUuid = $changeLogUuid;
		return $object;
	}

	/**
	 * @inheritdoc
	 */
	public function getObject($objectId)
	{
		// get an object from Daedalus and connect it with a phoebe object
		$ddsObject = $this->ddo()->getObject($objectId);
		return $ddsObject ? $this->createFauxIPhoebeObject($ddsObject['_class_type'], $objectId, $ddsObject) : null;
	}


	/** -------------------------------------------- **/
	/** ---------- Implementation Methods ---------- **/

	/**
	 * Create a faux IPhoebeObject class. Faux because this object does not
	 * exist within the phoebe_object table
	 *
	 * @param string $classType
	 * @param string $ddsObjectId
	 * @param array $data
	 * @return neon\phoebe\interfaces\IPhoebeObject
	 */
	public function createFauxIPhoebeObject($classType, $ddsObjectId, $data)
	{
		$pom = $this->createPhoebeObjectModel($classType, $ddsObjectId);
		$pom->data = $data;
		return $this->createIPhoebeObject($pom);
	}

	/**
	 * Get hold of the counts in Daedalus and add them to the classes
	 * @param array &$classes
	 */
	protected function mergeDaedalusFields(&$classes)
	{
		if (count($classes)) {
			$classTypes = [];
			foreach ($classes as $class) {
				$ct = $class['class_type'];
				$classes[$ct]['count_total'] = 0;
				$classes[$ct]['count_deleted'] = 0;
				$classes[$ct]['count_current'] = 0;
				$classTypes[] = $ct;
			}
			$query = "SELECT * FROM dds_class WHERE `class_type` IN ('".implode("','",$classTypes)."')";
			$counts = neon()->db->createCommand($query)->queryAll();
			foreach ($counts as $c) {
				$classes[$c['class_type']] = array_merge($classes[$c['class_type']], $c);
				$classes[$c['class_type']]['count_current'] = ($c['count_total'] - $c['count_deleted']);
			}
		}
	}

	protected $_dds = null;
	protected function dds()
	{
		if ($this->_dds == null)
			$this->_dds = neon('dds')->IDdsClassManagement;
		return $this->_dds;
	}

	protected $_ddo = null;
	protected function ddo()
	{
		if ($this->_ddo == null)
			$this->_ddo = neon('dds')->IDdsObjectManagement;
		return $this->_ddo;
	}

	protected function getDdsClass($classType)
	{
		return $this->dds()->getClass($classType);
	}

	protected function addDdsClass($classType, $module)
	{
		return $this->dds()->addClass($classType, $module);
	}

	protected function listDdsMembers($classType)
	{
		return $this->dds()->listMembers($classType, true);
	}

	/**
	 * Create a migration with the provided label
	 * @param type $label
	 */
	private function startMigration($label)
	{
		neon()->dds->getIDdsAppMigrator('phoebe')->openMigrationFile($label);
	}

	/**
	 * close the currently open migration
	 */
	private function endMigration()
	{
		neon()->dds->getIDdsAppMigrator('phoebe')->closeMigrationFile();
	}


}