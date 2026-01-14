<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/04/2020
 * @package neon
 */


use \http\Exception\InvalidArgumentException;

/**
 * Output links an object has
 * @param $params
 *   'uuid' - the object UUID
 *   'class' - the daedalus class type
 * @param $template
 */
function smarty_function_getLinked($params, $template)
{
	$uuid = \neon\core\helpers\Arr::getRequired($params, 'uuid');
	$class = \neon\core\helpers\Arr::getRequired($params, 'class');
	$links = \neon\daedalus\services\ddsManager\models\DdsMember::findAll(['link_class' => $class]);
	$linksToTables = count($links);

	// output links an object has
}