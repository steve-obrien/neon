<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 12/07/2018
 * @package neon
 */
// set globals before Yii does!
defined('YII_ENV') or define('YII_ENV', env('NEON_ENV', 'dev'));
defined('YII_DEBUG') or define('YII_DEBUG', env('NEON_DEBUG', true));
// define a start variable used when determining execution time @see neon()->getExecutionTime()
defined('NEON_START') or define('NEON_START', microtime(true));

require __DIR__.'/Neon.php';

/**
 * Override the default Yii class
 * This allows us to override and extend Yii static functions in Neon
 */
class Yii extends Neon
{
}

spl_autoload_register(['Yii', 'autoload'], true, true);
// Ability to replace any yii core class with an alternative
Yii::$classMap = require YII2_PATH . '/classes.php';
Yii::$container = new yii\di\Container();
