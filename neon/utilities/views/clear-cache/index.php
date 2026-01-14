<?php
use \neon\core\helpers\Str;
function folderSize($dir) {
	$size = 0;
	foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
		$size += is_file($each) ? filesize($each) : folderSize($each);
	}
	return $size;
}

/**
 * @param string|array $path
 */
function fileSizeBadge($path) {
	if (is_array($path)) {
		if (!isset($path['cachePath'])) return;
		$path = $path['cachePath'];
	}
	if (file_exists(neon()->getAlias($path))) {
		$size = folderSize(neon()->getAlias($path));
		?>
		<span class="badge badge-info " style="font-size:16px;margin-left:auto;align-self:center;"><?= $size===0 ? 0 : Str::formatBytes($size); ?></span>
		<?php
	}
}

?>

<div class=" p-8 border-b ">
	<a href="<?= url(['/utilities/clear-cache/clear-all']) ?>" class="btn btn-lg btn-default "><i class="fa fa-trash"></i> Clear all cache</a>
	<p style="font-size:16px;">This will delete all cache data, all published assets, and all compiled templates. This will temporarily affect the web applications performance.</p>
</div>



<div class=" p-8 border-b">
	<div style="display:flex; align-items:center;">
		<a href="<?= url(['/utilities/clear-cache/clear-smarty']) ?>" class="btn btn-lg btn-default " style="margin-right:10px;"><i class="fa fa-trash"></i> Smarty Cache</a>
		<div>
			- This will remove smarties compiled template files
		</div>
		<?= fileSizeBadge('@runtime/smarty') ?>
	</div>
</div>

<div class=" p-8 border-b">
	<div style="display:flex; align-items:center;">
		<a href="<?= url(['/utilities/clear-cache/clear-assets']) ?>" class="btn btn-lg btn-default " style="margin-right:10px;"><i class="fa fa-trash"></i> Clear Assets</a>
		<div>
			- This will remove all published assets in <code><?= neon()->assetManager->basePath ?></code>
		</div>
		<?= fileSizeBadge(neon()->assetManager->basePath) ?>
	</div>
</div>

<?php foreach($caches as $app => $caches): ?>

	<h3 class="pl-4" style="padding: 7px 16px; background-color: #f8f8f8; margin: 0;"><?=$app?></h3>

	<?php foreach($caches as $key => $cache): ?>
		<?php
		/*
		 * As this is a core utility managing often core cache its ok if we test for "known" caches
		 */
		?>
		<?php if ($cache['class'] === 'yii\caching\ArrayCache') continue; ?>

		<?php if ($key === 'cacheFirefly'): ?>
			<div class="p-8 border-b " >
				<div style="display:flex">
					<a href="<?= url(['/utilities/clear-cache/clear', 'app' => $app, 'component' => $key]) ?>" class="btn btn-lg btn-default " style="margin-right:10px;"><i class="fa fa-trash"></i> Firefly Cache</a>
					<div>
						- This will remove all cache managed by the <code>neon()-><?= $key ?></code> component
						<?php if (isset($cache['cachePath'])): ?>
							<br/> - This will delete cache files stored in <code><?= neon()->getAlias($cache['cachePath']) ?></code>
						<?php endif; ?>
					</div>
					<?= fileSizeBadge($cache) ?>
				</div>
				<div>
					Firefly caches the result of transformed images.
					Transforming images is a CPU and memory intensive operation that is quite slow. Deleting the cache will force firefly to redo all its image transforms.
					You will likely notice a slight delay in images appearing the first time they are loaded after removing the cache.
				</div>
			</div>
		<?php else: ?>
		<?php
			if ($cache['class'] === 'yii\caching\ArrayCache') continue;
			/**
			 * To catch other caches added
			 * If a new cache component has been added - but this utility has not been updated then it will show up here automatically.
			 */
			?>
			<div class="p-8 border-b " style="display:flex">
				<div>
					<a href="<?= url(['/utilities/clear-cache/clear', 'app' => $app, 'component' => $key]) ?>" class="btn btn-lg btn-default " style="margin-right:10px;"><i class="fa fa-trash"></i> <?= str_replace('Cache', '', \neon\core\helpers\Str::titleize($key, true)) ?> Cache</a>
				</div>
				<div>
					- This will remove all cache managed by the <code>neon()-><?= $key ?></code> component
					<?php if (isset($cache['cachePath'])): ?>
						<br/>- This deletes caches files stored in <code><?= neon()->getAlias($cache['cachePath']) ?></code>
					<?php endif; ?>
				</div>
				<?= fileSizeBadge($cache) ?>
			</div>
		<?php endif;     ?>
	<?php endforeach; ?>
<?php endforeach; ?>
