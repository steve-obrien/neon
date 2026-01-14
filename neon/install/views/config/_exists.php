<?php
use neon\core\helpers\Url as Url;
use neon\install\helpers\InstallHelper;
?>
<p>The <code><?= InstallHelper::getLocalConfigFile() ?></code> file already exists.</p>
<p>Delete this file to run through the installation process again.</p>
<?php if ($progress == 2): ?>
	<p>Or if the details are correct then you can "Run the Install"...</p>
<?php endif; ?>
<hr>
<?php if ($progress == 4): ?>
	<a class="btn btn-default" href="<?= Url::toRoute(['/user/account/login']); ?>">Log In</a>
<?php endif; ?>
<?php if ($progress == 2): ?>
	<a href="<?= Url::toRoute(['/install/database/index']) ?>" class="btn btn-primary">
		Run the Install
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
	</a>
<?php else: ?>
	<a href="<?= Url::toRoute(['/install/database/index']) ?>" class="btn btn-primary">
		Next
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
	</a>
<?php endif; ?>
