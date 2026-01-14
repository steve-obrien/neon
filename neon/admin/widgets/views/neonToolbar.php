<?php
use yii\widgets\Breadcrumbs;
?>
<div class="row neonToolbar">
	<div class="col-md-6">
		<h2 class="neonToolbar_title pull-left"><?= $title ?></h2>
		<div class="pull-left">
			<?=
				Breadcrumbs::widget([
					'homeLink' => ['label'=>'Dash', 'url'=>['/admin/index/index']],
					'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
				]);
			?>
		</div>
	</div>
	<div class="col-md-6 text-right">
		<?php foreach($buttons as $button): ?>
			<?php if(is_array($button)): ?>
				<div class="btn-group" role="group" aria-label="Actions">
					<?php foreach($button as $b): ?>
						<?= $b ?>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<?= $button ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
