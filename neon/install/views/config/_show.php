<?php
use neon\core\helpers\Url;
use neon\install\helpers\InstallHelper;
?>
<p>Sorry, but I cannot write to the <code><?= InstallHelper::getLocalConfigFile(); ?></code> file</p>

<p>You can create the <code><?= InstallHelper::getLocalConfigFile(); ?></code> manually and paste the following text into it.</p>
<div class="form-group">
	<textarea class="form-control" style="font-family: Consolas,Monaco,monospace" rows="15"><?= $config ?></textarea>
</div>
<p>After that has been completed, you can "Run the Install"</p>
<hr>
<a href="<?= Url::toRoute(['/install/database/index']) ?>" class="btn btn-primary">Run the Install</a>