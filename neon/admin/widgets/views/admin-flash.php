<?php foreach ($flashes as $key => $message): ?>
<div class="alert <?= $alertTypes[$key] ?> alert-dismissible alert-admin" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<strong><?= strtoupper($key) ?>!</strong> <?= $message; ?>
</div>
<?php endforeach; ?>