<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/10/2017
 * @package neon
 */

namespace neon\core\view;

use cebe\markdown\GithubMarkdown;
use neon\core\helpers\Str;
use neon\dev\widgets\NeonFiddle;

/**
 * Class MarkdownNeon
 * Adds neon specific elements to markdown
 * For example we may wish to include components via tags in a markdown way for eaxmple
 * {componentName}
 * Or documentation tips
 *
 * {{tip}}
 * Documentation tip
 * {/tip}}
 *
 * {{NeonFiddle}}
 *
 * {/NeonFiddle}}
 *
 * It would also be good to be able to run code (when in debug / dev mode) that would show the code that was run
 * in the documentation and print the results nicely too.  Perhaps:
 *
 * ~~~code
 *
 * ~~~
 *
 * @package neon\core\view
 */
class MarkdownNeon extends GithubMarkdown
{
	public $enableNewlines = true;

	public $neonPluginOpenTag = ['<!', '>'];
	public $neonPluginCloseTag = '</!>';

	/**
	 * Store an array of tags to render widgets store th tag text without delimiters
	 * for example:
	 * ```php
	 * [
	 *     'myPlugin' => ['class' => 'neon\my\class']
	 * ]
	 * ```
	 * @var array
	 */
	public $registeredWidgets = [];

	protected function identifyNeonPlugin($line, $lines, $current)
	{
		$openDelimiter = $this->neonPluginOpenTag[0];
		//if (Str::startsWith($line, $openDelimiter))
		if (strncmp($line, $openDelimiter, strlen($openDelimiter)) === 0) {
			return true;
		}
		return false;
	}

	protected function consumeNeonPlugin($lines, $current)
	{
		// create block array
		$block = ['neonPlugin'];

		$block['plugin']  = $this->_getPluginNameFromLines($lines, $current);
		$block['attributes']  = $this->_getPluginAttributesFromLines($lines, $current);
		$block['content'] = $this->_getPluginContentFromLines($lines, $current, $endLine);

		return [$block, $endLine];
	}

	protected function renderNeonPlugin($block)
	{
		$plugin = $block['plugin'];
		$content = implode("\n",$block['content']);
		$attributes = $block['attributes'];
		if (class_exists($plugin))
			return $plugin::widget(array_merge($attributes, ['content' => $content]));
		return '<div class="alert alert-danger">unknown plugin: &quot;' .  $plugin . '&quot;</div>';
	}

	protected function _getPluginAttributesFromLines($lines, $current)
	{
		list ($openDelimiter, $closeDelimiter) = $this->neonPluginOpenTag;
		$content = [];
		// consume all lines until the $closingTag is found
		for ($i = $current, $count = count($lines); $i < $count; $i++) {

			$line = $lines[$i];
			// stop consuming lines if we find the closing tag
			if (strpos($line, $closeDelimiter) !== false) {
				$content[] = $line;
				break;
			}
			$content[] = $line;
		}
		// remove tag and open delimiter from first and last lines
		$plugin = $this->_getPluginNameFromLines($lines, $current);
		$lastLine = \count($content)-1;
		$content[0] = Str::replaceFirst($openDelimiter, '', $content[0]);
		$content[0] = Str::replaceFirst($plugin, '', $content[0]);
		$content[$lastLine] = Str::replaceLast($closeDelimiter, '', $content[$lastLine]);

		$attrs = implode(' ', $content);
		// xml part
		$xml = simplexml_load_string("<attr $attrs></attr>");
		$attributes = [];
		foreach($xml[0]->attributes() as $a => $b) {
			$attributes[$a] = (string) $b;
		}
		return $attributes;
	}

	/**
	 * Get the plugin name without delimiters
	 *
	 * @param string $line - the line which contains the plugin tag
	 * @param int $current - the current line position
	 * @return String - the plugin name
	 */
	private function _getPluginNameFromLines($lines, $current)
	{
		$line = rtrim($lines[$current]);
		list ($openDelimiter, $closeDelimiter) = $this->neonPluginOpenTag;
		$tag = trim(substr($line, \strlen($openDelimiter)));
		// remove the closing delimiter if it has one
		$tagLine = str_replace($closeDelimiter, '', $tag);
		return explode(' ', $tagLine)[0];
	}

	/**
	 * @param array $lines
	 * @param int $current the current line to start from (the line with the detected plugin tag)
	 * @param int $i - the end line number
	 * @return array - array of lines that should be sent to this plugin
	 */
	private function _getPluginContentFromLines(array $lines, $current, &$i)
	{
		$closingTag = $this->neonPluginCloseTag;
		$content = [];
		// consume all lines until the $closingTag is found
		for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			// stop consuming lines if we find the closing tag
			if (strpos($line, $closingTag) !== false)
				break;
			$content[] = $line;
		}
		return $content;
	}

	protected function _getLinesBetween($lines, $current, $startTag, $endTag)
	{

	}


}