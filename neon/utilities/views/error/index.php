<?php
$errorLog = neon()->getAlias('@runtime/logs/error.log');
?>
<div style="padding:8px 8px 8px;border-bottom:1px solid #ccc;">
	<a href="<?= url(['/utilities/error/clear']) ?>" class="btn btn-default">Clear</a>
</div>
<?php if (file_exists($errorLog)): ?>
	<pre style="background-color:transparent;border:0;position:absolute;top:50px;left:0;bottom:0;right:0"><?php include $errorLog; ?></pre>
<?php endif ?>
