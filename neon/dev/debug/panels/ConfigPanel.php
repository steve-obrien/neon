<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\dev\debug\panels;

use yii\base\ViewContextInterface;

/**
 * Debug Panel.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ConfigPanel extends \yii\debug\panels\ConfigPanel implements ViewContextInterface
{
	/**
	 * @inheritdoc
	 */
	public function save()
	{
		$data = parent::save();
		$data['application']['yii'] = neon()->getYiiVersion();
		$data['application']['neon'] = neon()->getVersion();
		return $data;
 	}

	/**
	 * @inheritdoc
	 */
	public function getViewPath()
	{
		return __DIR__ . '/views/config';
	}

	/**
	 * @inheritdoc
	 */
	public function getSummary()
	{
		return neon()->view->render('summary', ['panel' => $this], $this);
	}

	/**
	 * @inheritdoc
	 */
	public function getDetail()
	{
		return neon()->view->render('detail', ['panel' => $this], $this);
	}
}