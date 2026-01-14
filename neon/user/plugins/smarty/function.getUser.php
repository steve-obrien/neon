<?php

/**
 * Get hold of the \neon\user\interfaces\iUser interface
 * for the current user
 * @param array $params
 *   assign => the object name you want to set the user to
 * @param object $template
 *   the smarty template object
 */
function smarty_function_getUser($params, $template)
{
	$assign = isset($params['assign'])?$params['assign']:'user';
	$template->assign($assign, neon()->user);
}
