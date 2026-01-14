<?php

namespace neon\admin;

/**
 * The noen core admin app class
 */
class App extends \neon\core\BaseApp
{
	public function getDefaultMenu()
	{
		return [
			'label' => 'Dashboard',
			'order' => 0,
			'url' => ['/admin/index/index'],
			'visible' => neon()->user->is('neon-administrator')
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [
			'fromEmailAddress' => [
				'class' => '\neon\core\form\fields\Text',
				'label' => 'From Email Address',
				'hint' => 'Set the from email address for emails sent from the system',
				'validators' => [
					['class'=>'email'],
					['class'=>'required']
				],
				'placeholder' => 'e.g. robot@' . strtolower(setting('core', 'site_name', neon()->name)) . '.com'
			],
			'fromEmailName' => [
				'name' => 'fromEmailName',
				'class' => '\neon\core\form\fields\Text',
				'label' => 'From Email Name',
				'hint' => 'Set the from email name to show for emails sent from the system'
			],
			'sslOnly' => [
				'name' => 'sslOnly',
				'class' => '\neon\core\form\fields\SwitchButton',
				'label' => 'Force SSL (https) everywhere',
				'hint' => 'The application will redirect all requests to secure https requests. ' .
					(neon()->isDevMode() ? 'Note: You are currently in dev mode so the redirects to https will not happen' : '')
			],
		];
	}
}
