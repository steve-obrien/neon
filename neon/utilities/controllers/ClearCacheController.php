<?php


namespace neon\utilities\controllers;


use neon\cms\components\Renderer;
use neon\core\web\AdminController;
use neon\core\view\ICanFlushTemplates;
use yii\caching\Cache;
use yii\caching\CacheInterface;

class ClearCacheController extends AdminController
{
	public $layout = '@neon/utilities/views/layout.php';

	/**
	 * @return string
	 */
	public function actionIndex()
	{
		$this->view->title = 'Clear Cache';
		return $this->render('index', [
			'caches' => $this->getCaches()
		]);
	}

	/**
	 * Clears a cache component
	 */
	public function actionClear($app, $component)
	{
		$cmp = neon()->getApp($app)->get($component);
		if ($cmp !== null && $cmp instanceof CacheInterface) {
			if ($cmp->flush()) {
				neon()->session->setFlash('success', $component . ' cache successfully flushed');
				return $this->redirect(['/utilities/clear-cache/index']);
			}
		}
	}

	/**
	 * Clear all caches
	 */
	public function actionClearSmarty()
	{
		list($compiled, $cached) = $this->clearSmartyCache();
		neon()->session->setFlash('success', "Smarty cache cleared 👍  - removed $compiled compiled templates and $cached cache files");
		return $this->redirect(['/utilities/clear-cache/index']);
	}

	/**
	 * Handles clearing assets action
	 */
	public function actionClearAssets()
	{
		$removed = neon()->assetManager->flushAssets();
		neon()->session->setFlash('success', "Flushed $removed asset directories");
		return $this->redirect(['/utilities/clear-cache/index']);
	}

	/**
	 * Clear all caches
	 */
	public function actionClearAll()
	{
		$caches = $this->getCaches();
		foreach($caches as $app => $caches) {
			$a = neon()->getApp($app);
			if ($a === null) return;
			foreach($caches as $name => $component) {
				$cmp = $a->get($name);
				if ($cmp !== null && $cmp instanceof CacheInterface) {
					$cmp->flush();
				}
			}
		}
		// clear the smarty cache:
		$this->clearTemplateCache();
		neon()->assetManager->flushAssets();
		neon()->session->setFlash('success', "All cache cleared 👍");
		return $this->redirect(['/utilities/clear-cache/index']);
	}

	/**
	 * Clears all template cache
	 * @throws \SmartyException
	 * @throws \yii\base\ErrorException
	 */
	public function clearTemplateCache()
	{
		$r = new Renderer();
		$r->smarty->clearCompiledTemplate();
		$r->smarty->clearAllCache();
		foreach(neon()->view->renderers as $ext => $config) {
			$renderer = neon()->view->getRendererByExtension($ext);
			if ($renderer instanceof ICanFlushTemplates) {
				$renderer->flushTemplates();
			}
		}
	}

	/**
	 * Clear Smarty cache
	 * @return array  position [0] is number of compiled templates removed, position [1] is number of cache files removed
	 * @throws \SmartyException
	 */
	public function clearSmartyCache()
	{
		// smarty is not controlled by an application component that understands the cache interface
		$r = new Renderer();
		return [$r->smarty->clearCompiledTemplate(), $r->smarty->clearAllCache()];
	}

	/**
	 * Returns only cache components from an array like neon()->getComponents();
	 * @param array $components - array of components
	 * @return array
	 */
	public function getCacheComponents($components)
	{
		return collect($components)->filter(function($c) {
			$implements = is_object($c) ? class_implements($c) : class_implements($c['class']);
			return in_array('yii\caching\CacheInterface', $implements);
		})->all();
	}

	/**
	 * Get all cache components
	 * @return array
	 */
	public function getCaches()
	{
		return collect(neon()->getAllApps())->map(function($config, $app) {
			$a = neon()->getApp($app); if ($a === null) return;
			return $this->getCacheComponents($a->getComponents());
		})
		->put('neon', $this->getCacheComponents(neon()->getComponents()))
		->filter(function($item) { return !empty($item); })
		->all();
	}
}
