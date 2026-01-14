<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/04/2020
 * @package neon
 */

namespace neon\cms\form\fields;

use \neon\core\form\fields\Field;
use neon\core\form\fields\ImageMultiple;

class ImageCompare extends ImageMultiple
{
    /**
	 * @inheritdoc
	 */
    public $ddsDataType = 'json';

    /**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
        $view->registerAssetBundle(\neon\cms\assets\CmsFormAsset::class);
		$view->registerAssetBundle(\neon\firefly\assets\BrowserAsset::class);
    }

    /**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'neon\\core\\form\\fields\\Text'];
	}

    /**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'icon' => 'fa fa-image', 'group' => 'Media', 'order' => 612, 'label' => 'Image Compare'
		];
    }

    public function getValueDisplay($context='')
	{
		$json = $this->getValue();
		if (empty($json))
			return neon()->formatter->asJson($this->getValue());
		if ($context==='grid') {
			$out = '';
			foreach($json as $image) {
				$url = neon()->firefly->getImage($image);
				$out .= "<img src='$url' />";
			}
			return $out;
		}
		return neon()->formatter->asJson($this->getValue());
	}

}