<?php

/* @var $this yii\web\View */
/* @var $user \neon\user\models\User */
/* @var string $resetLink */
/* @var $expiresIn String */
?>
Hello <?= $user->getName() ?>,

Follow the link below to reset your password:

<?= $resetLink ?>

(This link will expire in <?= $expiresIn; ?>)