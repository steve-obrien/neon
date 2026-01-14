<?php

namespace neon\core\web;

use neon\core\helpers\Css;
use neon\core\helpers\File;
use neon\core\helpers\Html;
use neon\core\helpers\Str;
use neon\core\helpers\Url;
use yii\base\ViewRenderer;

/**
 * 
 * @property \neon\cms\controllers\RenderController $context
 */
class View extends \yii\web\View
{
	/**
	 * @var array - store html fragments indexed by position
	 * @see getHtmlFragment
	 * @see registerHtml
	 */
	public $html = [];

	/**
	 * Whether css files should be concatenated
	 * @var bool
	 */
	public $cssConcatenate = false;

	/**
	 * @var bool - Whether to process the css for variables using the registered css renderer
	 */
	public $cssProcess = false;

	/**
	 * @var bool - whether to minify the css content
	 * It may be useful to set this to false in development mode
	 */
	public $cssMinify = true;

	/**
	 * @var bool - Whether to concatenate js files
	 */
	public $jsConcatenate = false;

	/**
	 * @var array - the route to the controller action that will serve dynamic css content
	 * using the registered css renderer
	 */
	public $cssDynamicRoute = ['/core/css/asset'];

	/**
	 * @var StyleManager|array config for style manager
	 */
	public $styleManager = ['class' => '\neon\core\web\StyleManager'];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->styleManager = \Neon::createObject($this->styleManager);
	}

	public function getPage()
	{
		return neon()->cms->getPageData();
	}
	/**
	 * Registers a html code block.
	 * This allows you to chuck html in the appropriate area - for example a pre minified blob of code
	 * that can contain both <style> and <script> tags - this can be useful when adding embedded code from external providers
	 * for example facebook pixel, google analytics or even chat plugins like intercom.
	 *
	 * @param string $html the Html code block to be registered
	 * @param int $position the position at which the html block should be inserted
	 * in a page. The possible values are:
	 *
	 * - [[POS_HEAD]]: in the head section
	 * - [[POS_BEGIN]]: at the beginning of the body section
	 * - [[POS_END]]: at the end of the body section
	 *
	 * @param string $key the key that identifies the html code block. If null, it will use
	 * md5($html) as the key. If two html code blocks are registered with the same key, the latter
	 * will overwrite the former.
	 */
	public function registerHtml($html, $position = self::POS_END, $key = null)
	{
		$key = $key ?: md5($html);
		$this->html[$position][$key] = $html;
	}

	/**
	 * Checks if the view file exists
	 *
	 * @param  string  $view
	 * @param  Object  $context  The context to find the view - typically the controller object
	 * @return bool
	 */
	public function exists($view, $context)
	{
		return file_exists($this->findViewFile($view, $context));
	}

	/**
	 * Find the view renderer by extension
	 *
	 * @param string $extension - the extension e.g. tpl | twig | md
	 * @return bool|ViewRenderer
	 */
	public function getRendererByExtension($extension)
	{
		if (isset($this->renderers[$extension])) {
			if (is_array($this->renderers[$extension]) || is_string($this->renderers[$extension])) {
				$this->renderers[$extension] = \Neon::createObject($this->renderers[$extension]);
			}
			/* @var $renderer ViewRenderer */
			return $this->renderers[$extension];
		}
		return false;
	}

	/**
	 * Find a view file
	 *
	 * @param  string  $view  The view name
	 * @param  Object  $context
	 * @return  string|false  file path or false if the file does not exist
	 */
	public function findView($view, $context)
	{
		$exists = file_exists($this->findViewFile($view, $context));
		return $exists ? $this->findViewFile($view, $context) : false ;
	}

	/**
	 * Renders a view in response to an AJAX request.
	 *
	 * This method is similar to [[render()]] except that it will surround the view being rendered
	 * with the calls of [[beginPage()]], [[head()]], [[beginBody()]], [[endBody()]] and [[endPage()]].
	 * By doing so, the method is able to inject into the rendering result with JS/CSS scripts and files
	 * that are registered with the view.
	 *
	 * @param string $content the content string to render
	 * @return string the rendering result
	 * @see render()
	 */
	public function renderAjaxContent($content)
	{
		ob_start();
		ob_implicit_flush(false);

		$this->beginPage();
		$this->head();
		$this->beginBody();
		echo $content;
		$this->endBody();
		$this->endPage(true);

		return ob_get_clean();
	}

	/**
	 * Resolves all registered asset bundles and registers their files
	 */
	public function resolveBundles()
	{
		foreach (array_keys($this->assetBundles) as $bundle) {
			$this->registerAssetFiles($bundle);
		}
	}

	/**
	 * Replaces the static::PH_* tags with the appropriate content
	 *
	 * @param string $html
	 * @return string
	 */
	public function injectHtml($html)
	{
		$this->trigger(self::EVENT_END_PAGE);
		neon()->view->resolveBundles();
		$output = strtr($html, [
			self::PH_HEAD => $this->renderHeadHtml(),
			self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
			self::PH_BODY_END => $this->renderBodyEndHtml(neon()->request->isAjax)
		]);
		$this->clear();
		return $output;
	}

	/**
	 * @inheritdoc
	 */
	public function renderHeadHtml()
	{
		profile_begin('renderHeadHtml');
		if ($this->cssConcatenate)
			$this->cssFiles = $this->resolveFiles($this->cssFiles, 'css');
		$out = parent::renderHeadHtml() . "\n" . $this->getHtmlFragment(self::POS_HEAD);
		profile_end('renderHeadHtml');
		return $out;
	}

	/**
	 * @inheritdoc
	 */
	public function renderBodyBeginHtml()
	{
		return parent::renderBodyBeginHtml() . "\n" . $this->getHtmlFragment(self::POS_BEGIN);
	}

	/**
	 * @inheritdoc
	 */
	public function renderBodyEndHtml($ajaxMode)
	{
		profile_begin('renderBodyEndHtml');
		if ($this->jsConcatenate) {
			if (isset($this->jsFiles[self::POS_END]))
				$this->jsFiles[self::POS_END] = $this->resolveFiles($this->jsFiles[self::POS_END], 'js');
		}
		$out = parent::renderBodyEndHtml($ajaxMode) . "\n" . $this->getHtmlFragment(self::POS_END);
		profile_end('renderBodyEndHtml');
		return $out;
	}

	/**
	 * Get all html fragments registered at position
	 *
	 * @param int $position self::POS_HEAD | self::POS_BEGIN | self::POS_END
	 * @return string
	 */
	public function getHtmlFragment($position)
	{
		return isset($this->html[$position]) ? implode("\n", $this->html[$position]) : '';
	}

	/**
	 * Generates a single css/js file and runs through a view renderer
	 * This generates a new array replacing files of the array that have been replaced by a concatenated file
	 * For e.g:
	 * ```php
	 * // original array:
	 * $css = [
	 *     'my/css/file.one.css' => '<link ...>',
	 *     'my/css/file.two.css' => '<link ...>',
	 *     'my/css/file.three.css' => '<link ...>',
	 *     '//style.com/css/external.css' => '<link ...>',
	 * ];
	 *
	 * // Given all the css files in the above array are concatenated this function will return:
	 * [
	 *     'my/new/concatenated/css.css' => '<link ...>',
	 *     '//style.com/css/external.css' => '<link ...>', // This file will not be concatenated as it is external
	 * ]
	 *
	 * @param array $files - an array of cssFiles to combine and resolve in format compatible with $this->cssFiles
	 * @param string $type one of 'css' or 'js'
	 * @throws \yii\base\Exception - if the file/directory containing the new concatenated file could not be created
	 * @return array - new array of css files to replace $this->cssFiles
	 */
	public function resolveFiles($files, $type='css')
	{
		// should cache results here
		$concatFiles = $this->findFilesToConcatenate($files);
		$url = ($type === 'css') ? $this->cssConcatenateFiles($concatFiles) : $this->jsConcatenateFiles($concatFiles);
		// replace css/js files to be output with the one concatenated file
		foreach($concatFiles as $fileKey) unset($files[$fileKey]);
		$files[$url] = ($type === 'css') ? Html::cssFile($url) : Html::jsFile($url);
		return $files;
	}

	/**
	 * Concatenate css files together
	 * Caches based on a serialised key of the $files as assetManager appends the created timestamp of each file via
	 * ?v=[timestamp] this also enables us to regenerate the file if the source files ave changed.
	 *
	 * @param array $concatenateFiles an array of public asset paths to css files - the same as the css string paths
	 * in $this->cssFiles
	 * @param string $key - By reference this property will be set to the key of the generated css file
	 * enabling css to be generated via @see $this->getCssByKey();
	 * @throws \yii\base\Exception - if the file/directory containing the new concatenated file could not be created
	 * @return string - url to dynamic css file url
	 */
	public function cssConcatenateFiles($concatenateFiles, &$key='')
	{
		$key = md5(serialize([$concatenateFiles, $this->cssMinify]));

		$url = $this->_assetManagerGenerate("/css/$key.css", function() use ($concatenateFiles) {
			// concatenate the css
			$rootPath = neon()->getAlias('@webroot');
			$webPath = neon()->getAlias('@web') ? neon()->getAlias('@web') : 'unknown';
			$cssConcat = '';
			foreach ($concatenateFiles as $fileKey) {
				// The fileKey will be the web accessible path to the js file with possibly a ?v=[timestamp] on the end
				// for cache busting. Lets remove get params ? (namely the cache busting ?v=)
				$file = substr($fileKey, 0, strpos($fileKey, '?'));
				// Get the disk file path - replace web root path with the disk root path
				$fileName = $rootPath . Str::replaceFirst($webPath, '', $file);
				// make sure local css url's are made absolute
				$css = Css::replaceRelativeUrls(file_get_contents($fileName), $fileKey);
				$cssConcat .= $css;
			}
			return $cssConcat;
		});
		// create a dynamic route that will call a controller action
		// enabling the css to be parsed for variables
		$this->cssDynamicRoute['key'] = $key;
		$this->cssDynamicRoute['style'] = md5(serialize($this->getStyleData()));
		return Url::to($this->cssDynamicRoute);
	}

	/**
	 * Concatenate all scripts together
	 *
	 * @param array $concatenateFiles - an array of js files @see $this->jsFiles
	 * @throws \yii\base\Exception - if the file/directory containing the new concatenated file could not be created
	 * @return string - url to generated js file
	 */
	public function jsConcatenateFiles($concatenateFiles)
	{
		$key = md5(implode('', $concatenateFiles));
		return $this->_assetManagerGenerate("/js/$key.js", function() use ($concatenateFiles) {
			// check if the file exists with $jsFileName
			// if it does not exist lets concat all scripts into one file
			$jsConcat = '';
			$rootPath = neon()->getAlias('@webroot');
			$webPath = neon()->getAlias('@web');
			foreach ($concatenateFiles as $fileKey) {
				// The fileKey will be the web accessible path to the js file with possibly a ?v=[timestamp] on the end
				// for cache busting.
				// remove get params ? (namely the cache busting ?v=)
				$file = substr($fileKey, 0, strpos($fileKey, '?'));
				// replace overlap web root path
				$fileName = $rootPath . (empty($webPath) ? $file : Str::replaceFirst($webPath, '', $file));
				// make sure we can find the file here
				// we must concatenate each file on a new line to prevent
				// line comments commenting out the first line of the next file
				if (substr($fileName, -6) === 'min.js') {
					$jsConcat .= "\n" . file_get_contents($fileName);
				} else {
					$jsConcat .= "\n" . file_get_contents($fileName);
					// if allow auto php minify: its a bit too slow
					//$jsConcat .= \JShrink\Minifier::minify(file_get_contents($fileName), array('flaggedComments' => false));
				}
			}
			return $jsConcat;
		});
	}

	/**
	 * Finds css/js files to be concatenated
	 * This function works on theme bundle arrays in the format similar to ['file/path' => '<html tag>']
	 * @see $this->cssFiles and @see $this->jsFiles
	 * Note that adding a concatenate="false" will prevent the file from being concatenated
	 * Furthermore externally loaded assets will not be concatenated
	 *
	 * @param array $files in format of ['file/url' => '<tag>'] @see $this->cssFiles
	 * @return array of published web file paths (keys of $this->cssFiles)
	 */
	public function findFilesToConcatenate($files)
	{
		profile_begin('_findFilesToConcatenate');
		$concatenateFiles = [];
		foreach ($files as $fileKey => $tag) {
			// Do not concatenate externally loaded files (for e.g. from cdns)
			if (Url::isAbsolute($fileKey))
				continue;
			// remove excluded files - files we do not want to concatenate
			if (Str::contains($tag, 'concatenate="false"'))
				continue;
			$concatenateFiles[] = $fileKey;
		}
		profile_end('_findFilesToConcatenate');
		return $concatenateFiles;
	}

	/**
	 * Creates an asset file if one does not exist at defined $path using the result of the
	 * $callback as the file contents - this operates in a similar way to the cache interface `neon()->cache->getOrSet`
	 * With the difference that the contents of this file will be publicly accessible
	 *
	 * @param string $path - the local path within the assets
	 * @param callable $callback - a callback function that generated the file contents
	 * @return string
	 * @throws \yii\base\Exception - if the file/directory containing the new concatenated file could not be created
	 */
	private function _assetManagerGenerate($path, $callback)
	{
		$file = neon()->getAlias(neon()->assetManager->basePath . $path);
		if (! file_exists($file))
			File::createFile($file, $callback());
		return neon()->assetManager->baseUrl . $path;
	}

	/**
	 * Get css for the page
	 * This can be used by the dynamic css controller action to process the css content
	 * and parse using the registered css renderer
	 *
	 * @param string $key - key to identify the css
	 * @param $styleKey
	 * @return string
	 */
	public function getCssByKey($key, $styleKey='')
	{
		$css = '';
		// load css file
		$file = neon()->view->getCssFileByKey($key);
		// parse css file
		$css = neon()->view->cssProcess ?
			$this->renderFile($file, $this->getStyleData($styleKey))
			: file_get_contents(neon()->getAlias($file));
		// minify css file
		if (neon()->view->cssMinify)
			$css = Css::minify($css);
		return $css;
	}

	/**
	 * Returns a path to a css file based on a given key
	 *
	 * @param string $key
	 * @return string a path string or alias string to the css file
	 */
	public function getCssFileByKey($key)
	{
		return '@webroot/assets/css/' . $key . '.css';
	}

	/**
	 * Get the complete data array to be passed to css processor
	 *
	 * @param string $styleKey - a key to identify the style to load
	 * @return array
	 */
	public function getStyleData($styleKey='')
	{
		return neon()->view->styleManager->getStyles();
	}

	/**
	 * Override parent to apply css minification to css blocks
	 *
	 * @inheritdoc
	 */
	public function registerCss($css, $options = [], $key = null)
	{
		parent::registerCss(Css::minify($css), $options, $key);
	}
}