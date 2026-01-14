<?php
/**
 * @link http://www.newicon.net/
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/framework/license/
 * @author Steve O'Brien <steve.obrien@newicon.net>
 * @date 12/12/2015 16:13
 */

namespace neon\core\grid;

use neon\core\grid\column\IColumn;
use neon\core\grid\query\YiiQuery;
use yii\base\InvalidConfigException;
use \yii\base\Widget;
use \yii\helpers\ArrayHelper;
use \yii\data\ActiveDataProvider;
use \neon\core\helpers\Html;
use \neon\core\helpers\Url;
use \yii\widgets\LinkPager;
use ReflectionClass;

/**
 * The Grid class inherit from this class to create your own advanced neon Grids
 *
 * The grid goes through 4 main stages to render:
 * 1: $this->init(): function is called, this is where you configure the columns by adding them to the grid
 * 2: $this->load($_REQUEST): data pertinent to the grid is loaded. Typically this is the request data
 * 3: $this->setup(): the setup function is called, this allows customisation of the grid and columns based on the request data
 * 4: $this->run(): finally run processes and renders the grid
 *
 * ## Notes:
 * processSearch: inside the run function processSearch is called, this function passes the queryBuilder object to each column to
 * collect individual column filter information
 *
 * @package neon\core\grid
 * @author Steve O'Brien <steve@newicon.net>
 *
 * @property \yii\data\DataProviderInterface $dataProvider
 * 
 * public function init()
 * {
 *     $this->query = (new \yii\db\Query())->select('*')->from('table');
 *     // you can add columns via helper functions.
 *     // but columns are essentially datatypes - and fields are basically data types.
 *     // so it's generally more powerful to add via these:
 *     $this->addColumn('json', [
 *         'member' => new \neon\core\form\fields\Json('json'),
 *     ]);
 *     // shorthand method
 *     $this->addColumnField('json', new \neon\core\form\fields\Json('json'));
 * }
 */
class Grid extends GridBase
{
	/**
	 * The active query object
	 * @var \yii\db\ActiveQuery
	 */
	public $query;

	/**
	 * Get the grids Data provider object
	 *
	 * @return YiiActiveDataProvider
	 */
	public function getDataProvider()
	{
		if (!$this->_dataProvider) {
			$this->_dataProvider = new YiiActiveDataProvider([
				'query' => $this->query,
			]);
			$this->setDataProvider($this->_dataProvider);
		}
		return $this->_dataProvider;
	}
}