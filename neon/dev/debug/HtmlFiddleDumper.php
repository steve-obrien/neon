<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/09/2018
 * @package neon
 */

namespace neon\dev\debug;

use neon\core\web\View;
use Symfony\Component\VarDumper\Cloner\Cursor;

/**
 * Create a nicely formatted dump - but make similar to the `./neo dev/shell` in other words more plain text
 * remove the style and script tags output.  If a yii view object is available then the style tag will be registered
 * with the html head position and injected on render.
 * Class HtmlFiddleDumper
 * @package neon\dev\debug
 */
class HtmlFiddleDumper extends HtmlDumper
{
	protected $dumpPrefix = '<pre class=sf-dump id=%s data-indent-pad="%s">';
	protected $dumpSuffix = '</pre>';

	/**
	 * {@inheritdoc}
	 */
	public function enterHash(Cursor $cursor, $type, $class, $hasChild)
	{
		if (Cursor::HASH_INDEXED === $type || Cursor::HASH_ASSOC === $type) {
			$class = 0;
		}
		parent::enterHash($cursor, $type, $class, $hasChild);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function dumpLine($depth, $endOfValue = false)
	{
		if ($endOfValue && 0 < $depth) {
			$this->line .= ',';
		}
		parent::dumpLine($depth, $endOfValue);
	}

	/**
	 * Dumps the HTML header.
	 */
	protected function getDumpHeader()
	{
		parent::getDumpHeader();
		neon()->view->registerHtml($this->dumpHeader, View::POS_BEGIN);
	}
}