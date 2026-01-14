<?php

namespace neon\user\grid;

use \neon\core\helpers\Url;
use \neon\core\helpers\Html;

class UserInviteGrid extends \neon\core\grid\Grid
{
	public function init()
	{
		$this->query = (new \yii\db\Query())
			->select("`user_invite`.*, `invited_user`.`email`, `created_by`.`uuid` as created_by, `updated_by`.`uuid` as updated_by")
			->from('user_invite')
			->leftJoin('user_user invited_user', '`user_invite`.`user_id` = `invited_user`.`uuid`')
			->leftJoin('user_user created_by', '`user_invite`.`created_by` = `created_by`.`id`')
			->leftJoin('user_user updated_by', '`user_invite`.`updated_by` = `updated_by`.`id`');
		if (!neon()->user->isSuper()) {
			$this->query->where("`created_by`.`uuid` = '".neon()->user->getUuid()."'");
		}

		$this->title = 'Pending Invites';
		$this->setPageSize(10);

		// columns
		$this->addTextColumn('email')->setDbField('invited_user.email');
		$this->addTextColumn('token');

		$f = new \neon\core\form\fields\SelectDynamic('created_by', [
			'dataMapProvider' => 'user',
			'dataMapKey' => 'users'
		]);
		$this->addColumn('created_by')->setDbField('user_invite.created_by')->setMember($f);
		$this->addColumn('updated_by')->setDbField('user_invite.updated_by')->setMember($f);
		$this->addTextColumn('created_at')->setDbField('user_invite.created_at');
		$this->addTextColumn('updated_at', ['title'=>'Updated At'])->setDbField('user_invite.updated_at');
		$this->addTextColumn('actions')->setFilter(false);
	}

	public function renderActions($model)
	{
		$resend = '<a class="btn btn-default btn-sm" data-toggle="tooltip" title="Resend" href="'.Url::to(['/user/invite/resend', 'token'=>$model['token']]).'" ><i class="fa fa-paper-plane" aria-hidden="true"></i></a>';
		$revoke = '<a class="btn btn-danger btn-sm" data-toggle="tooltip" title="Revoke" href="'.Url::to(['/user/invite/revoke', 'token'=>$model['token']]).'" ><i class="fa fa-undo" aria-hidden="true"></i></a>';
		return "$resend $revoke";
	}
}