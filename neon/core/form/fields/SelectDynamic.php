<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use neon\core\grid\query\IQuery;
use neon\core\helpers\Hash;
use neon\core\helpers\Html;
use \neon\core\helpers\Arr;
use neon\core\helpers\Str;
use neon\core\interfaces\IDataMapProvider;
use yii\debug\models\timeline\DataProvider;

/**
 * Class SelectDynamic - This select box will look up a list of items to populate itself.
 * It relies upon the `dataMapProvider` and `dataMapKey` properties to make a request that will return its items
 *
 * The items should be of form 'uuid64' as key and whatever value
 *
 * Essentially it off loads the responsibility of finding its items to the object that is returned from
 * the `dataMapProvider`.
 *
 * *NOTE* The data map provider should be accessible from `neon()->{$provider}`
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\form\fields
 */
class SelectDynamic extends Choice
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'link_uni';

	/**
	 * Typically an app name such as 'dds'
	 * The provider should be accessible from `neon()->app($provider)`
	 * The provider object returned from `neon()` should implement the `IFormBuilder` interface
	 *
	 * This can then be called like:
	 *
	 * ```php
	 * $items = neon()->app($this->$dataMapProvider)->getFormBuilderMap($this->dataMapKey, $this->dataMapFilters))
	 * ```
	 *
	 * @var string
	 */
	public $dataMapProvider;

	/**
	 * The specific key that you would like to return the items for.
	 * The $this->$dataMapProvider object should understand the key.
	 * @var string
	 */
	public $dataMapKey;

	/**
	 * Any filters you want to apply to a data map selection.
	 * @var array  field=>value
	 */
	public $dataMapFilters=[];

	/**
	 * Any fields you want to return in a data map selection if not the default.
	 * If you add an additional format=>[] array, the fields will be formatted
	 * accordingly
	 * @var array  [n]=>fields and 'format'=>['concat', 'prefix', 'postfix']
	 *   where if concat is set, the results will be concatenated with this
	 *   string (otherwise with a space) and prefix postfix arguments will be
	 *   applied to it.
	 */
	public $dataMapFields=[];

	/**
	 * an explicit limit attribute allows it to actually be used without the dumb __set implementation silently
	 * discarding it
	 * the arbitrary 100 default is to mimic the existing (questionable) behaviour
	 */
	public $limit = 100;

	/**
	 * @inheritdoc
	 */
	public function setItems($value)
	{
		//throw new \Exception('Items are dynamically loaded in a Dynamic Select box - and can not therefore be set');
	}

	/**
	 * A key for a reverse map request
	 * @var string
	 */
	private $_mapLookupRequestKey = null;

	/**
	 * @inheritdoc
	 */
	public function getItems()
	{
		// if we haven't or can't map a map request we can't have any values
		if (empty($this->dataMapKey))
			return [];

		$provider = $this->getProviderService();
		$format = false;
		if ($this->dataMapFields) {
			$format = array_key_exists('format', $this->dataMapFields) ? $this->dataMapFields['format'] : null;
		}
		$mapFields = $this->dataMapFields;
		unset($mapFields['format']);

		// If a form's data has been set from the database this will have been set
		// but if a form has been set from a request and this is needed before saving
		// to the database we need to make the database request here.
		if ($this->_mapLookupRequestKey === null) {
			$this->makeMapRequest($this->getValue());
		}
		// Items are always pulled back from the database to ensure data integrity
		$items = $provider->getMapResults($this->_mapLookupRequestKey);
		if ($format) {
			$concat = isset($format['concat']) ? $format['concat'] : ' ';
			$prefix = isset($format['prefix']) ? $format['prefix'] : '';
			$postfix = isset($format['postfix']) ? $format['postfix'] : '';
			foreach ($items as $k=>$i)
				$items[$k] = $prefix.implode($concat, is_array($i) ? $i : [$i]).$postfix;
		}
		return $items;
	}

	/**
	 * Filter data map results based on a string filter search
	 * @param string $query  - the query string typed into a drop down box
	 * @param int $start - map offset
	 * @param int $length - the number of maps to return
	 * @return array
	 * @throws \Exception
	 */
	public function getMapObjects($query=null, $start=0, $length=100)
	{
		$provider = $this->getProviderService();
		$format = ['prefix'=>'', 'concat'=>' ', 'postfix' => ''];
		$format = array_merge($format, isset($this->dataMapFields['format']) ? $this->dataMapFields['format'] : []);
		unset($this->dataMapFields['format']);
		$mapResults = $provider->getDataMap($this->dataMapKey, $query, $this->dataMapFilters, $this->dataMapFields, $start, $length);
		$maps = [];
		foreach ($mapResults as $key=>$mapSet) {
			if (is_array($mapSet)) {
				$map = implode($format['concat'], $mapSet);
			} else {
				$map = $mapSet;
			}
			$maps[$key] = $format['prefix'].$map.$format['postfix'];
		}
		return $maps;
	}

	/**
	 * Gets the Map Provider service
	 * @return IDataMapProvider
	 * @throws \Exception
	 */
	public function getProviderService()
	{
		return neon()->app('core')->getDataMapProvider($this->dataMapProvider);
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		$items = $this->getItems();
		if (is_array($this->value)) {
			$values = array_intersect_key($items, array_flip($this->value));
			return implode(', ',$values);
		} else if (isset($items[$this->value])) {
			return $items[$this->value];
		}
		return null;
	}

	/**
	 * The value from the database is the uuid of the linked field
	 * @inheritDoc
	 */
	public function setValueFromDb($value)
	{
		parent::setValueFromDb($value);
		if (!empty($value))
			$this->makeMapRequest($value);
		return $this;
	}

	/**
	 * Make a map request - this lets the map provider aware that a map request will likely be called
	 * when getItems is called - this enables batching multiple selectDynamics or map requests into one call
	 * @param mixed $value
	 * @throws \Exception
	 */
	public function makeMapRequest($value)
	{
		$dataMapFields = $this->dataMapFields;
		unset($dataMapFields['format']);
		$this->_mapLookupRequestKey = $this->getProviderService()->makeMapLookupRequest($this->dataMapKey, $value, $dataMapFields);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if ($searchData !== '') {
			$query->where($this->getDataKey(), '=', $searchData);
		}
	}

	/**
	 * @return string
	 */
	protected function extractLinkClass()
	{
		return $this->dataMapKey;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['dataMapProvider', 'dataMapKey', 'dataMapFilters', 'dataMapFields', 'items', 'limit']);
	}

	/**
	 * @inheritdoc
	 */
	public function exportDefinition()
	{
		// clear any items on the form which are used for displaying but
		// not meant to be stored in the definition
		$definition = parent::exportDefinition();
		$definition['choices'] = [];
		$definition['definition']['items'] = [];
		return $definition;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Link One', 'name' => 'linkone', 'icon' => 'fa fa-link', 'group' => 'Table Links', 'order'=> 410,
		];
	}
}
