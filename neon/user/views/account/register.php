<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


$this->title = 'Account registration';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
	.login-content: {text-align:center;}
	#register-username:hover {position:relative;z-index:1;}
	#register-username:focus {position:relative;z-index:1;}
	#register-username {border-radius:6px;text-align:center;}
	#register-password {border-radius:6px; text-align:center;}
	#register-username, #register-password{font-size:18px; padding: 22px 16px;font-size: 18px;line-height: 1.3333333;}
	.login-content .checkbox {line-height: 25px;margin-top:20px;}
	#register .form-group {padding:0;}
</style>
<div class="login-content">
	<div class="brand">
		<h1><?= neon()->name; ?></h1>
		<h3 style="margin-bottom:0.5em;">Create your account</h3></div>
	<?php if (neon()->user->isGuest): ?>
		<?php
//		$form = ActiveForm::begin(['id' => 'login-form', 'class'=>'margin-bottom-0']);
//			echo $form->errorSummary($model, ['class'=>'alert alert-danger', 'style'=>'margin-bottom:15px;']);
//			echo Html::activeTextInput($model, 'username', ['class'=>'form-control input-lg', 'placeholder'=>'Username']);
//			echo Html::activePasswordInput($model, 'password', ['class'=>'form-control input-lg', 'placeholder'=>'Password']);
//			echo Html::submitButton('Register', ['class' => 'btn btn-primary btn-block btn-lg', 'name' => 'register-button']);
//		ActiveForm::end();
		?>
		<?= $form->run(); ?>
	<?php else: ?>
		<p>You are already logged in as <?= neon()->user->name; ?>.</p>
		<a href="<?= neon()->user->getReturnUrl() ?>" class="btn btn-primary">Return to your home page</a>
		<a href="<?= Url::toRoute(['/user/account/logout']) ?>" class="btn btn-primary">Logout</a>
	<?php endif; ?>
</div>