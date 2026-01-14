<?php

namespace neon\core\view;


class Theme extends \yii\base\Theme
{
	public function getBaseUrl()
	{
		$results = neon()->assetManager->publish($this->getBasePath().'/assets');
		return $results[1];
	}
}