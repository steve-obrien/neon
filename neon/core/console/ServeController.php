<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\console;

use yii\console\controllers\ServeController as YiiServeController;

/**
 * Runs PHP built-in web server
 *
 * In order to access server from remote machines use 0.0.0.0:8000. That is especially useful when running server in
 * a virtual machine.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.7
 */
class ServeController extends YiiServeController
{
    /**
     * @var int port to serve on.
     */
    public $port = 8080;
    /**
     * @var string path or path alias to directory to serve
     */
    public $docroot = '@root/public';
    /**
     * @var string path to router script.
     * See https://secure.php.net/manual/en/features.commandline.webserver.php
     */
    public $router;

}
