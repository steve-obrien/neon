<?php use yii\helpers\Url as Url; ?>
<p>Neon has been installed!</p>
<p>Now that neon is installed and the administrative account has been created you can log in:</p>
<hr>
<a data-cmd="login" href="<?= Url::toRoute(['/user/account/login']); ?>" class="btn btn-primary">Log In</a>