<?php

namespace neon\core\helpers;

use neon\core\helpers\Url;
use neon\core\helpers\HtmlPurifier;
use DOMDocument;
use DOMXPath;
use Exception;

class Html extends \yii\helpers\Html
{
	/**
	 * Show an array of items as tags - using span element and apply specified css class
	 *
	 * @param array $tagsArray
	 * @param string $class - css classes to apply to the wrapping tag
	 */
	public static function displayAsTags($tagsArray, $class = 'label label-info')
	{
		$out = '';
		foreach($tagsArray as $tag) {
			$out .= "<span class=\"$class\">$tag</span>";
		}
		return $out;
	}

	/**
	 * Generate a favicon
	 * @param string|array $favicon - a string of a firefly image id 
	 *   or an array containing the firefly image id inside 'id' property  
	 */
	public static function favicon($favicon)
	{
		$favicon = is_array($favicon) ? Arr::getRequired($favicon, 'id') : $favicon;
		
		// currently firefly cannot generate ico formats 
		// <link rel="shortcut icon" type="image/x-icon" href="{asset path="/images/fav/$fav/favicon.ico"}"> -->
		if (empty($favicon)) return '';
		$url = url('firefly/file/img');
		return <<<HTML
			<link rel="icon" type="image/png" sizes="192x192" href="$url?id=$favicon&w=192&h=192">
			<link rel="icon" type="image/png" sizes="32x32" href="$url?id=$favicon&w=32&h=32">
			<link rel="icon" type="image/png" sizes="96x96" href="$url?id=$favicon&w=96&h=96">
			<link rel="icon" type="image/png" sizes="16x16" href="$url?id=$favicon&w=16&h=16">
			<link rel="apple-touch-icon" sizes="57x57" href="$url?id=$favicon&w=57&h=57">
			<link rel="apple-touch-icon" sizes="60x60" href="$url?id=$favicon&w=60&h=60">
			<link rel="apple-touch-icon" sizes="76x76" href="$url?id=$favicon&w=76&h=76">
			<link rel="apple-touch-icon" sizes="120x120" href="$url?id=$favicon&w=120&h=120">
			<link rel="apple-touch-icon" sizes="144x144" href="$url?id=$favicon&w=144&h=144">
			<link rel="apple-touch-icon" sizes="152x152" href="$url?id=$favicon&w=152&h=152">
			<meta name="msapplication-TileImage" content="$url?id=$favicon&w=144&h=144">
		HTML;
	}

	/**
	 * Return the Html for a custom button
	 *
	 * @param mixed $url  the URL the button should return to @see \yii\helpers\Url::to
	 * @param string $text  the button text
	 * @param string $icon  the fa icon to use (can omit the fa-)
	 * @param string $class  the class(es) to apply post class btn
	 * @param array $options  any additional options
	 *  - specify $options['icon']
	 * @return string Html for the custom button
	 */
	public static function buttonCustom($url, $text, array $options=[])
	{
		$options['class'] = 'btn '. Arr::get($options, 'class', '');
		$icon = Arr::remove($options, 'icon', '');
		return self::a("<i class=\"$icon\"></i> ".$text, $url, $options);
	}

	/**
	 * Return the Html for a standard edit button
	 *
	 * @param array|string $url @see \yii\helpers\Url::to
	 * @param string $text - button text
	 * @return string Html for an edit button
	 */
	public static function buttonEdit($url, $text='Edit', $options=[])
	{
		$options['icon'] = 'fa fa-pencil';
		$options['class'] = 'btn-default ' . Arr::get($options, 'class', '');
		return self::buttonCustom($url, $text, $options);
	}

	/**
	 * Return the HTML for a standard delete button
	 * Note delete often means soft delete (destroy means remove from the db entirely)
	 *
	 * @param mixed $url
	 * @see \yii\helpers\Url::to
	 * @return string Html
	 */
	public static function buttonDelete($url, $text='Delete', $options=[])
	{
		$options['icon'] = 'fa fa-trash';
		$options['class'] =  'btn-danger ' . Arr::get($options, 'class', '');
		return self::buttonCustom($url, $text, $options);
	}

