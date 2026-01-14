<?php use yii\helpers\Url; ?>
<div class="alert alert-warning alert-admin">
	You are currently impersonating <strong><?php echo neon()->user->getName(); ?></strong>. <a href="<?= Url::to(['/user/account/impersonate-restore']); ?>">Restore to <?php echo $restore->username; ?></a>
</div>