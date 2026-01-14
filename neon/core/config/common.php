<?php
/**
 * How configuration works:
 * Configuration loads in this order:
 * - env ini is loaded in and globals set - you can access env.ini or environment variables inside config using the `env`
 * function for e.g.: env('NEON_DEBUG', false), this is useful for loading in API keys and secure tokens without adding them to the repository.
 * the env.ini file is a placeholder for the essentially variables that must be set - secure keys can also be set as environment variable on the target machine
 * or env.ini file created by a deployment process.
 * - the common config (this file)
 * - the application type specific config either a 'web' application or a 'console' application - ./neo commands
 * launch neon as a console application certain objects such as the http request object do not exist in this context
 * - your project specific common config `@root/config/common.php`
 * - your project specific application type config `@root/config/web.php` or `@root/config/console.php` - if it exists
 *
 *
 *
 * @see \neon\core\ApplicationWeb
 *
 * Available Aliases
 * =================
 *
 * Neon loads in some useful aliases which can be used in the configuration:
 * - "@neon" = DIR_NEON
 * - "@config" = DIR_CONFIG
 * - "@root" = DIR_ROOT
 * - "@runtime" = the runtime folder
 * - "@system" = the application system folder
 * - "@web" = the web public folder
 * - "@app" = this actually points to the "@neon" and is equivalent to basePath
 */
