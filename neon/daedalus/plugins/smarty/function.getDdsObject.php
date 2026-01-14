<?php

use \http\Exception\InvalidArgumentException;

/**
 * Get hold of a dds object and convert it to JSON output.
 * @param $params
 *   'uuid' - the object UUID
 *   'assign' - optional - the parameter to pass back to the template
 *     defaults to ddsObject
 *   'asJson' - set to true if you want the data returned as json otherwise will be php array.
 * @param $template
 */
function smarty_function_getDdsObject($params, $template)
{
	if (!isset($params['uuid']))
		throw new InvalidArgumentException("DdsObjectToJson - you need to pass in the 'uuid'");
	$assign = !empty($params['assign']) ? $params['assign'] : 'ddsObject';
	$asJson = isset($params['asJson']) ? $params['asJson'] : false;
	$dds = neon('dds')->IDdsObjectManagement;
	$object = $dds->getObject($params['uuid']);
	$template->assign($assign, $asJson ? json_encode($object) : $object);
}