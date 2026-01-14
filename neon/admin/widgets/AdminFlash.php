<?php

namespace neon\admin\widgets;

use yii\base\Widget;

class AdminFlash extends Widget
{
	/**
	 * @var array the alert types configuration for the flash messages.
	 * This array is setup as $key => $value, where:
	 * - $key is the name of the session flash variable
	 * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
	 */
	public $alertTypes = [
		'error'   => 'alert-danger',
		'danger'  => 'alert-danger',
		'success' => 'alert-success',
		'info'	=> 'alert-info',
		'warning' => 'alert-warning'
	];

	public function run()
	{
		return $this->render('admin-flash', [
			'flashes' => neon()->session->getAllFlashes(),
			'alertTypes' => $this->alertTypes
		]);
	}
}
