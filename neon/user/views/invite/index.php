<?php
use neon\core\helpers\Html;
use yii\bootstrap\ActiveForm;
use neon\admin\widgets\AdminHeader;
$this->title = 'Invite User';
$this->params['breadcrumbs'][] = $this->title;
?>
<?= AdminHeader::widget(['title'=>'Invite User', 'buttons'=>[ Html::buttonCancel(['/user/index/index']) ]]); ?>

<div class="container">
	<div class="site-signup">
		<div class="grid">
			<div class="row col-sm-6 col-sm-offset-3">
				<div class="panel panel-default">
					<div class="panel-body">
						<?= $form->run() ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>