<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */


$this->title = $this->title ? $this->title : Neon::$app->name;
$this->params['breadcrumbs'][] = $this->title;

?>
<style>
	.login-content: {text-align:center;}
	#login-username:hover {position:relative;z-index:1;}
	#login-username:focus {position:relative;z-index:1;}
	#login-username {border-radius:6px 6px 0 0;text-align:center;}
	#login-password {border-radius:0 0 6px 6px;text-align:center;margin-top:-1px;margin-bottom:20px;}
	.login-content {font-size:16px;}
	.login-content .checkbox {line-height: 25px;margin-top:20px;}
</style>
<div class="login-content">
	<div class="brand"><h1 style="margin-top:0;margin-bottom:0.5em;"><?= neon()->name ?></h1></div>
	<?php if (neon()->user->isGuest): ?>
		<?php $form = ActiveForm::begin(['id' => 'login-form', 'class'=>'margin-bottom-0']); ?>
			<?= $form->errorSummary($model, ['class'=>'alert alert-danger', 'style'=>'margin-bottom:15px;']); ?>
			<?= Html::activeTextInput($model, 'username', ['class'=>'form-control input-lg ', 'placeholder'=>'Email Address']); ?>
			<?= Html::activePasswordInput($model, 'password', ['class'=>'form-control input-lg', 'placeholder'=>'Password']); ?>
			<?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block btn-lg', 'name' => 'login-button']) ?>
			<div class="checkbox"><?= Html::activeCheckbox($model, 'rememberMe'); ?></div>
			<?= Html::a('Forgot password?', ['/user/account/request-password-reset'], ['id'=>'password_reset_link']) ?></span>
		<?php ActiveForm::end(); ?>
	<?php else: ?>
		<p>You are already logged in as <?= neon()->user->name; ?>.</p>
		<a href="<?= neon()->user->getReturnUrl() ?>" class="btn btn-primary">Return to your home page</a>
		<a href="<?= Url::toRoute(['/user/account/logout']) ?>" class="btn btn-primary">Logout</a>
	<?php endif; ?>
</div>