<?php use yii\helpers\Url as Url; ?>
<p>The database tables are already installed.</p>
<p>To run this installation step again you will have to first delete your database tables.</p>
<hr>
<?php if ($progress == 4): ?>
	<a class="btn btn-default" href="<?= Url::toRoute(['/user/account/login']); ?>">Log In</a>
<?php endif; ?>
<a href="<?= Url::toRoute(['/install/account/index']) ?>" class="btn btn-primary">
	Next
	<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
</a>