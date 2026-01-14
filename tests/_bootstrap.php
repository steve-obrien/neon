<?php
///*
//| --------------------------------------------------------------------------------------------------
//| Bootstrap the necessary application variables - performs the same job as the root application bootstrap
//| --------------------------------------------------------------------------------------------------
//*/
//defined('NEON_START') or define('NEON_START', microtime(true));

/*
| --------------------------------------------------------------------------------------------------
| Load in core framework test helper functions
| --------------------------------------------------------------------------------------------------
*/
require_once dirname(__DIR__) . '/neon/core/test/helpers.php';
/*
|------------------------------------------------------------------------------
| Define the test directory path
|------------------------------------------------------------------------------
*/
defined('DIR_TEST') or define('DIR_TEST', __DIR__);

/*
|------------------------------------------------------------------------------
| Define the vendor directory path
|------------------------------------------------------------------------------
*/
defined('NEON_VENDOR') or define('NEON_VENDOR', dirname(DIR_TEST) . '/vendor');

/*
|------------------------------------------------------------------------------
| Define the root directory path
|------------------------------------------------------------------------------
*/
defined('DIR_ROOT') or define('DIR_ROOT', DIR_TEST . '/_root');

/*
|------------------------------------------------------------------------------
| Register The Auto Loader
|------------------------------------------------------------------------------
|
| Composer provides a auto generated class loader for
| our application. This takes care of loading classes for us.
|
*/
require NEON_VENDOR . '/autoload.php';

// Here you can initialize variables that will be available to your tests
use \neon\core\Env;
/*
| --------------------------------------------------------------------------------------------------
| For more control of before and after test suit execution see:
| tests/_support/Helper/[Api|Acceptance|Unit] and _beforeSuite and _afterSuite functions
| --------------------------------------------------------------------------------------------------
*/

// Set up a console application

/*
| --------------------------------------------------------------------------------------------------
| Loading in environment variables from a file does NOT overwrite pre existing environment variables
| as we want to ensure we are using a different database for the tests lets set this variable first.
| Note this will first check if you have explicitly set the environment variable 'DB_NAME' for e.g.
| `export DB_NAME="my_test"`
| If not, it sets it to 'neon_test'
| --------------------------------------------------------------------------------------------------
*/
// default start database settings
// you can overwrite these by setting DB_USER and DB_PASSWORD environment variables
// The test framework initially creates a neon_test database and a specific user
Env::setEnvironmentVariable('DB_USER', env('DB_USER', 'root'));
Env::setEnvironmentVariable('DB_PASSWORD', env('DB_PASSWORD', 'root'));
// Always overwrite the db name
Env::setEnvironmentVariable('DB_NAME', env('TEST_DB_NAME', 'neon_test'), true);


/*
| --------------------------------------------------------------------------------------------------
| Now we have set the database env variable it is safe to attempt to load in the project specific
| environment variables (pre existing variables like DB_NAME set above will NOT be overwritten)
| Load in remaining environment variables
| All variables inside the env.ini can be overwritten if environment variables already exist
| --------------------------------------------------------------------------------------------------
*/
//Env::setEnvironmentFromFile(DIR_TEST.'/_root/config/env.ini');
/*
| --------------------------------------------------------------------------------------------------
| Load the neon console application
| --------------------------------------------------------------------------------------------------
*/
// We need to clear any cache before loading the application
\neon\core\helpers\File::removeDirectory(DIR_TEST . '/_root/var');
// clear system dir
\neon\core\helpers\File::removeDirectory(DIR_TEST . '/_root/system');

// create the neon console application, global calls to neon() will return this object
$neon = Env::createConsoleApplication(['components' => ['db' => ['enableSchemaCache' => false]]]);
/*
| --------------------------------------------------------------------------------------------------
| Enable us to autoload tests via the \neon\tests namespace
| --------------------------------------------------------------------------------------------------
| A namespace such as \neon\tests\unit\daedalus\MyClass
| will points to \neon-root\tests\unit\daedalus\MyClass
*/
Neon::setAlias('@neon/tests', __DIR__);