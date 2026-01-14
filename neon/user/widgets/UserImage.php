<?php

namespace neon\user\widgets;

use yii\base\Widget;
use neon\core\helpers\Html;

class UserImage extends Widget
{
	/**
	 * The image size to display
	 * @var int 
	 */
	public $size = 40;
	
	public function run()
	{

		$email = \Neon::$app->user->getEmail();
		return neon()->user->getImageUrl();
	}
}