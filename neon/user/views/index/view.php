<?php

use neon\core\helpers\Html;
use yii\widgets\DetailView;
use neon\admin\widgets\AdminHeader;

/* @var $this yii\web\View */
/* @var $model neon\user\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'] = [
	['label' => Yii::t('app', 'Users'), 'url' => ['index']],
	$this->title
];
$buttons = [ Html::buttonEdit(['/user/index/edit', 'id'=>$model->id]) ];
if (neon()->user->isSuper())
	$buttons[] = Html::buttonDelete(['/user/index/delete', 'id'=>$model->id]);
?>
<?=
	AdminHeader::widget([
		'title' => Html::encode($this->title),
		'buttons' => $buttons
	]);
?>
<div class="panel panel-default">
	<div class="panel-body">

		<?= DetailView::widget([
			'model' => $model,
			'attributes' => [
				'username',
				'email:email',
				'status',
				'super',
				'failed_logins',
				'created_at',
				'updated_at',
				'last_login',
				'logins',
			],
		]) ?>
	</div>
</div>