return [
	/**
	 * For developers, set whether to display debug information
	 * Note: this should be set to false in a production environment
	 */
	'debug' => env('NEON_DEBUG', false),
	/**
	 * Set the environment for this installation
	 * This can be one of the following values:
	 * 'dev', 'prod', 'test'
	 */
	'env' => env('NEON_ENV', 'prod'),

	'timeZone' => env('NEON_TIMEZONE', 'Europe/London'),

	'id' => 'Neon',

	'name' => 'Neon',

	'language' => 'en',
	'sourceLanguage' => 'en-GB',
	'basePath' => '@neon', // also becomes alias @app
	'vendorPath' => NEON_VENDOR,
	'runtimePath' => '@var/runtime', // location of runtime folder

	'bootstrap' => ['log'],
	'controllerNamespace' => 'neon\\core\\controllers',

	'aliases' => [
		// ensure yii assets point to correct path installed by composer
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		// handle legacy aliases
		'@neon/dds' => '@neon/daedalus'
	],

	'layout' => 'main',
	// Module / app loader will supply these settings
	// Define core apps that cannot be installed / uninstalled here
	'coreApps' => [
		'core' => [
			'class' => '\neon\core\App',
			'classConfig' => [] // used to pass config data through to e.g. forms
		],
		'install' => [
			'class' => '\neon\install\App',
		],
		'cms' => [
			'class' => '\neon\cms\App',
			'requires' => 'daedalus'
		],
		'daedalus' => [
			'class' => '\neon\daedalus\App',
			'appAliases' => ['dds'],
			'requires' => 'core'
		],
		'user' => [
			'class' => '\neon\user\App',
			'roles' => [
				// routes are either set by extending from AdminController or defined here
				'neon-administrator' => [
					'label' => 'Neon Administrator',
					'routes' => [ '^/admin/.*' ],
					'homeUrl' => '/admin'  // default url if not going elsewhere
				],
				'neon-developer' => [
					'label' => 'Neon Developer',
					'routes' => [ '^/admin/.*' ],
					'homeUrl' => '/admin'  // default url if not going elsewhere
				],
			]
		],
		'admin' => [
			'class' => '\neon\admin\App',
			'components' => [
				'menuManager' => [
					'class' => 'neon\admin\services\menu\Manager'
				]
			]
		],
		'settings' => [
			'class' => '\neon\settings\App',
		],
		'phoebe' => [
			'class' => '\neon\phoebe\App',
			'requires' => 'daedalus'
		],
		'dev' => [
			'class' => '\neon\dev\App',
			//'enabled' => env('NEON_DEBUG'),
		],
		'firefly' => [
			'class' => \neon\firefly\App::class,
			'cache' => 'cacheFirefly',
			'components' => [
				'IMediaManager' => [
					'class' => '\neon\firefly\services\ImageManager'
				],
				'IFileManager' => [
					'class' => '\neon\firefly\services\FileManager',
					'defaultDrive' => 'media',
				],
				'driveManager' => [
					'class' => '\neon\firefly\services\DriveManager',
					/*
					|--------------------------------------------------------------------------
					| Default Filesystem Drive
					|--------------------------------------------------------------------------
					|
					| Here you may specify the default drive that should be used
					| by the framework. This moust point to a configured drive defined in the
					| `drives` property
					|
					*/
					'default' => 'media',

					/*
					|--------------------------------------------------------------------------
					| Filesystem Drives
					|--------------------------------------------------------------------------
					|
					| Here you may configure as many filesystem "drives" as you wish, and you
					| may even configure multiple disks of the same driver. Defaults have
					| been setup for each driver as an example of the required options.
					|
					*/
					'drives' => [
						'backup' => [
							'driver' => 'local',
							'root' => '@var/storage/backup',
						],
						'media' => [
							'driver' => 'local',
							'root' => '@var/storage/media',
							'visibility' => 'public',
							'permissions' => [
								'file' => [
									'public' => 0664,
									'private' => 0600,
								],
								'dir' => [
									'public' => 0775,
									'private' => 0700,
								]
							]
						],
					]
				],
				'fileManager' => [
					'class' => '\neon\firefly\services\FileManager',
					'defaultDrive' => 'media',
				],
				'mediaManager' => [
					'class' => '\neon\firefly\services\MediaManager',
					'rootPath' => '/'
				]
			]
		],
		'utilities' => [
			'class' => '\neon\utilities\App',
		],
	],
	'components' => [
		// the specific connection details are merged with the db config.
		// the local config will override settings here, so these are sensible defaults.
		'db' => [
			'dsn' => 'mysql:host='.env('DB_HOST', '127.0.0.1').';port='.env('DB_PORT', '3306').';dbname='.env('DB_NAME'),
			'username' => env('DB_USER', 'root'),
			'password' => env('DB_PASSWORD', 'root'),
			'tablePrefix' => env('DB_TABLE_PREFIX', ''),
			'charset' => 'utf8mb4',
			'class' => 'neon\core\db\Connection',
			'enableSchemaCache' => true, // yii default
			'schemaCacheDuration' => 3600, // yii default
			'schemaCache' => 'cacheSchema',
			'enableQueryCache' => true, // yii default
			'queryCacheDuration' => 3600, // yii default
			'queryCache' => 'cacheQuery',
			'attributes' => [
				PDO::ATTR_PERSISTENT => true
			]

		],
		'formatter' => [
			'class' => 'neon\core\services\Formatter',
			// where as the neon()->timeZone can be modified
			// this defaultTimeZone assumed
			'defaultTimeZone' => env('NEON_TIMEZONE', 'Europe/London'),
		],
		'i18n' => [
			'class' => 'yii\i18n\I18N',
			'translations' => [
				'neon' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@system/i18n/messages',
				]
			]
		],
		// Cache components:
		'cache' => [
			'class' => 'yii\caching\FileCache',
			'cachePath' => '@runtime/cache/general',
			'serializer' => (extension_loaded('igbinary')) ? ['igbinary_serialize', 'igbinary_unserialize'] : null
		],
		'cacheFirefly' => [
			'class' => yii\caching\FileCache::class,
			'cachePath' => '@runtime/cache/firefly',
			'serializer' => (extension_loaded('igbinary')) ? ['igbinary_serialize', 'igbinary_unserialize'] : null,
			// never run garbage collection - we keep firefly cache forever.
			// The cache scales at the same rate as number of images uploaded.
			'gcProbability' => 0
		],
		'cacheSchema' => [
			'class' => yii\caching\FileCache::class,
			'cachePath' => '@runtime/cache/db/schema',
			'serializer' => (extension_loaded('igbinary')) ? ['igbinary_serialize', 'igbinary_unserialize'] : null
		],
		'cacheQuery' => [
			'class' => 'yii\caching\FileCache',
			'cachePath' => '@runtime/cache/db/query',
			'serializer' => (extension_loaded('igbinary')) ? ['igbinary_serialize', 'igbinary_unserialize'] : null
		],
		// A PHP in memory array cache - caches items inside a php array created for the request
		'cacheArray' => [
			'class' => 'yii\caching\ArrayCache',
			// there is no need to serialize values stored in this cache
			// as they are stored in a php array
			'serializer' => false
		],
		'user' => [
			'class' => '\neon\user\services\User',
			'identityClass' => 'neon\user\models\User',
			'identityCookie' => ['name' => 'neon_identity', 'httpOnly' => true],
			'loginUrl' => ['user/account/login'],
			// Number of seconds the session will remain valid if the remember me option is set
			'rememberMeDuration' => 3600 * 24 * 30,
			'enableAutoLogin' => true,
		],
		// neon sets up a session table by default: 
		// you have to add this to your common config:
		'session' => [
			// 'class' => '\neon\user\web\DbSession',
			// 'sessionTable' => 'user_session',
		],
		'authManager' => [
			'class' => '\yii\rbac\DbManager',
			'itemTable' => '{{%user_auth_item}}',
			'itemChildTable' => '{{%user_auth_item_child}}',
			'assignmentTable' => '{{%user_auth_assignment}}',
			'ruleTable' => '{{%user_auth_rule}}'
		],
		'mailer' => [
			'class' => neon\core\mail\Mailer::class,
			'viewPath' => '@neon/core/mail',
			// send all emails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => env('NEON_DEBUG'),
			// string the directory where the email messages are saved when [[useFileTransport]] is true.
			'fileTransportPath' => '@runtime/mail',
			// for mailgun:
			// 'transport' => [
			//     'dsn' => 'mailgun+https://'. env('MAILGUN_KEY').':mg.newicon.net@api.eu.mailgun.net'
			// ],
		],
		'log' => [
			'traceLevel' => 0,
			'targets' => [
				'error' => [
					'class' => 'yii\log\FileTarget',
					'levels' => ['error'],
					// To disable 404 logs:
					'except' => ['yii\web\HttpException:404'],
					'logFile' => '@runtime/logs/error.log',
					'maxFileSize' => 1024 * 2,
					'maxLogFiles' => 20,
					'logVars' => ['_GET', '_POST', '_FILES', '_SESSION', '_SERVER'],
					'maskVars' => ['_SERVER.HTTP_AUTHORIZATION','_SERVER.PHP_AUTH_USER','_SERVER.PHP_AUTH_PW','_SERVER.DB_PASSWORD','_SERVER.NEON_SECRET','_SERVER.NEON_ENCRYPTION_KEY', '_SERVER.HTTP_COOKIE']
				],
				'warning' => [
					'class' => 'yii\log\FileTarget',
					'levels' => ['warning'],
					'logFile' => '@runtime/logs/warning.log',
					'maxFileSize' => 1024 * 2,
					'maxLogFiles' => 20,
					'logVars' => ['_GET', '_POST', '_FILES', '_SESSION', '_SERVER'],
					'maskVars' => ['_SERVER.HTTP_AUTHORIZATION','_SERVER.PHP_AUTH_USER','_SERVER.PHP_AUTH_PW','_SERVER.DB_PASSWORD','_SERVER.NEON_SECRET','_SERVER.NEON_ENCRYPTION_KEY']
				],
				'trace' => [
					// to enable trace logging set ['components']['log']['targets']['trace']['enabled'] to true in the app config
					'enabled' => false,
					'class' => 'yii\log\FileTarget',
					'levels' => ['trace'],
					'logFile' => '@runtime/logs/debug.log',
					'maxFileSize' => 1024 * 2,
					'maxLogFiles' => 20,
					'logVars' => ['_GET', '_POST'],
					'maskVars' => []

				],
			],
		],
		'assetManager' => [
			'class' => '\neon\core\web\AssetManager',
			'linkAssets' => (env('NEON_ENV') == 'dev'),
			'appendTimestamp' => true,
		],
		'security' => [
			'cipher' => 'AES-256-CBC'
		],
		'view' => [
			'class' => 'neon\core\web\View',
			// Whether css files should be concatenated
			'cssConcatenate' => false,
			// Whether to process the css for variables using the registered css renderer
			// currently only applicable id cssConcatenate is true
			'cssProcess' => false,
			// whether to minify the css content
			// It may be useful to set this to false in development mode
			// applies to css blocks and only to concatenated file
			'cssMinify' => true,
			// Whether to concatenate js files
			'jsConcatenate' => false,
			'theme' => [
				'class' => 'neon\core\view\Theme',
				'basePath' => '@neon/core/themes/neon',
				'pathMap' => [
					'@neon' => '@neon/core/themes/neon',
					'@app/views' => '@neon/core/themes/neon',
				],
			],
			'renderers' => [
				// smarty
				'tpl' => [
					'class' => '\neon\core\view\SmartyRenderer',
					/**
					 * @var string the directory or path alias pointing to where Smarty cache will be stored.
					 */
					'cachePath' => '@runtime/smarty/cache',
					/**
					 * @var string the directory or path alias pointing to where Smarty compiled templates will be stored.
					 */
					'compilePath' => '@runtime/smarty/compile',
					/**
					 * @var array Add additional directories to Smarty's search path for plugins.
					 * 'pluginDirs' => ['my/dir'],
					 */
					/**
					 * @var array Class imports similar to the use tag
					 */
					'imports' => [],
					/**
					 * @var array Widget declarations
					 */
					'widgets' => ['functions' => [], 'blocks' => []],
					/**
					 * @var string extension class name
					 */
					'extensionClass' => '\neon\core\view\SmartyExtension',
					/**
					 * @var array additional Smarty options
					 * @see http://www.smarty.net/docs/en/api.variables.tpl
					 */
					'options' => [
						// Compile check will check the modified time of the source template file
						// if it has been modified after it was compiled it will recompile
						// for performance we can turn this off in production
						'compile_check' => env('NEON_DEBUG'),
						// Forces smarty to recompile its templates every time.
						// useful for development
						'force_compile' => env('NEON_DEBUG')
					],
				],
				'css' => [
					'class' => '\neon\core\view\SmartyRenderer',
					'cachePath' => '@runtime/Smarty/css/cache',
					'compilePath' => '@runtime/Smarty/css/compile',
					'imports' => [],
					'widgets' => ['functions' => [], 'blocks' => []],
					'extensionClass' => '\neon\core\view\SmartyExtension',
					/**
					 * @var array additional Smarty options
					 * @see http://www.smarty.net/docs/en/api.variables.tpl
					 */
					'options' => [
						'escape_html' => true,
						'left_delimiter' => '[[',
						'right_delimiter' => ']]',
						// Compile check will check the modified time of the source template file
						// if it has been modified after it was compiled it will recompile
						// for performance we can turn this off in production
						'compile_check' => env('NEON_DEBUG'),
						// Forces smarty to recompile its templates every time.
						// useful for development
						'force_compile' => env('NEON_DEBUG')
					],
				],
				// markdown renderer
				'md' => [
					'class' => '\neon\core\view\MarkdownRenderer',
				]
			],
		],
	]
];
