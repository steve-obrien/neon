<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 23/08/2017
 * Time: 12:40
 */

namespace neon\core\grid;

use \yii\data\BaseDataProvider;

class PhoebeDataProvider extends BaseDataProvider
{
	/**
	 * The phoebe type this data provider should represent
	 * @var string
	 */
	public $phoebeType;

	/**
	 * The class type this data provider should represent
	 * @var string
	 */
	public $classType;

	/**
	 * @var DdsQuery
	 */
	public $queryBuilder;

	/**
	 * @var array  any additional columns to include
	 */
	public $additionalColumns = [];

	/**
	 * Set the internal form definition for the data provider
	 * @var array
	 */
	public $formDefinition = null;

	public function init()
	{
		if (empty($this->phoebeType))
			throw new \RuntimeException('PhoebeDataProvider: the phoebe type has not been set. It needs to be set on construction');
		if (empty($this->classType))
			throw new \RuntimeException('PhoebeDataProvider: the phoebe class type has not been set. It needs to be set on construction');
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareModels()
	{
		// prepare pagination
		if (($pagination = $this->getPagination()) !== false) {
			$pagination->totalCount = $this->getTotalCount();
			if ($pagination->totalCount === 0) {
				return [];
			}
		}

		$paginator =  [
			'start' => $pagination->getOffset(),
			'length' => $pagination->getLimit(),
			'total' => $pagination->totalCount
		];

		// get all result rows
		$results = $this->getResults($paginator);
		$this->getPagination()->totalCount = $paginator['total'];

		// load each row through the form to get its configuration
		$form = $this->getForm();

		$data = [];
		foreach($results as $object) {
			$form->reset();
			$form->loadFromDb($object);
			$data[] = array_merge($object, $form->getDataDisplay());
		}
		return $data;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareKeys($models)
	{
		// TODO: Implement prepareKeys() method.
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareTotalCount()
	{
		// remove the need for this query!!
		$results = $this->getResults($paginator);
		return $paginator['total'];
	}

	protected function getResults(&$paginator = null)
	{
		// get all result rows
		$pagination = $paginator ? $paginator : ['total'=>true];
		$filters = $this->queryBuilder->getFilter();
		$orderBy = $this->queryBuilder->getOrder();
		return $this->phoebe()->listObjects($this->classType, $pagination, $filters, $orderBy, $this->additionalColumns);
	}

	/**
	 * Get hold of the appropriate IPhoebeType object
	 * @return \neon\phoebe\interfaces\IPhoebeType
	 */
	protected function phoebe()
	{
		if (!$this->_phoebe) {
			$this->_phoebe = neon('phoebe')->getService()->getPhoebeType($this->phoebeType);
		}
		return $this->_phoebe;
	}
	private $_phoebe = null;

	/**
	 * Create a form linked to the appropriate table
	 * @return \neon\core\form\Form
	 */
	protected function getForm()
	{
		if ($this->_form === null) {
			$this->_form = new \neon\core\form\Form($this->getFormDefinition());
		}
		return $this->_form;
	}
	private $_form = null;

	/**
	 * Get hold of the appropriate definition for a form object
	 * @return array
	 */
	protected function getFormDefinition()
	{
		if ($this->formDefinition == null)
			$this->formDefinition = neon('phoebe')->getFormDefinition($this->phoebeType, $this->classType);
		return $this->formDefinition;
	}

	/**
	 * Get hold of the query builder
	 * @return DdsQuery
	 */
	public function getQueryBuilder()
	{
		return $this->queryBuilder;
	}


}