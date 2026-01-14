<?php
namespace neon\core\view;

use neon\core\view\ICanFlushTemplates;
use yii\helpers\FileHelper;

class TwigRenderer extends \yii\twig\ViewRenderer implements ICanFlushTemplates
{
	/**
	 * @inhertidoc
	 * @throws \yii\base\ErrorException - if something goes wrong with the file directory operation
	 */
	public function flushTemplates()
	{
		FileHelper::removeDirectory($this->cachePath);
	}
}
