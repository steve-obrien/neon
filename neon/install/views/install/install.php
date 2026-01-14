<?php use yii\helpers\Url as Url; ?>
<div class="media">
	<div class="media-left">
		<div class="media-object">
			<span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green;font-size:35px;"></span>
		</div>
	</div>
	<div class="media-body">
		<strong>The database has been successfully installed!</strong> We now need to set up an administrative account to access and administer the application.
	</div>
</div>
<div class="clearfix" style="margin-top:20px;">
	<a href="<?= Url::toRoute(['/install/account']) ?>" class="btn btn-primary">
		Create an Admin User Account
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
	</a>
</div>