<?php

namespace neon\admin\components;

use neon\core\web\AdminController as CoreAdminController;

/**
 * Simple admin controller base class.
 * This is useful for portals and admin areas.  The user has to be logged in.
 * @deprecated use \neon\core\web\AdminController
 */
class AdminController extends CoreAdminController
{
}