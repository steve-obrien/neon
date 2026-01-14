<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 21/10/2018
 * Time: 13:04
 */

namespace neon\core\helpers;

/**
 * Css helper Class  - perform css operations and string manipulations
 * @package neon\core\helpers
 */
class Css
{
	/**
	 * Applies basic css minification to css string
	 *
	 * @return string - the minified css
	 */
	public static function minify($css)
	{
		// Some basic css minification - it makes little difference when gzipping
		// Remove comments
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		// Remove space after colons
		$css = str_replace(': ', ':', $css);
		// Remove whitespace
		$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
		return $css;
	}

	/**
	 * Finds and replaces all instances of url('') with an absolute path.
	 * This function fixes the problem of relative url includes in css.  For example combining multiple
	 * css assets from different libraries - both of these libraries could import relative files such as 'img/folder.png'
	 * we need to replace the 'img/folder.png' with the full asset path which might become 'publish/assets/cd89e3/img/folder.png'
	 *
	 * @param string $cssContent
	 * @param string $publishedCssFilePath - the full public asset path of the css file - as urls are relative
	 * a `dirname($cssFile)` provides the correct path to replace.
	 * @return null|string|string[]
	 */
	public static function replaceRelativeUrls($cssContent, $publishedCssFilePath)
	{
		$content = preg_replace_callback('/url\(([^)]*)\)/', function ($matches) use ($publishedCssFilePath) {
			$url = trim($matches[1], '\'"');
			// ignore non url (urls e.g. data:image url paths) and absolute urls
			if (Url::isAbsolute($url))
				return $matches[0];
			return 'url("' . dirname($publishedCssFilePath) . '/' . $url . '")';
		}, $cssContent);
		return $content;
	}
}