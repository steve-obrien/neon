<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 07/12/2016 13:34
 * @package neon
 */

namespace neon\user\web;

/**
 * Class DbSession - Add extra data to the session table
 * @package neon\user\web
 */
class DbSession extends \yii\web\DbSession
{
	protected function composeFields($id=null, $data=null)
	{
		// NOTE: do not interact with the identity inside this call
		// this will trigger a "session_regenerate_id(): Cannot call session save handler in a recursive manner" error
		// Do not use methods on neon()->user that invoke calls to the identity class
		// the neon()->user->identity->getIdentity() function will try to renewAuthentication, and most likely,
		// that is where we have just come from.
		$fields = parent::composeFields($id, $data);
		// get additional data to store in the session
		$fields['user_uuid'] = isset($_SESSION[neon()->user->idParam]) ? $_SESSION[neon()->user->idParam] : null;
		$fields['ip_address'] = neon()->request->getUserIP();
		$fields['last_active'] = date('Y-m-d H:i:s');
		$fields['user_agent'] = neon()->request->getUserAgent();
		$fields['referrer'] = neon()->request->getReferrer();
		$fields['current_url'] = neon()->request->getAbsoluteUrl();
		return $fields;
	}
}