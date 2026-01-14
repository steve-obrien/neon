<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


$this->title = 'Password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php if (neon()->session->hasFlash('success')) : ?>
	<?= Html::a('Back to the login form.', ['/user/account/login']) ?>
<?php else: ?>
	<h1><?= Html::encode($this->title) ?></h1>

	<div class="login-content">
			<p>Please fill out your email. A link to reset your password will be sent to this email.</p>
			<?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
			<?= $form->field($model, 'email', ['inputOptions' =>  ['class'=>'form-control input-lg']]) ?>
			<div class="form-group">
				<?= Html::submitButton('Send', ['class' => 'btn btn-primary btn-lg btn-block']) ?>
			</div>
			<?php ActiveForm::end(); ?>
	</div>
<?php endif; ?>