	/**
	 * Return the HTML for a standard Add button
	 *
	 * @param mixed $url
	 * @param string $text button text
	 * @see \yii\helpers\Url::to
	 * @return string Html
	 */
	public static function buttonAdd($url, $text='Add', $options=[])
	{
		$options['icon'] = 'fa fa-plus';
		$options['class'] = 'btn-primary btn-add';
		return self::buttonCustom($url, $text, $options);
	}

	/**
	 * Return the HTML for a standard cancel button
	 *
	 * @param mixed $url
	 * @param string $text button text
	 * @return string Html
	 */
	public static function buttonCancel($url='', $text='Cancel', $options=[])
	{
		$options['icon'] = 'fa fa-arrow-left';
		$options['class'] = 'btn-default btn-cancel';
		return self::buttonCustom(Url::goBack($url), $text, $options);
	}

	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param bool $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	public static function gravatar($email, $s = '80', $d = 'mm', $r = 'g', $img = false, $atts = array())
	{
		$out = '//www.gravatar.com/avatar/';
		$out .= md5(strtolower(trim($email)));
		$out .= "?s=$s&d=$d&r=$r";
		if ($img) {
			$out = Html::img($out, $atts);
		}
		return $out;
	}

	/**
	 * @inheritdoc
	 */
	public static function checkbox($name, $checked = false, $options = [])
	{
		$checkbox = parent::checkbox($name, $checked, $options);
		return '<div class="checkbox">' . $checkbox . '</div>';
	}

	/**
	 * @inheritdoc
	 */
	public static function radio($name, $checked = false, $options = [])
	{
		$radio = parent::radio($name, $checked, $options);
		return '<div class="radio">' . $radio . '</div>';
	}

	/**
	 * Sanitise a value.
	 *
	 * @param mixed $value
	 * @param string $allowableTags - see striptags function for the correct format
	 * @return mixed $value
	 */
	public static function sanitise($value, $allowableTags = null)
	{
		profile_begin('Html::sanitise', 'html');
		static $tagsProcessed = [];
		if (!empty($allowableTags) && !in_array($allowableTags, $tagsProcessed)) {
			self::processTags($allowableTags, $tagsProcessed);
		}
		if (is_array($value)) {
			foreach ($value as $k => $v)
				$value[$k] = static::sanitise($v, $allowableTags);
		} else {
			if (!is_null($value)) {
				if (isset($tagsProcessed[$allowableTags])) {
					$tags = $tagsProcessed[$allowableTags];
					$value = str_replace($tags['return'], $tags['replace'], $value);
					$value = trim(strip_tags($value));
					$value = str_replace($tags['replace'], $tags['return'], $value);
				} else {
					$value = trim(strip_tags($value));
				}
			}
		}
		profile_end('Html::sanitise', 'html');
		return $value;
	}

	/**
	 * Purify a value allow for some html parameters to get through. These can
	 * be further restricted from the default by using $allowedTags
	 *
	 * Note: this is slow in comparison to sanitise and should be used only when
	 * you are expecting more complex HTML to be passed in e.g. wysiwyg
	 *
	 * @param mixed $value
	 * @param string $allowedTags - see striptags function for the correct format
	 *   If you set this to false, tags will not be stripped beyond the
	 *   default ones by HTML::purifier
	 * @return mixed $value
	 */
	public static function purify($value, $allowedTags = null)
	{
		profile_begin('Html::purify', 'html');
		if (is_array($value)) {
			foreach ($value as $k => $v)
				$value[$k] = static::purify($v, $allowedTags);
		} else {
			if (!is_null($value)) {
				if ($allowedTags === false) {
					$value = trim(HtmlPurifier::process($value, function($config) {
						$def = $config->getHTMLDefinition(true);
						$def->addAttribute('img', 'srcset', 'Text');
						$def->addAttribute('img', 'sizes', 'Text');
						$def->addAttribute('img', 'loading', 'Text');
					}));
				} else {
					$value = trim(HtmlPurifier::process(strip_tags($value, $allowedTags)));
				}
			}
		}
		profile_end('Html::purify', 'html');
		return $value;
	}

