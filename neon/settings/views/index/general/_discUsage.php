<?php
// This could be managed more specifically by firefly - we may want to limit storage and charge for extra
// based on the size of the media folders.
use \neon\core\helpers\Str;
$diskTotal = disk_total_space('/');
$diskFreeSpace = disk_free_space('/');
$diskUsed = $diskTotal - $diskFreeSpace;
$diskPercentage = $diskUsed / $diskTotal * 100;
$diskColor= ($diskPercentage > 60) ? "#d62020" : "#67c23a";
?>
<h4>Disc Usage /</h4>
<div class="table-responsive">
	<table class="table table-condensed      table-hover" style="table-layout: fixed;">
		<tbody>
		<tr><td style="white-space: normal">Percent used</td><td style="overflow:auto">
				<el-progress :percentage="<?= round($diskPercentage) ?>" color="<?= $diskColor ?>"></el-progress>
			</td></tr>
		<tr><td style="white-space: normal">Used</td><td style="overflow:auto"><?= Str::formatBytes($diskUsed) ?></td></tr>
		<tr><td style="white-space: normal">Free</td><td style="overflow:auto"><?= Str::formatBytes($diskFreeSpace) ?></td></tr>
		<tr><td style="white-space: normal">Total</td><td style="overflow:auto"><?= Str::formatBytes($diskTotal) ?></td></tr>
		</tbody>
	</table>
</div>