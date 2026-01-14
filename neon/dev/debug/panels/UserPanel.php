<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\dev\debug\panels;

use neon\install\helpers\InstallHelper;

/**
 * Debug Panel.
 */
class UserPanel extends \yii\debug\panels\UserPanel
{
	/**
	 * Prevent the user debug panel during install
	 * @return bool
	 */
	public function isEnabled()
	{
		return InstallHelper::isInstallRoute() ? false : true;
	}
}