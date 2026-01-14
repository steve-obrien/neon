<?php
namespace neon\core\grid;

use neon\core\form\interfaces\IField;
use neon\core\grid\column\Column;
use \neon\core\grid\GridBase;
use \neon\core\grid\DdsDataProvider;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\core\interfaces\IProperties;
use neon\core\traits\PropertiesTrait;

class DdsGrid extends GridBase
{
	/**
	 * Url to delete a grid item. Set this to null to remove a delete button,
	 * to a string to use the default parameters or an array of the form
	 * [url, 'param1'=>value]
	 * The additional parameters 'type' and 'id' will be added automatically
	 * for the classType and uuid of the row object
	 * @var string|arrary
	 */
	public $gridDeleteUrl = '/daedalus/index/delete-object';

	/**
	 * Url to edit a grid item. Set this to null to remove an edit button,
	 * to a string to use the default parameters or an array of the form
	 * [url, 'param1'=>value]
	 * The additional parameters 'type' and 'id' will be added automatically
	 * for the classType and uuid of the row object
	 * @var string|array
	 */
	public $gridEditUrl = '/daedalus/index/edit-object';

	/**
	 * Url to view a grid item. Set this to null to remove a view button,
	 * to a string to use the default parameters or an array of the form
	 * [url, 'param1'=>value]
	 * The additional parameters 'type' and 'id' will be added automatically
	 * for the classType and uuid of the row object
	 * @var string|array
	 */
	public $gridViewUrl = '/daedalus/index/view-object';

	/**
	 * Url to a grids change log. Set this to null to remove a change log button,
	 * to a string to use the default parameters or an array of the form
	 * [url, 'param1'=>value]. It will only show if there is a change log for the class
	 * The additional parameters 'type' and 'id' will be added automatically
	 * for the classType and uuid of the row object
	 * @var string|array
	 */
	public $gridLogUrl = '/daedalus/change-log/list';

	/**
	 * Set whether or not the _created column is shown
	 * Set to true or an array of configuration parameters to show
	 * @var bool
	 */
	public $showCreatedColumn = false;

	/**
	 * Default config for the '_created' column
	 * @var array
	 */
	public $createdColumnConfig = [
		'filter' => false
	];

	/**
	 * Set whether or not the _updated column is shown
	 * Set to true or an array of configuration parameters to show
	 * @var bool | array
	 */
	public $showUpdatedColumn = false;

	/**
	 * Default config for the '_updated' column
	 * @var array
	 */
	public $updatedColumnConfig = [
		'filter' => false
	];

	/**
	 * Set whether or not to show the _uuid column
	 * Set to true or an array of configuration parameters to show
	 * @var bool | array
	 */
	public $showUuidColumn = false;

	/**
	 * Default config for the '_uuid' column
	 * @var array
	 */
	public $uuidColumnConfig = [
		'width' => '10rem'
	];

	/**
	 * @var bool - whether to show the undelete button
	 */
	public $showGridUndeleteButton = true;

	/**
	 * @var bool - whether to show the destroy button
	 */
	public $showGridDestroyButton = true;

	/**
	 * @var bool - whether or not to use the active scope.
	 * This shows the set of active (not deleted) items in a scope
	 */
	public $useActiveScope = false;

