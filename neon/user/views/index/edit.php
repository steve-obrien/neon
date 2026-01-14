<?php
use neon\admin\widgets\AdminHeader;
use neon\core\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<?= AdminHeader::widget(['title'=>'Edit User', 'buttons'=>[ Html::buttonCancel(['/user/index/index']) ]]); ?>

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