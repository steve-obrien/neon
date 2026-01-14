<?php
use \neon\core\helpers\Str;
use neon\utilities\utils\Server; 
?>
<div class="p-4 text-lg">

<?php
	$values = [
		'OS version' => PHP_OS . ' ' . php_uname('r'),
		'Neon Version' => neon()->getVersion(),
		'Neon Version (composer git reference)' => \Composer\InstalledVersions::getReference('newicon/neon'),
		'Yii Version' => \yii\BaseYii::getVersion(),
		'PHP version' =>  PHP_VERSION,
		'Current Language' => !empty(neon()->language) ? neon()->language: '',
		'Source Language' => !empty(neon()->sourceLanguage) ? neon()->sourceLanguage: '',
		'Charset' => !empty(neon()->charset) ? neon()->charset : '',
		'IP' =>$_SERVER['REMOTE_ADDR'],
	];
	$systemValues = collect($values)->map(function($value, $label) { return ['label' => $label, 'value' => $value]; });

	$values = [
		'Driver' => neon()->db->driverName,
		'Version' => neon()->db->getServerVersion(),
		'Database Name' => env('DB_NAME'),
	];
	$dbValues = collect($values)->map(function($value, $label) { return ['label' => $label, 'value' => $value]; });
	if (neon()->user->is('neon-administrator')) {
		$dbValues[] = ['label' => 'Username', 'value' => env('DB_USER'), 'locked'=>'neon-administrator'];
	}

	$envs = [
		['label' => 'Environment', 'value' => env('NEON_ENV')],
		['label' => 'Debug Mode', 'value' => YII_DEBUG ? 'Yes' : 'No'],
		['label' => 'Timezone', 'value' => env('NEON_TIMEZONE')],
	];

	$result = collect($_ENV)->except([
		'NEON_ENV', 'NEON_DEBUG', 'DB_NAME', 'DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_TABLE_PREFIX', 'NEON_SECRET', 'NEON_ENCRYPTION_KEY', 'NEON_TIMEZONE',
	]);
	$envs = collect($envs)->merge($result->map(function($value, $key) {
		return ['label' => $key, 'value' => '***'];
	})->all());

	$server = new Server();
	$cpu = $server->getCpuInfo();
	$memory = $server->getMemoryInfo();
?>

<?php 
	$free = disk_free_space('/');
	$total = disk_total_space('/');
	$used = $total - $free;
	$percent = $used/$total*100;
?>

<h2>Server</h2>
<table class="table" style="table-layout: fixed;">
	<tr>
		<th>CPU</th>
		<td><strong><?= $cpu['cores'] ?> core</strong> <?= $cpu['model'] ?> </td>
	</tr>
	<tr>
		<th>Memory</th>
		<td><strong><?= $memory['total']/(1024*1024*1024) ?></strong> GB</td>
	</tr>
	<tr>
		<th>Disk</th>
		<td><?= Str::formatBytes($total); ?>  (<?= Str::formatBytes($free); ?> free)</td>
	</tr>
	<tr>
		<th></th>
		<td><div class="relative pt-1">
	<div style="overflow:hidden;display:flex;height:20px;border-radius:7px;background:#ccc;" class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-lightBlue-200">
		<div style="display:flex;flex-direction:column; justify-content: center; background:#337ab7; width:<?= $percent ?>%"></div>
	</div>
</div></td>
	</tr>
</table>

<h2>System</h2>
<table class="table" style="table-layout: fixed;">
	<?php foreach($systemValues as $values): ?>
	<tr>
		<th><?= $values['label'] ?></th>
		<td><?= $values['value'] ?></td>
	</tr>
	<?php endforeach; ?>
</table>


<h2>Environment</h2>
<table class="table" style="table-layout: fixed;">
	<?php foreach($envs as $value): ?>
		<tr>
			<th><?= $value['label'] ?></th>
			<td><?= $value['value'] ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<h2>Database</h2>
<table class="table" style="table-layout: fixed;">
	<?php foreach($dbValues as $value): ?>
		<tr>
			<th>
				<?= $value['label'] ?>
				<?php if (isset($value['locked'])): ?>
					<abbr title="This is only visible to <?= $value['locked'] ?> users"><svg style="width:16px;float:right;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></abbr>
				<?php endif; ?>
			</th>
			<td>
				<?= $value['value'] ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>

<h2>Requirements</h2>
<?php foreach($requirements as $req): ?>
	<div style="display:flex;">
		<svg style="width:16px; height:16px;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
		<div><?= $req['name']; ?></div>
	</div>
<?php endforeach; ?>

</div>
