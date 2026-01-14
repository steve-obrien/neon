<?php

namespace neon\core\helpers;

use HTMLPurifier_Config;
use yii\helpers\BaseHtmlPurifier;

class HtmlPurifier extends BaseHtmlPurifier
{
	/**
	 * Extend config for processing
	 *
	 * @param HTMLPurifier_Config $config
	 */
	protected static function configure($config)
	{
		parent::configure($config);
		$def = $config->getHTMLDefinition(true);
		$def->addAttribute('a', 'target', 'Text');
		$def->addAttribute('img', 'srcset', 'Text');
		$def->addAttribute('img', 'sizes', 'Text');
		$def->addAttribute('img', 'loading', 'Text');
	}
}