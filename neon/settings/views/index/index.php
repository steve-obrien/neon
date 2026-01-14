<?php $this->title = 'Settings'; ?>
<?= \neon\admin\widgets\AdminHeader::widget(); ?>
<div class="row">
	<div class="col-md-2">
		<div class="panel panel-default panel-flush" >
			<ul class="nav nav-stacked nav-pills " role="tablist">
				<?php $index=1; foreach ($form->getSubForms() as $appId => $appForm): ?>
					<?php $hasError = $appForm->hasError(); ?>
					<li class="<?= $index == 1 ? 'active' : '' ?>">
						<a href="#<?= $appId ?>-tab" role="tab" data-toggle="pill">
							<?= neon($appId)->name; ?> <span class="pull-right"><?= $hasError ? 'ERROR' : '' ?></span>
						</a>
					</li>
					<?php $index++; endforeach; ?>
				<li>
					<a href="#general-tab" role="tab" data-toggle="pill">
						General
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="col-md-10 ">
		<?php echo $form->renderHeader() ?>
		<div class="tab-content" >
			<?php $index=1; foreach ($form->getSubForms() as $appId => $appForm) : ?>
				<div role="tabpanel" class="tab-pane <?php echo $index == 1 ? 'active' : '' ?>" id="<?php echo $appId ?>-tab">
					<div class="panel panel-default">
						<div class="panel-body">
							<?php echo $appForm ?>
							<neon-core-form-fields-submit name="submit-<?= $appId ?>">Save Settings</neon-core-form-fields-submit>
						</div>
					</div>
				</div>
				<?php $index++; endforeach; ?>
			<div role="tabpanel" class="tab-pane" id="general-tab">
				<?php echo $this->render('general/_general'); ?>
			</div>
		</div>
		<?php $form->renderFooter(); ?>
	</div>
</div>