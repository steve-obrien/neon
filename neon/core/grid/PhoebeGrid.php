<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 23/08/2017
 * Time: 12:23
 */

namespace neon\core\grid;

use neon\core\form\Form;
use neon\core\form\interfaces\IField;
use \neon\core\grid\GridBase;
use \neon\core\grid\PhoebeDataProvider;
use neon\core\grid\query\DdsQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\dev\widgets\NeonFiddle;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use neon\dev\debug\HtmlDumper;
use neon\dev\debug\NeonCaster;

class PhoebeGrid extends GridBase
{
	/**
	 * The phoebe type for the grid e.g. 'applicationForm'. This is
	 * required on construction via config.
	 * @var string
	 */
	public $phoebeType;

	/**
	 * The class type within the phoebe type. This is required on construction
	 * via the config.
	 * @var string
	 */
	public $classType;

	/**
	 * Additional columns that you may want to show on the grid
	 * e.g. 'data' and 'deleted'. Set during construction via config.
	 * @var array
	 */
	public $additionalColumns=[];

	/**
	 * Default page size
	 * @var integer
	 */
	public $defaultPageSize = 50;

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
				'phoebeType', 'classType', 'additionalColumns'
			],
			parent::getProperties()
		);
	}


	public function __construct($config=[])
	{
		if (empty($config['phoebeType']) || empty($config['classType'])) {
			throw new \RuntimeException('You need to provide at least the phoebeType and classType on construction');
		}
		parent::__construct($config);
	}

	public function init()
	{
		parent::init();
		$this->phoebeLoadGrid();
	}

	/**
	 * Load the phoebe grid
	 * For this to be successful, the classType and phoebeType should already be set
	 *
	 * @return $this
	 */
	protected function phoebeLoadGrid()
	{
		$this->title = $this->classType;
		$includeDeleted = in_array('deleted', $this->additionalColumns);
		$definition = neon('phoebe')->getGridDefinition($this->phoebeType, $this->classType, $this->additionalColumns);
		$page = isset($_POST[$this->id]['page']) ? (int) Html::sanitise($_POST[$this->id]['page']) : null;
		$queryBuilder = new DdsQuery();

		// set the data provider to be a Phoebe one!
		$dataProvider = new PhoebeDataProvider([
			'phoebeType' => $this->phoebeType,
			'classType' => $this->classType,
			'formDefinition' => $definition,
			'queryBuilder' => $queryBuilder,
			'additionalColumns' => $this->additionalColumns,
			'pagination' => [
				// we move through pages using ajax and it sends a special page param prefixed with this grid id
				'page' => $page,
				'defaultPageSize' => $this->defaultPageSize,
			]
		]);
		$this->setDataProvider($dataProvider);
		$this->setColumns($definition);

		$this->addButtonColumn('edit')->setTitle(false)
			->setEdit(['/phoebe/appforms/index/edit-object', 'type' => $this->classType, 'id' => '{{uuid}}'])
			->setDelete(['/phoebe/appforms/index/delete-object', 'type' => $this->classType, 'id' => '{{uuid}}']);
		return $this;
	}

	/**
	 * Get the data provider object
	 * @return DdsDataProvider
	 */
	public function getDataProvider()
	{
		if (!$this->_dataProvider)
			throw new \RuntimeException('You need to call phoebeLoadGrid before the grid can be used');
		return $this->_dataProvider;
	}


}