	/**
	 * Apply htmlentities recursively onto a value.
	 *
	 * @param mixed $value  the values to be encoded
	 * @param int $entities  which entities should be encoded - this is
	 *   a or'ed bit mask of required types (see htmlentities for details)
	 * @return string
	 */
	public static function encodeEntities($value, $entities = ENT_QUOTES)
	{
		if (is_array($value)) {
			foreach ($value as $k => $v)
				$value[$k] = static::encodeEntities($v, $entities);
		} else {
			$value = htmlentities($value ?? '', $entities);
		}
		return $value;
	}

	/**
	 * helper method to allow for the keeping of allowable tags in strip tags
	 * @param array $openingTags
	 * @param array $tagsProcessed
	 */
	protected static function processTags($openingTags, &$tagsProcessed)
	{
		// append the closing tags to the list and add a comma for splitting later
		$tagsStr = str_replace('>','>,',($openingTags.str_replace('<','</',$openingTags)));
		// create the replacement code with a highly unlikely set of strings
		$replaceStr = str_replace(['</', '<', '/>', '>'], ['__NEWICON_CLOSELT__', '__NEWICON_LT__', '__NEWICON_CLOSERT__', '__NEWICON_RT__'], $tagsStr);

		// now create the replacement and returned tags
		$replaceTags = explode(',', substr($replaceStr,0,-1)); // ignore trailing comma
		$returnTags = explode(',', substr($tagsStr,0,-1)); // ignore trailing comma
		$tagsProcessed[$openingTags] = ['replace'=>$replaceTags, 'return'=>$returnTags];
	}

	/**
	 * Wrap text in a highlight span tag with class neonBgHighlight
	 * This function will not break html tags when finding and replacing text
	 *
	 * @param string|array $search - a non html string or array of strings to search for
	 * @param string $html - html content on which to highlight the string
	 * @param string $empty - what to show if the field is empty
	 * @return string html
	 */
	public static function highlight($search, $html, $empty='')
	{
		if ($html === null)
			return neon()->formatter->nullDisplay;
		if ($html === '')
			return $empty;
		if (empty($search))
			return $html;

		profile_begin('Html::Highlight');
		// escape search and html entities as otherwise the loadHTML or appendXML
		// can blow up if there are < & > in the data
		if (!is_array($search))
			$search = [$search];
		try {
			foreach ($search as $s) {
				// now search and rebuild the data
				$dom = new DOMDocument('1.0', 'UTF-8');
				// suppress all warning!
				// loadHtml seems to ignore the LIBXML_ERR_NONE setting
				// we don;t care if we are not given perfectly formed html
				// Even if we have serious issues in the html `$dom->saveHtml` makes a good expected effort
				@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED + LIBXML_HTML_NODEFDTD + LIBXML_NONET + LIBXML_ERR_NONE);
				$xpath = new DOMXPath($dom);
				$s = htmlentities($s);
				$findStack = [];
				// Only operate a string find / replace on the content of html text nodes
				foreach ($xpath->query('//text()') as $node) {
					$f = $dom->createDocumentFragment();
					// appendXml is useless and will break easily when exposed to characters
					// preg_replace is a million times more robust....
					// So keep the dom parser happy by replacing its internal text with an md5 key
					$replace = preg_replace('/(' . preg_quote($s) . ')/i', '<span class="neonSearchHighlight">$1</span>', htmlentities($node->nodeValue));
					$key = '{[' . md5($replace) . ']}';
					$findStack[$key] = $replace;
					$f->appendXML($key);
					$node->parentNode->replaceChild($f, $node);
				}
				$html = $dom->saveHTML();
				// additional string to replace
				$findStack["\n"] = '';
				$findStack['<?xml encoding="utf-8" ?>'] = '';
				// replace keys with the results of the search and replace
				$html = str_replace(array_keys($findStack), array_values($findStack), $html);
			}
		} catch (Exception $e) {
			return $html;
		}
		profile_end('Html::Highlight');
		return $html;
	}

}
