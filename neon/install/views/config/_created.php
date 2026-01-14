<?php use yii\helpers\Url as Url; ?>
<div class="media">
	<div class="media-left">
		<div class="media-object">
			<span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green;font-size:35px;"></span>
		</div>
	</div>
	<div class="media-body">
		<!-- @test: successfully created env config -->
		<p>We now have a configured <code>config/env.ini</code> file so we can talk to the database. Next we need to "Run the Install" and create an administrative user account...</p>
	</div>
</div>
<hr>
<div class="clearfix" style="margin-top:10px;">
	<a href="<?= Url::toRoute(['/install/database/index']) ?>" class="btn btn-primary" data-cmd="run_install">
		Run the Install
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
	</a>
</div>