	/**
	 * @var bool - whether or not to use the deleted scope.
	 * This shows the set of deleted items in a scope so they can be restored
	 */
	public $useDeletedScope = false;

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		/**
		 * Return all the properties that are needed for generic rendering
		 * or exporting of this grid. Include parent properties.
		 */
		return array_merge(
			[
				'classType', 'gridDeleteUrl', 'gridEditUrl', 'showCreatedColumn',
				'createdColumnConfig', 'showUpdatedColumn', 'updatedColumnConfig',
				'showUuidColumn', 'uuidColumnConfig', 'showGridUndeleteButton',
				'showGridDestroyButton', 'useActiveScope', 'useDeletedScope'
			],
			parent::getProperties()
		);
	}

	/**
	 * Set the DDS class type
	 * @param string $value
	 */
	public function setClassType($value)
	{
		$this->_classType = $value;
		// Set a default title
		if ($this->title === null)
			$this->title = $this->_classType;
	}

	/**
	 * Return the dds class type string
	 * @return string
	 */
	public function getClassType()
	{
		return $this->_classType;
	}

	/**
	 * Get the grids Data provider object
	 * @return DdsDataProvider
	 */
	public function getDataProvider()
	{
		if (!$this->_dataProvider) {
			$this->_dataProvider = new DdsDataProvider([
				'classType' => $this->getClassType(),
			]);
		}
		return $this->_dataProvider;
	}

	/**
	 * The DDS class type
	 * @var string
	 */
	protected $_classType;

	/**
	 * Configure default columns based on the members in the dds class type
	 * If you like to change which columns are added or remove or add additional ones then override this method
	 * or implement the setup method.
	 */
	public function init()
	{
		parent::init();
		// don't configure the grid if the class type has not been set!
		if ($this->_classType === null) {
			if (neon()->debug)
				throw new \RuntimeException('You need to configure the grid class type before calling init');
			return null;
		}

		// $this->addCheckColumn('_chk');
		$this->addColumnsFromClassMembers();
		$this->addDdsObjectColumns();
		
		if ($this->useActiveScope)
			$this->addScope('active', 'Active');
		if ($this->useDeletedScope)
			$this->addScope('deleted', 'Deleted');

	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		$this->addActionButtonColumn();
	}

	/**
	 * @param IQuery $query
	 */
	public function scopeActive(IQuery $query)
	{
		$query->where('_deleted', '=', 0);
	}

	/**
	 * @param IQuery $query
	 */
	public function scopeDeleted(IQuery $query)
	{
		$query->where('_deleted', '=', 1);
	}

	/**
	 * Add the edit and delete DDS object buttons to the grid
	 */
	public function addActionButtonColumn($title=false)
	{
		$buttonCol = $this->addButtonColumn('edit')->setTitle($title);

		// add a view button
		if ($this->gridViewUrl) {
			$viewUrl = is_string($this->gridViewUrl) ? [$this->gridViewUrl] : $this->gridViewUrl;
			$viewUrl['type'] = $this->getClassType();
			$viewUrl['id'] = '{{_uuid}}';
			$buttonCol->addButton('view', $viewUrl, '', 'fa fa-eye', 'btn-default', ['data-toggle' => 'tooltip', 'title'=>'View']);
		}

		if ($this->hasScope('deleted') && $this->isScopeActive('deleted')) {
			if ($this->showGridUndeleteButton) {
				$buttonCol->addButton('undelete', '','', 'fa fa-undo', 'btn-default', [
					'data-toggle' => 'tooltip',
					'title' => 'Restore',
					'data-action' => 'actionUndelete'
				]);
			}
			if ($this->showGridDestroyButton) {
				$buttonCol->addButton('destroy', '', '', 'fa fa-bolt', 'btn-danger', [
					'data-toggle' => 'tooltip',
					'title' => 'Destroy Permanently',
					'data-action' => 'actionDestroy',
				]);
			}
		}

		if (!$this->hasScope('deleted') || !$this->isScopeActive('deleted')) {
			if ($this->gridEditUrl) {
				// add an edit and view
				$editUrl = is_string($this->gridEditUrl) ? [$this->gridEditUrl] : $this->gridEditUrl;
				$editUrl['type'] = $this->getClassType();
				$editUrl['id'] = '{{_uuid}}';
				$buttonCol->addButton('edit', $editUrl, '', '', '', [
					'data-toggle' => 'tooltip',
					'title'=>'Edit'
				]);
			}
			if ($this->gridDeleteUrl) {
				$deleteUrl = is_string($this->gridDeleteUrl) ? [$this->gridDeleteUrl] : $this->gridDeleteUrl;
				$deleteUrl['type'] = $this->getClassType();
				$deleteUrl['id'] = '{{_uuid}}';
				$deleteUrl['redirect'] = false;
				$buttonCol->addButton('delete', $deleteUrl, '', '', '', [
					'data-toggle' => 'tooltip',
					'title'=>'Delete',
					'data-delete-ajax' => 'true'
				]);
			}
		}
		if ($this->gridLogUrl) {
			if (neon('dds')->IDdsChangeLogManagement->hasChangeLog($this->getClassType())) {
				$logUrl = is_string($this->gridLogUrl) ? [$this->gridLogUrl] : $this->gridLogUrl;
				$logUrl['type'] = $this->getClassType();
				$logUrl['uuid'] = '{{_uuid}}';
				$buttonCol->addButton('Logs', $logUrl, '', 'fa fa-history', '', ['data-toggle' => 'tooltip', 'title'=>'View Change Log']);
			}
		}

	}

	/**
	 * Perform a permanent destroy
	 * @param $id
	 */
	public function actionDestroy($id)
	{
		// the permissions should be configured when adding a button,
		// the grid can then recheck these before running this action
		if (neon()->user->hasRole('neon-administrator')) {
			$object = neon('phoebe')->getService()->getPhoebeType('daedalus')->getObject($id);
			$object->destroyObject();
		}
	}

	/**
	 * Perform an undelete, restore of an object
	 * @param $id
	 */
	public function actionUndelete($id)
	{
		// the permissions should be configured when adding a button,
		// the grid can then recheck these before running this action
		if (neon()->user->hasRole('neon-administrator')) {
			$object = neon('phoebe')->getService()->getPhoebeType('daedalus')->getObject($id);
			$object->undeleteObject();
		}
	}

	/**
	 * Adds the columns to the grid based on the members in the dds class type
	 */
	public function addColumnsFromClassMembers()
	{
		$formDefinition = neon()->phoebe->getFormDefinition('daedalus', $this->getClassType());
		$memberForm = new \neon\core\form\Form($formDefinition);
		foreach ($memberForm->getFields() as $member) {
			$this->addDdsColumn($member);
		}
	}

	/**
	 * Add Dds Object Columns. This will add the _created, _updated and _uuid fields if the appropriate
	 * showCreatedColumn etc is set.
	 * @throws \Exception
	 */
	public function addDdsObjectColumns()
	{
		if ($this->showCreatedColumn) {
			$this->addDdsObjectColumn('_created', '\neon\core\form\fields\DateTime', $this->createdColumnConfig);
		}
		if ($this->showUpdatedColumn) {
			$this->addDdsObjectColumn('_updated', '\neon\core\form\fields\DateTime', $this->updatedColumnConfig);
		}
		$this->addDdsObjectColumn('_uuid', '\neon\core\form\fields\Uuid64', $this->uuidColumnConfig)
			->setDbField('_uuid')
			->setAsIndex()->setVisible($this->showUuidColumn);
		return $this;
	}

	/**
	 * @param $member
	 * @param $columnType
	 * @param $config
	 * @throws \Exception
	 * @return Column
	 */
	public function addDdsObjectColumn($member, $columnType, $config)
	{
		$colConfig = [
			'class' => 'neon\core\grid\column\DefinitionColumn',
			'member' => new $columnType($member),
		];
		if (is_array($config))
			$colConfig = array_merge($colConfig, $config);
		return $this->addColumn($member, $colConfig);
	}

	/**
	 * Load appropriate columns based on DDS details
	 * @deprecated - just set the classType - either in the init or the config
	 */
	public function ddsLoadGrid($classType, $includeDeleted=false, $pageSize=50)
	{
		if ($this->title == null)
			$this->title = $classType;
		// TODO: should be in constructor can't create a DDS grid without the DDS class ref
		$this->classType = $classType;

		// set the data provider to be a DDS one!
		$dataProvider = new DdsDataProvider([
			'classType' => $classType,
		]);
		$this->setDataProvider($dataProvider);

		$formDefinition = neon()->phoebe->getFormDefinition('daedalus', $this->classType);
		$memberForm = new \neon\core\form\Form($formDefinition);
		foreach ($memberForm->getFields() as $member) {
			$this->addDdsColumn($member);
		}
		$this->addActionButtonColumn();
		return $this;
	}

	/**
	 * Add a column with the correct config
	 * @param IField $member
	 * @return \neon\core\grid\column\Column
	 * @throws \Exception
	 */
	public function addDdsColumn(IField $member)
	{
		// get grid column class for member
		return $this->addColumn($member->getDataKey(), [
			'member' => $member,
			'class' => 'neon\core\grid\column\Column',
			'title' => $member->getLabel()
		]);
	}

	public function showQuery()
	{
		return  implode("; \n", neon()->getDds()->IDdsObjectManagement->sql);
	}
}

