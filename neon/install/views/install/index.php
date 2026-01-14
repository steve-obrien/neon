<?php use yii\helpers\Url as Url; ?>
<div class="media">
	<div class="media-left">
		<div class="media-object">
			<span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green;font-size:35px;"></span>
		</div>
	</div>
	<div class="media-body">
		<p>You have a configured <code>config/env.ini</code> file. Now we can talk to the database we need to "Run the Install" and create an administrative user account...</p>
	</div>
</div>
<div class="clearfix" style="margin-top:10px;">
	<a href="<?= Url::toRoute(['/install/database/install']) ?>" class="btn btn-primary">
		Run the Install
		<span class="glyphicon glyphicon-chevron-right" ></span>
	</a>
</div>