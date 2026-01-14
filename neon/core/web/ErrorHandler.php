<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/12/2016 19:04
 * @package neon
 */

namespace neon\core\web;

/**
 * Class Controller
 * @package neon\core\web
 *
 * @method \neon\core\web\View getView()
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
     /**
     * @var string the path of the view file for rendering exceptions and errors call stack element.
     */
    public $callStackItemView = '@neon/core/views/errorHandler/callStackItem.php';

    /**
     * IDE to open error lines in your favorite IDS
     * will create a link like <a href="vscode://file/path/to/php/file.php:100
     */
    public $ide = 'vscode';
}