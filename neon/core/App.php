<?php

namespace neon\core;

use yii\base\ActionEvent;

/**
 * The neon core admin app class
 */
class App extends \neon\core\BaseApp
{

	/**
	 * Use this to add additional global config for classes within core e.g. form fields, grids etc.
	 * The config can be set in the application configuration as so
	 * For example, setting additional properties for a class XXX
	 * ```php
	 * 'coreApps' => [
	 *     'core' => [
	 *         'classConfig' => [
	 *             'FULL_NAMESPACE_PATH_TO_XXX' => [
	 *                 'property' => 'value'
	 *             ]
	 *         ],
	 *     ...
	 * ```
	 * For example for the wysiwyg form field
	 * ```php
	 * 'coreApps' => [
	 *     'core' => [
	 *         'classConfig' => [
	 *             '\neon\core\form\fields\Wysiwyg' => [
	 *                 'configSets' => [....]
	 *             ]
	 *         ],
	 *         ...
	 * ```
	 *
	 * To get this config within the class use
	 * ```php
	 * neon('core')->classConfig
	 * ```
	 * and then check for your class namespace to see if you have config e.g. for the Wysiwyg
	 * ```php
	 * \Arr::get(neon('core')->classConfig, '\neon\core\form\fields\Wysiwyg.configSets', []);
	 * ```
	 * @var array
	 */
	public $classConfig = [];

	public function configure()
	{
	}


	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		neon()->name = setting('core', 'site_name', neon()->name);


		// set up rules
//		neon()->urlManager->addRules([
//			'/core/api/map/providers' => '/core/api/map/providers',
//			'/core/api/map/providers/<provider>/<map>' => '/core/api/map/get',
//		]);
//		neon()->urlManager->addRules([
//			'/css/<file>' => '/core/css/asset',
//		]);
//		neon()->urlManager->addRules([
//			'/assets/css/<style>/<key:(.*)>.css' => '/core/css/asset',
//		]);

	}


	public function getName()
	{
		return 'Core';
	}

	public $controllerMap = ['serve' => 'yii\console\controllers\ServeController'];

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		return [
			'site_name' => [
				'label' => 'Site name',
				'hint' => 'Set the default name of this application'
			],
			'openai' => [
				'class' => 'neon\core\form\fields\Password',
				'checkPasswordDatabases' => false,
				'label' => 'OpenAI Key',
			],
			'global_password_enabled' => [
				'label' => 'Global Password',
				'hint' => 'Add a global password to all pages - this is useful for live previews.',
				'class' => 'neon\core\form\fields\SwitchButton'
			],
			'global_password' => [
				'label' => '',
				'hint' => 'The password',
				'class' => 'neon\core\form\fields\Password',
				'showIf' => ['global_password_enabled', '=', true]
			],
			'global_password_holding_page' => [
				'label' => '',
				'hint' => 'Holding Page',
				'class' => 'neon\core\form\fields\Text',
				'showIf' => ['global_password_enabled', '=', true]
			],
		];
	}

}
