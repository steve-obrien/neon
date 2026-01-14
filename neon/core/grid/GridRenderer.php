<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 11/10/2018
 * Time: 14:23
 */

namespace neon\core\grid;

use neon\core\grid\query\YiiQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use yii\base\ViewContextInterface;
use yii\base\Widget;
use yii\widgets\LinkPager;

/**
 * Class GridRenderer
 * This class is responsible for grid render functions.
 * For crazy ambitious grids this allows you to change its rendering functions by creating your own
 * @package neon\core\grid
 */
class GridRenderer implements ViewContextInterface
{
	/**
	 * @var GridBase
	 */
	private $_grid;

	/**
	 * @var string
	 */
	public $viewFile = '@neon/core/grid/views/body.tpl';

	/**
	 * GridRenderer constructor.
	 * @param GridBase $grid
	 */
	public function __construct($grid)
	{
		$this->_grid = $grid;
	}

	/**
	 * @return GridBase
	 */
	public function getGrid()
	{
		return $this->_grid;
	}

	/**
	 * Renders a view.
	 *
	 * The view to be rendered can be specified in one of the following formats:
	 *
	 * - [path alias](guide:concept-aliases) (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
	 *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
	 *   active module.
	 * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
	 *
	 * If the view name does not contain a file extension, it will use the default one `.php`.
	 *
	 * @param string $view the view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidArgumentException if the view file does not exist.
	 */
	public function render($view, $params = [])
	{
		return neon()->view->render($view, $params, $this);
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file to be rendered. This can be either a file path or a [path alias](guide:concept-aliases).
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidArgumentException if the view file does not exist.
	 */
	public function renderFile($file, $params = [])
	{
		return neon()->view->renderFile($file, $params, $this);
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
	 * @return string the directory containing the view files for this widget.
	 */
	public function getViewPath()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * @return mixed
	 */
	public function doRender()
	{
		return $this->render($this->viewFile, [
			'debug' => (env('NEON_DEBUG') == true),
			'grid' => $this->_grid,
			'renderer' => $this
		]);
	}

	/**
	 * Render header groups
	 * @return string
	 */
	public function renderHeaderGroups()
	{
		if (empty($this->_grid->getColumnGroups())) {
			return '';
		}

		$out = '<tr>';
		foreach($this->_grid->getColumnGroups() as $groupLabel => $details) {
			$colspan = count($details['columns']);
			$out .= "<td colspan='$colspan'>$groupLabel</td>";
		}
		$out .= '</tr>';
		return $out;
	}

	// region Render Functions
	//------------------------------------------------------------------------------------------------------------------

	/**
	 * Renders the pager.
	 * @throws \Exception - if Widget instantiation fails
	 * @return string the rendering result
	 */
	public function renderPager()
	{
		$pagination = $this->_grid->getDataProvider()->getPagination();
		$filters = [];
		foreach($this->_grid->getFilterForm()->getFields() as $field) {
			if (!empty($field->getValue()))
				$filters[$field->getInputName()] = $field->getValue();
		}
		$queryParams = neon()->request->queryParams;
		// remove any pre existing grid specific filters from the url - we don't want to add them twice
		if (isset($queryParams[$this->_grid->gridName()]))
			unset($queryParams[$this->_grid->gridName()]);
		// gross - we need to remove any pre existing filters from the query params
		$pagination->params = $queryParams + $filters;

		/* @var $class LinkPager */
		$pager = $this->_grid->pager;
		$class = Arr::remove($pager, 'class', LinkPager::class);
		$pager['pagination'] = $pagination;
		$pager['view'] = neon()->view;
		return $class::widget($pager);
	}

	/**
	 * Render the grid rows
	 *
	 * @return string
	 */
	public function renderRows()
	{
		$models = array_values($this->_grid->getDataProvider()->getModels());
		$out = '';
		foreach($models as $index => $row) {
			$out .= $this->renderRow($row);
		}
		return $out;
	}

	/**
	 * Render a single grid row
	 *
	 * @param array $row - row data
	 * @return string
	 */
	public function renderRow($row)
	{
		$out = '<tr >';
		foreach ($this->_grid->getColumns() as $colKey => $columnObject) {
			if ($columnObject->visible) {
				$out .= $columnObject->renderDataCell($row, $colKey, 0);
			}
		}
		$out .= '</tr>';
		return $out;
	}

	/**
	 * Render grid scope filters
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function renderScopes()
	{
		$postScope = Arr::getValue($_REQUEST, [$this->_grid->id, 'scope']);
		if ($postScope && Arr::getValue($this->_grid->getScopes(), $postScope)) {
			$currentFilter = $postScope;
		} else {
			$currentFilter = key($this->_grid->getScopes());
		}
		$first = true;
		$filtersHTML = '';
		foreach($this->_grid->getScopes() as $link => $scope) {

			$count = $this->_grid->getScopeTotalCount($link);
			$current = '';
			if ($currentFilter == $link) {
				$current = 'active';
			}
			$filtersHTML .= '<li>';
			if ($first) {
				$first = false;
			} else {
				$filtersHTML .= ' | ';
			}
			$filtersHTML .= '<a id="'.$this->_grid->id.'-scope-'.$link
				.'" data-scope="'.$link.'" href="?'.$this->_grid->id.'[scope]='.$scope['key'].'" class="'.$current.'">'.$scope['name'].' <span class="count">('.$count.')</span></a></li>';
		}
		return $filtersHTML;
	}

	/**
	 * Html tag options for the summary div element
	 *
	 * @var array
	 */
	public $summaryOptions = ['class' => 'neonGrid_summary'];

	/**
	 * Terrible yii code: renders a summary of the fetched data - e.g. "showing 1 of 59 records"
	 *
	 * @return string
	 */
	public function renderSummary()
	{
		$count = $this->_grid->dataProvider->getCount();
		if ($count <= 0) {
			return '';
		}
		$summaryOptions = $this->summaryOptions;
		$tag = Arr::remove($summaryOptions, 'tag', 'div');
		if (($pagination = $this->_grid->dataProvider->getPagination()) !== false) {
			$totalCount = $this->_grid->dataProvider->getTotalCount();
			$begin = $pagination->getPage() * $pagination->pageSize + 1;
			$end = $begin + $count - 1;
			if ($begin > $end) {
				$begin = $end;
			}
			$page = $pagination->getPage() + 1;
			$pageCount = $pagination->pageCount;
			$tagMessage = ' <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.';
			return Html::tag($tag, \Yii::t('yii',  $tagMessage, [
				'begin' => $begin,
				'end' => $end,
				'count' => $count,
				'totalCount' => $totalCount,
				'page' => $page,
				'pageCount' => $pageCount,
			]), $summaryOptions);
		} else {
			$begin = $page = $pageCount = 1;
			$end = $totalCount = $count;
			return Html::tag($tag, \Yii::t('yii', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', [
				'begin' => $begin,
				'end' => $end,
				'count' => $count,
				'totalCount' => $totalCount,
				'page' => $page,
				'pageCount' => $pageCount,
			]), $summaryOptions);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	//endregion

}
