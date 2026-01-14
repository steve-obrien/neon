<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \neon\user\models\User */
/* @var $resetLink String */
/* @var $expiresIn String */
?>
<div class="user-invite">
	<p>Hello,</p>

	<p>Follow or copy the link below to complete your user registration for <?= $siteName ?>:</p>

	<p><?= Html::a(Html::encode($inviteLink), $inviteLink) ?></p>

	<p>(This link will expire in <?= $expiresIn; ?>)</p>
</div>
