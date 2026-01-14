<?php
/**
 * The start up script to load the neon web app in test mode.
 */

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Access Forbidden.');
}

ini_set('max_execution_time', 300);
ini_set('memory_limit', '128M');

defined('DIR_TEST') or define('DIR_TEST', dirname(dirname(__DIR__)));
// fake top level DIR_ROOT for tests - is the _root directory
defined('DIR_ROOT') or define('DIR_ROOT', DIR_TEST . '/_root');
// load the vendor includes
defined('NEON_VENDOR') or define('NEON_VENDOR', dirname(DIR_TEST) . '/vendor');
require NEON_VENDOR . '/autoload.php';
// include c3 codeception code coverage logging for remote code coverage
include './c3.php';

\neon\core\Env::setEnvironmentFromFile(DIR_ROOT.'/config/env.ini', true);
$neon = \neon\core\Env::createWebApplication();
$neon->registerAllApps();
$neon->run();