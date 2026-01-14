 <?php
use neon\core\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<?php $af = ActiveForm::begin(['id' => 'form-signup', 'layout'=>'horizontal']); ?>
<div class="site-signup">
	<div class="panel panel-default">
		<div class="panel-body">
			<fieldset>
			<?= $af->field($form, 'username', ['inputOptions'=>['autocomplete'=>'off']]) ?>
			</fieldset>
			<fieldset>
			<?= $af->field($form, 'email', ['inputOptions'=>['autocomplete'=>'off']]) ?>
			</fieldset>
			<fieldset>
			<?= $af->field($form, 'password')->passwordInput() ?>
			</fieldset>
		</div>
		<div class="panel-footer">
			<div class="row">
				<div class="col-sm-3"></div>
				<div class="col-sm-6">
					<?= Html::buttonCancel(['/user/index/index']) ?>
					<?= Html::submitButton('<i class="fa fa-check"></i> Save', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php ActiveForm::end(); ?>
