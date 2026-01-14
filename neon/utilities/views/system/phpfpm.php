<script src="https://cdn.tailwindcss.com"></script>

<?php
$stats = fpm_get_status();
$memory_limit = ini_get('memory_limit');


$server = new \neon\utilities\utils\Server();
$memoryInfo = $server->getMemoryInfo();
$memoryInfo['total_gb'] = $memoryInfo['total']/(1024*1024*1024);

?>

<?php function dtdd($label, $value, $description = '', $icon='') { ?>
	<div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
		<dt class="truncate text-lg font-medium text-gray-800" title="<?= $description ?>"><?= $label ?></dt>
		<dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= $value ?></dd>
	</div>
<?php } ?>

<div class="p-4 bg-gray-100">
	<h3 class="text-base font-semibold leading-6 text-gray-900">Memory</h3>
	<dl class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
		<div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
			<dt class="truncate text-lg font-medium text-gray-800">
			<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heroicon-ram">
				<rect x="3" y="6" width="18" height="12" rx="2" ry="2"></rect>
				<path d="M7 6V4M17 6V4M7 20v-2M17 20v-2M3 10h18M3 14h18"></path>
				<path d="M5 10v4M9 10v4M13 10v4M15 10v4"></path>
			</svg>
			PHP Memory Limit</dt>
			<dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= $memory_limit ?></dd>
		</div>
		<?= dtdd('Total Server Memory', $memoryInfo['total_gb'] . ' GB'); ?>
	</dl>
</div>

<div class="p-4 bg-gray-100">
	<h3 class="text-base font-semibold leading-6 text-gray-900">FPM Processes</h3>
	<dl class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
		<?= dtdd('Idle processes (waiting for requests)', $stats['idle-processes'], 'The number of processes that are currently idle (waiting for requests).') ?>
		<?= dtdd('Active processes (processing requests)', $stats['active-processes'], 'The number of processes that are currently processing requests.') ?>
		<?= dtdd('Total processes', $stats['total-processes'], 'The current total number of processes.') ?>
	</dl>
</div>

<div class="p-4 bg-gray-100">
	<h3 class="text-base font-semibold leading-6 text-gray-900">FPM Stats</h3>
	<dl class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
		<?= dtdd('Total accepted connections', $stats['accepted-conn'], 'The total number of accepted connections.') ?>
		<?= dtdd('Max active processes', $stats['max-active-processes'], 'The maximum number of concurrently active processes.') ?>
		<?= dtdd('Max children reached', $stats['max-active-processes'], 'Has the maximum number of processes ever been reached? If so the displayed value is greater than or equal to 1 otherwise the value is 0.') ?>
		<?= dtdd('Slow requests	', $stats['slow-requests'], 'The total number of requests that have hit the configured request_slowlog_timeout.') ?>
	</dl>
</div>

<div class="p-4">
	<h3 class="text-base font-semibold leading-6 text-gray-900">FPM Processes</h3>
	<table class="min-w-full divide-y divide-gray-300">
		<thead>
			<tr>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The system PID of the process.">pid</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The state of the process - Idle, Running, ...">state</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The date/time at which the process started.">start-time</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The number of seconds since the process started.">start-since</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The total number of requests served.">requests</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The total time in microseconds spent serving last request.">request-duration</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The HTTP method of the last served request.">request-method</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The URI of the last served request (after webserver processing, it may always be /index.php if you use a front controller pattern redirect).">request-uri</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Query-string</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The full path of the script executed by the last request. This will be '-' if not applicable (eg. status page requests).">script</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The %cpu of the last request. This will be 0 if the process is not Idle because the calculation is done when the request processing is complete. The value can exceed 100%, because metric will tell which percentage of the total cpu time was used in the last request - takes processes on all cores into account, whereas the 100% is for one core only.">last request cpu</th>
				<th scope="col" class="px-2 py-3.5 text-left text-sm font-semibold text-gray-900" title="The maximum amount of memory consumed by the last request. This will be 0 if the process is not Idle because the calculation is done when the request processing is complete.">last request memory</th>
			</tr>
		</thead>
		<tbody class="divide-y divide-gray-200">
			<?php foreach ($stats['procs'] as $p) : ?>
				<tr>
					<td class="whitespace-nowrap px-2 py-2 text-sm "><?= $p['pid'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['state'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['start-time'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['start-since'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['requests'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['request-duration'] / 1000 ?> ms</td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['request-method'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['request-uri'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['query-string'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['script'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= $p['last-request-cpu'] ?></td>
					<td class="whitespace-nowrap px-2 py-2 text-sm"><?= \neon\core\helpers\Str::formatBytes($p['last-request-memory'], 4); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>