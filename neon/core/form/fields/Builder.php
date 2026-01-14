<?php


namespace neon\core\form\fields;


class Builder extends Ide
{
	public $ddsDataType = 'json';

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'group' => "Experimental",
			'icon' => 'fa fa-info',
			'order' => 921
		];
	}
}