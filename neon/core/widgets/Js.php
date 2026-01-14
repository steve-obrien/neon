<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 16/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\widgets;

use yii\base\Widget;
use \neon\core\web\View;

class Js extends Widget
{
	public $position = View::POS_READY;

	public function init()
	{
		parent::init();
		ob_start();
	}

	public function run()
	{
		$jsScript = ob_get_clean();
		// remove script tags
		$jsScript = str_replace(['<script>', '</script>'], '', $jsScript);
		$this->view->registerJs($jsScript, $this->position);
	}
}