<?php

namespace neon\user\widgets;

use yii\base\Widget;
use neon\user\models\User;

class Impersonating extends Widget
{
	public function run()
	{
		if (!neon()->user->isImpersonating()) {
			return '';
		}
		return $this->render('impersonating', [
			'restore' => neon()->user->getImpersonatingRestoreUser()
		]);
	}
}