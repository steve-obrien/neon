<?php
/**
 * Web Application specific config
 * -------------------------------
 * Note you can override and extend this by creating a web.php file inside the project config folder
 * merged into common.php
 * merge(common, web, local)
 */
$config = [
	'defaultRoute' => 'core/site',
	// enable the redactor widget
	'components' => [
		'request' => [
			'cookieValidationKey' => neon_encryption_key(),
			'enableCookieValidation' => true,
			'enableCsrfValidation' => true,
			'csrfParam' => 'neon_csrf',
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			]
		],
		'response' => [
			'class' => 'neon\core\web\Response',
			'formatters' => [
				'json' => 'neon\core\web\JsonResponseFormatter',
				'php' => 'neon\core\web\PhpResponseFormatter',
			]
		],
		'urlManager' => [
			'class' => 'neon\core\web\UrlManager',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'normalizer' => [
				'class' => 'yii\web\UrlNormalizer',
				'collapseSlashes' => true,
				'normalizeTrailingSlash' => true,
			]
		],
		'errorHandler' => [
			'class' => 'neon\core\web\ErrorHandler',
			/**
			 * This is used to generate an open link so error lines can be opened from the browser
			 * The default is vscode (<a href="vscode:file/path/to/file.php:100">)
			 */
			'ide' => 'vscode',
			/**
			 * @var string the route (e.g. `error/error`) to the controller action that will be used
			 * to display external errors. Inside the action, it can retrieve the error information
			 * using `Yii::$app->errorHandler->exception`. This property defaults to null, meaning ErrorHandler
			 * will handle the error display.
			 */
			'errorAction' => '/core/error/error',
			/**
			 * @var integer the size of the reserved memory. A portion of memory is pre-allocated so that
			 * when an out-of-memory issue occurs, the error handler is able to handle the error with
			 * the help of this reserved memory. If you set this value to be 0, no memory will be reserved.
			 * Defaults to 256KB.
			 */
			'memoryReserveSize' => 262144,
			/**
			 * @var string the path of the view file for rendering exceptions without call stack information.
			 */
			'errorView' => '@yii/views/errorHandler/error.php',
			/**
			 * @var string the path of the view file for rendering exceptions.
			 */
			'exceptionView' => '@yii/views/errorHandler/exception.php',
			/**
			 * @var string the path of the view file for rendering exceptions and errors call stack element.
			 */
			'callStackItemView' => '@neon/core/views/errorHandler/callStackItem.php',
			/**
			 * @var string the path of the view file for rendering previous exceptions.
			 */
			'previousExceptionView' => '@yii/views/errorHandler/previousException.php'
		],
	]
];
return $config;
