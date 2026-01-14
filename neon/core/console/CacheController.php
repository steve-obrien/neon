<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\console;

use neon\cms\components\Renderer;
use neon\core\helpers\Console;
use neon\core\helpers\File;
use neon\core\view\ICanFlushTemplates;
use yii\helpers\FileHelper;

/**
 *
 */
class CacheController extends \yii\console\controllers\CacheController
{
	/**
	 * Clears smarty compiled templates (Alias of core/flush-templates)
	 *
	 * @param string $db id connection component
	 * @return int exit code
	 * @throws Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionFlushSmarty()
	{
		return $this->actionFlushTemplates();
	}

	/**
	 * Clears compiled templates
	 *
	 * @param string $db id connection component
	 * @return int exit code
	 * @throws Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionFlushTemplates()
	{
		// cms breaks view rendering convention (for no real good reason)
		foreach(neon()->view->renderers as $ext => $config) {
			$renderer = neon()->view->getRendererByExtension($ext);
			if ($renderer instanceof ICanFlushTemplates) {
				$renderer->flushTemplates();
				$this->stdout("✔ $ext templates cache cleared\n", Console::FG_GREEN);
			}
		}
	}

	/**
	 * Flush all published assets
	 */
	public function actionFlushAssets()
	{
		$removed = neon()->assetManager->flushAssets();
		$this->stdout("✔ cleared $removed published assets directory(s)\n", Console::FG_GREEN);
	}
}
