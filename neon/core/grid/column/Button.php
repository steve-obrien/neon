<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/08/2017
 * Time: 15:12
 */

namespace neon\core\grid\column;


use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;

class Button extends Column
{
	public $dataCellOptions = ['class'=>'text-right'];

	/**
	 * Button column options
	 *
	 * @var array
	 */
	private $_buttons = [];

	/**
	 * Set the edit button url - adds the edit button to the column
	 *
	 * @param array|string $url
	 * @param array $options - additional html attribute options
	 * @return $this
	 */
	public function setEdit($url, array $options=[])
	{
		$this->_buttons['edit'] = $options;
		$this->_buttons['edit']['url'] = $url;
		return $this;
	}

	/**
	 * Set the delete button url - adds the delete button to the column
	 * @param array|string $url
	 * @param array $options - additional html attribute options
	 */
	public function setDelete($url, array $options=[])
	{
		$this->_buttons['delete'] = $options;
		$this->_buttons['delete']['url'] = $url;
	}

	/**
	 * @see Html::buttonCustom
	 * @param string $key - an internal key to refer to this button
	 * @param string|array URL - the button URL the result will eventually be passed into Url::to to create a correct URL
	 * @param string text - the button text
	 * @param string icon - the icon class to use
	 * @param string class - one of ['primary', 'default', 'secondary', 'warning', 'info', 'danger']
	 *   will append text to the CSS class e.g. btn-$type
	 * @param array $options - passed to Html::buttonCustom
	 */
	public function addButton($key, $url, $text, $icon='', $class='', $options=[])
	{
		$this->_buttons[$key] = array_merge(compact('url', 'text', 'icon', 'class'), $options);
	}

	/**
	 * @inheritDoc
	 */
	public function renderFilterCellContent()
	{
		return '';
	}

	/**
	 * The width of an individual button
	 * @var string
	 */
	public $buttonWidth = '3em';

	/**
	 * @inheritdoc
	 */
	public function getWidth()
	{
		return 'calc(' . (count($this->_buttons)+count($this->_actions)) . ' * ' .$this->buttonWidth . ')';
	}

	/**
	 * @inheritDoc
	 */
	public function renderDataCellContent($model, $key, $index, $context='grid')
	{
		if (count($this->_buttons)) {
			$buttons = [];
			foreach ($this->_buttons as $buttonKey => $btnOps) {
				if (!isset($btnOps['data']))
					$btnOps['data'] = [];
				if ($this->getGrid()->hasIndexColumn())
					$btnOps['data'] = ['index' => $model[$this->getGrid()->getIndexColumn()->getKey()] ];
				$func = 'buttonCustom';
				if ($buttonKey === 'delete') {
					$func = 'buttonDelete';
					$btnOps['data'] = Arr::merge($btnOps['data'], ['data'=> ['delete-ajax' => true ]]);
				}
				if ($buttonKey === 'edit') {
					$func = 'buttonEdit';
				}
				$text = Arr::remove($btnOps, 'text', '');
				$url = Arr::remove($btnOps, 'url', '');
				$url = $this->grid->replaceRowTagsWithValues($url, $model);
				$btnOps['class'] = Arr::get($btnOps, 'class', '') . ' btn-grid';
				$buttons[] = Html::$func($url, $text, $btnOps);
			}
			return '<div class="btn-group btn-column">' . implode('', $buttons) . '</div>';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function processSearch(IQuery $query)
	{
		// not applicable
	}

	/**
	 * @inheritDoc
	 */
	public function processSort(IQuery $query, $descending)
	{
		// not applicable
	}

}