<h2><a href="<?= url("/dev/docs") ?>">Neon Developer Guide</a></h2>
<h2><a href="<?= url("/dev/index/terminal") ?>">PHP Fiddle Terminal</a></h2>
<h2><a href="<?= url("/dev/index/terminal-js") ?>">JS Fiddle Terminal</a></h2>

<?php if ($debugToolbarEnabled): ?>
	<a class="btn btn-primary" href="<?= url("/dev/index/toggle-debug-toolbar") ?>">Turn off debug toolbar (just for me) </a>
<?php else: ?>
	<a class="btn btn-default" href="<?= url("/dev/index/toggle-debug-toolbar") ?>">Turn on debug toolbar (just for me) </a>
<?php endif ?>