<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url as Url;
use yii\helpers\Html as Html;
?>

<h4>Create an Admin Account</h4>
<p>Please provide the following information. Don't worry, you can always change these settings later.</p>
<hr/>
<?php $form = ActiveForm::begin([
	'layout' => 'horizontal',
	'fieldConfig' => [
		'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
		'horizontalCssClasses' => [
			'label' => 'col-sm-3',
			'offset' => 'col-sm-offset-4',
			'wrapper' => 'col-sm-8',
			'error' => '',
			'hint' => '',
		]
	]
]); ?>

	<?= $form->field($model, 'username')->hint('Name of the admin user') ?>
	<hr/>
	<?= $form->field($model, 'email')->hint('Admin email address') ?>
	<hr/>
	<?= $form->field($model, 'password')->passwordInput()->hint('Admin password.') ?>
	<hr/>
	<div class="form-group">
		<div class="col-sm-4 col-sm-push-3">
			<?= Html::submitButton('Submit <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>', ['class' => 'btn btn-primary']) ?>
		</div>
	</div>
<?php ActiveForm::end(); ?>