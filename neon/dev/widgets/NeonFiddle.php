<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/09/2018
 * @package neon
 */
namespace neon\dev\widgets;


use neon\dev\debug\HtmlFiddleDumper;
use neon\dev\debug\NeonCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use yii\base\Widget;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Class NeonFiddle
 * @package neon\dev\widgets
 */
class NeonFiddle extends Widget
{
	/**
	 * @var string content captured between begin and end tags
	 */
	public $content;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		ob_start();
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		if (!neon()->user->isSuper())
			return 'This widget is not available';

		$blockContent = trim(ob_get_clean());
		// specified content takes precedent over scraped content between the begin and end tags
		$this->content = $this->content ? $this->content : $blockContent ;

		$f = new \neon\core\form\Form('terminal_'.$this->id);
		$f->add(new \neon\core\form\fields\Ide('ide'));
		$f->registerScripts(neon()->view, false);

		return $this->render('neonFiddle.tpl', [
			'content' => Json::encode($this->content),
			'result' => Json::encode(self::process($this->content)),
			// (the controller is obviously also secured, this is just to aid the usability and not show buttons that wont work!)
			'canRun' => neon()->user->isSuper(), // this prevents showing the run button
			'formId' => $f->getId(),
			'files' => $this->getFiddles()
		]);
	}

	/**
	 * Evaluate php and return a dump of the result :scream:
	 * @param string $php - to evaluate
	 * @param boolean $save - whether to save the php for later recall
	 * @return string - returns the string dump of the result
	 * @throws \Exception - will throw exceptions based on evaluated php
	 */
	public static function process($php, $save = true)
	{
		return self::dump(self::doEval($php, $save));
	}

	/**
	 * Format a response from php as a nice string dump
	 * @param mixed $result
	 * @return string
	 */
	public static function dump($result)
	{
		$cloner = new VarCloner();
		$cloner->addCasters(NeonCaster::getCasters());
		$dumper = new HtmlFiddleDumper();
		return $dumper->dump($cloner->cloneVar($result), true);
	}

	/**
	 * Run php code and return the result
	 * @param string $code - php code to eval
	 * @param boolean $save - whether to save the file
	 * @return mixed|string
	 * @throws \yii\base\Exception
	 */
	public static function doEval($code, $save=true)
	{
		if (!neon()->user->isSuper())
			return;
		$dir = static::getFiddleDir();
		FileHelper::createDirectory($dir);
		$file = neon()->getAlias($dir . date('Y').date('m').date('d').'-'. date('H'). '_' . sha1($code) . '.php');
		file_put_contents($file, "<?php $code ?>");
		$ret = null;
		try {
			ob_start();
			$ret = include $file;
			$echoed = ob_get_clean();
			$ret = $echoed ?: $ret;
		} finally {
			if ($save !== true) {
				dd('HERE');
				unlink($file);
			}
		}
		return $ret;
	}

	/**
	 * Get the direct path where fiddle files should be stored
	 * @return bool|string
	 */
	public static function getFiddleDir()
	{
		return neon()->getAlias('@runtime/fiddles/php/');
	}

	/**
	 * @param $file
	 * @return bool|string
	 */
	public static function getFiddleContentFromFile($file)
	{
		$contents = file_get_contents(static::getFiddleDir(). basename($file));
		return str_replace(['<?php', '?>'], '', $contents);
	}

	/**
	 * Return an array of all saved php fiddles
	 * @return array
	 */
	public function getFiddles()
	{
		$files = array_diff(scandir(static::getFiddleDir(), SCANDIR_SORT_DESCENDING), ['..', '.']);
		if (!empty($files))
			return $files;
	}

}
