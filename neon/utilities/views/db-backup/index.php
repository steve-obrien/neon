
<div style="padding:12px 12px 12px 2em;"><a href="<?= url(['/utilities/db-backup/backup']) ?>" class="btn btn-primary "><i class="fa fa-database"></i> Create New Backup </a></div>
<table class="table">
	<tr>
		<td style="padding-left:2em;">File</td>
		<td>Size</td>
		<td>Created</td>
	</tr>
<?php foreach($backups as $backup): ?>
<tr>
	<td style="padding-left:2em;"><a href="<?= url(['/utilities/db-backup/download', 'file' => $backup['file']]); ?>"><i class="fa fa-download"></i> <?= $backup['file'] ?></a></td>
	<td><?= format_bytes($backup['size']) ?></td>
	<td><?= $backup['modified'] ?></td>
</tr>
<?php endforeach; ?>
</table>
