<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \neon\user\models\User */
/* @var $resetLink String */
/* @var $expiresIn String */
?>
<div class="password-reset">
	<p>Hello <?= Html::encode($user->getName()) ?>,</p>

	<p>Follow the link below to reset your password:</p>

	<p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>

	<p>(This link will expire in <?= $expiresIn; ?>)</p>
</div>
