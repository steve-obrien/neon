<?php
/**
 * Console application specific config
 * merged into common.php
 * merge(common, app_console, env_*, local)
 */
$config = [
	'id' => 'console',
	'bootstrap' => ['log'],
	'defaultRoute' => 'help',
	'controllerNamespace' => 'neon\core\console\controllers',
	'components' => [
		'db' => [
			'enableSchemaCache' => true,
			'schemaCacheDuration' => 3600,
		],
		// override the request property
		'request' => [
			'class' => '\yii\console\Request',
		],
		'errorHandler' => [
			'class' => '\yii\console\ErrorHandler',
		],
		// the user module could define this on boot.
		// but this is useful if you need to make any project specific adjustments
		'session' => [ // for use session in console application
			'class' => 'yii\web\DbSession'
		],
		'assetManager'=>[
			// so we can use the assetManager in console - we will guess the url path.
			// this default is @web/assets - tat does not exist in console mode.
			// Yii's web application sets the @web alias on bootstrap using the request object.
			'baseUrl' => '/assets'
		]
	],
];
if (env('NEON_DEBUG')) {
	//$config['bootstrap'][] = 'gii';
	$config['modules'] = [
		//'gii' => ['class' => 'yii\gii\Module'],
	];
}
return $config;
