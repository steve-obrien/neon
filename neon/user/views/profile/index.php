<?php
use neon\admin\widgets\AdminHeader;
?>

<?=
	AdminHeader::widget([
		'title' => Ucwords(neon()->user->getName()) . '&#39s Account',
	]);
?>
<div class="container mt20">
	<div class="row">
		<!-- Tabs -->
		<div class="col-md-4">
			<div class="panel panel-default panel-flush">
				<div class="panel-heading">
					Settings
				</div>

				<div class="panel-body">
					<ul class="nav nav-stacked nav-pills" role="tablist">
						<!-- Profile Link -->
						<li role="presentation" class="active">
							<a href="#profile" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="true">
								<i class="fa fa-fw fa-btn fa-edit"></i>Profile
							</a>
						</li>

						<li role="presentation" class="">
							<a href="#profile" aria-controls="api" role="tab" data-toggle="tab" aria-expanded="false">
								<i class="fa fa-fw fa-btn fa-cubes"></i>Api Tokens
							</a>
						</li>

					</ul>
				</div>
			</div>

		</div>
		<!-- Tab Panels -->
		<div class="col-md-8">
			<div class="tab-content">
				<!-- Profile -->
				<div role="tabpanel" class="tab-pane active" id="profile">
					<div>
						<!-- Update Profile Photo -->
						<div>
							<div class="panel panel-default">
								<div class="panel-heading">Profile Photo</div>

								<div class="panel-body">
									<!--v-if-->

									<form class="form-horizontal" role="form">
										<!-- Photo Preview-->
										<div class="form-group">
											<label class="col-md-4 control-label">&nbsp;</label>

											<div class="col-md-6">
												<img src="<?= neon()->user->getImageUrl(120) ?>"/>
											</div>
										</div>

									</form>
								</div>
							</div><!--v-if-->
						</div><!--v-component-->

						<!-- Update Contact Information -->
						<div class="panel panel-default">
							<div class="panel-heading">Contact Information</div>

							<div class="panel-body">
							</div>
						</div><!--v-component-->
					</div><!--v-component-->
				</div>

			</div>
		</div>
	</div>
</div>

{js}

{/js}