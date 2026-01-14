<?php
namespace neon\core\view;

use \yii\base\ViewRenderer;

class MarkdownRenderer extends ViewRenderer
{
	public function render($view, $file, $params)
	{
		$contents = file_get_contents($file);
		$parser = new MarkdownNeon();
		return $parser->parse($contents);
	}
}