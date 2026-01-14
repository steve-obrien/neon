<?php


namespace neon\core\view;

/**
 * Interface IFlushTemplatesInterface
 * Enables view renderers to have their templates flushed via console and GUI
 * ./neo core/flush-templates
 * @package neon\core\web
 */
interface ICanFlushTemplates
{
	/**
	 * clears cached compiled templates
	 * @throws \yii\base\ErrorException - if something goes wrong with clearing the cache
	 */
	public function flushTemplates();
}
