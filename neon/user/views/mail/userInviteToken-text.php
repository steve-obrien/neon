<?php

/* @var $this yii\web\View */
/* @var $user \neon\user\models\User */
/* @var string $resetLink */
/* @var $expiresIn String */
?>
Hello,

Follow or copy the link below to complete your user registration for <?= $siteName ?>:

<?= $inviteLink ?>

(This link will expire in <?= $expiresIn; ?>)