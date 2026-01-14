<?php use yii\helpers\Url as Url; ?>
<p>An administrative user has already been added.</p>
<p>You will have to delete the database tables in order to re run this part of the installation process.</p>
<hr>
<a class="btn btn-primary" data-cmd="login" href="<?= Url::toRoute(['/user/account/login']); ?>">Log In</a>