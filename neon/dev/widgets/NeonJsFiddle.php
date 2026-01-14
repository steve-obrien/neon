<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/09/2018
 * @package neon
 */
namespace neon\dev\widgets;


use neon\core\helpers\Arr;
use neon\core\helpers\Hash;
use neon\core\helpers\Str;
use yii\base\Widget;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * Class NeonFiddle
 * @package neon\dev\widgets
 */
class NeonJsFiddle extends Widget
{
	/**
	 * @var string content captured between begin and end tags
	 */
	public $content;

	/**
	 * @var string[] - array of string asset bundle class paths to be registered
	 * @example
	 * ```php
	 * ['\neon\core\asset\CoreAsset']
	 * ```
	 */
	public $bundles = [];

	/**
	 * @var string[] - array of url string dependencies - to be registered
	 */
	public $urls = [];

	/**
	 * The tab to active on start
	 * @var string
	 */
	public $activeTab = 'javascript';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		ob_start();
	}

	/**
	 * @var string - path to the directory where serialized data files containing the fiddle data will be kept
	 */
	public static $dir = '@runtime/fiddles/js';

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		\neon\core\form\assets\FormAsset::register(neon()->view);
		(new \neon\core\form\fields\Ide('editor'))->registerScripts(neon()->view);

		if (!neon()->user->isSuper())
			return 'This plugin is not available';
		FileHelper::createDirectory(neon()->getAlias(self::$dir));
		$blockContent = trim(ob_get_clean());
		// specified content takes precedent over scraped content between the begin and end tags
		$this->content = $this->content ? $this->content : $blockContent;
		$this->bundles = $this->bundles ? $this->bundles : [];

		if (is_string($this->bundles)) {
			// should be comma separated list of string bundle class namespaces
			$this->bundles = array_map('trim', explode(',', $this->bundles));
		}
		if (is_string($this->urls)) {
			// should be comma separated list of string bundle class namespaces
			$this->urls = explode(',', $this->urls);
		}

		$html = $this->getTagValue($this->content, 'html');
		$style = $this->getTagValue($this->content, 'style');
		$script = $this->getTagValue($this->content, 'script');

		$fileId = self::process($script, $style, $html, $this->bundles, $this->urls);
		return $this->render('neonJsFiddle.tpl', [
			'uuid' => Hash::uuid64(),
			'html'   => str_replace('`', '>>>BACK_TICK<<<', trim($html)),
			'script' => str_replace('`', '>>>BACK_TICK<<<', trim($script)),
			'style'  => str_replace('`', '>>>BACK_TICK<<<', trim($style)),
			'bundles' => Json::encode($this->bundles),
			'urls' => Json::encode($this->urls),
			'activeTab' => $this->activeTab,
			'fileId' => $fileId
		]);
	}

	/**
	 * @param $script
	 * @param $style
	 * @param $html
	 * @param array $bundles
	 * @param array $urls
	 * @return string
	 */
	public static function process($script, $style, $html, $bundles=[], $urls=[])
	{
		$data = compact(['script','style', 'html', 'bundles', 'urls']);
		$serialized = serialize($data);
		$id = sha1($serialized);
		$dir = self::$dir;
		file_put_contents(neon()->getAlias("$dir/$id"), $serialized);
		return $id;
	}

	public function renderResult($fileId)
	{
		$dir = self::$dir;
		$serialized = file_get_contents(neon()->getAlias("$dir/$fileId"));
		$data = unserialize($serialized);
		$this->addDependencies($data);
		neon()->view->resolveBundles();
		return neon()->view->injectHtml($this->render('neonJsFiddleResult.tpl', $data));
	}

	public function addDependencies($data)
	{
		$bundles = Arr::getRequired($data, 'bundles');
		$urls = Arr::getRequired($data, 'urls');
		foreach($bundles as $bundle)
			neon()->view->registerAssetBundle($bundle);
		foreach($urls as $url) {
			if (Str::endsWith($url['url'], 'js'))
				neon()->view->registerJsFile($url);
			if (Str::endsWith($url['url'], 'css'))
				neon()->view->registerCssFile($url);
		}
	}

	public function getTagValue($string, $tag)
	{
		$pattern = "/<{$tag}>(.*?)<\/{$tag}>/s";
		preg_match($pattern, $string, $matches);
		return isset($matches[1]) ? $matches[1] : '';
	}

	public function getTageValues($content, $tags)
	{
		$arr = [];
		foreach ($tags as $tag) {
			$arr[$tag] = $this->getTagValue($content, $tag);
		}
		return $arr;
	}
}