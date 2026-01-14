<?php
use yii\helpers\Html;
use neon\core\themes\blacktie\AppAsset;
use yii\bootstrap\BootstrapAsset;
use neon\core\helpers\Url as Url;
use neon\install\helpers\InstallHelper as InstallHelper;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
BootstrapAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" style="background-color:#f1f1f1;">
<head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
	<style>
		.well.well-bright{background-color:#fff;box-shadow:0 1px 1px rgba(0, 0, 0, .1);}
	</style>
</head>
<body style="background-color:#f1f1f1;">

<?php $this->beginBody() ?>

	<div class="container" style="width:740px">

		<h1 class="text-muted text-center" style="margin-bottom:50px">Neon</h1>

		<style>
			.bs-wizard {margin-top: 10px;}
			/*Form Wizard*/
			.bs-wizard {border-bottom: solid 1px #e0e0e0; padding: 0 0 10px 0;}
			.bs-wizard > .bs-wizard-step {padding: 0; position: relative;}
			.bs-wizard > .bs-wizard-step + .bs-wizard-step {}
			.bs-wizard > .bs-wizard-step .bs-wizard-stepnum {color: #595959; font-size: 16px; margin-bottom: 5px;}
			.bs-wizard > .bs-wizard-step .bs-wizard-info {color: #999; font-size: 14px;padding:0 10px}
			.bs-wizard > .bs-wizard-step > .bs-wizard-dot {position: absolute; width: 30px; height: 30px; display: block; background: #fbe8aa; top: 45px; left: 50%; margin-top: -15px; margin-left: -15px; border-radius: 50%;}
			.bs-wizard > .bs-wizard-step > .bs-wizard-dot:after {content: ' '; width: 14px; height: 14px; background: #fbbd19; border-radius: 50px; position: absolute; top: 8px; left: 8px; }
			.bs-wizard > .bs-wizard-step > .progress {position: relative; border-radius: 0px; height: 8px; box-shadow: none; margin: 20px 0;}
			.bs-wizard > .bs-wizard-step > .progress > .progress-bar {width:0px; box-shadow: none; background: #fbe8aa;}
			.bs-wizard > .bs-wizard-step.active > .progress > .progress-bar {width:50%;}
			.bs-wizard > .bs-wizard-step.complete > .progress > .progress-bar {width:100%;}
			.bs-wizard > .bs-wizard-step:first-child.active > .progress > .progress-bar {width:0%;}
			.bs-wizard > .bs-wizard-step:last-child.active > .progress > .progress-bar {width: 100%;}
			.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot {background-color: #f5f5f5;}
			.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot:after {opacity: 0;}
			.bs-wizard > .bs-wizard-step:first-child  > .progress {left: 50%; width: 50%;}
			.bs-wizard > .bs-wizard-step:last-child  > .progress {width: 50%;}
			.bs-wizard > .bs-wizard-step.disabled a.bs-wizard-dot{ pointer-events: none; }
			.bs-wizard > .bs-wizard-step.selected .bs-wizard-stepnum{font-weight:bold;}
			.bs-wizard > .bs-wizard-step.selected > .bs-wizard-dot:after {background:#fb7c19;}
			/*END Form Wizard*/
		</style>
		<?php $steps = InstallHelper::getInstallSteps(); ?>
		<?php $progress = InstallHelper::getProgress(); // $this->params['progress']; ?>
		<div>
			<div class="well well-bright">
				<div class="row bs-wizard" style="border-bottom:0;">
					<div style="width:20%" class="col-xs-3 bs-wizard-step <?= Url::isRoute('/install/index') ? 'selected' : ''; ?>
					 <?= ($progress == 0) ? (Url::isRoute('/install/config') ? 'complete' : 'active') : (($progress > 0) ? 'complete' : 'disabled') ?>">
						<div class="text-center bs-wizard-stepnum">Ready Check</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="<?= Url::toRoute(['/install/index']); ?>" class="bs-wizard-dot"></a>
						<div class="bs-wizard-info text-center">Check system requirements.</div>
					</div>
					<div style="width:20%"
						 class="col-xs-3 bs-wizard-step
						<?= Url::isRoute('/install/config') ? 'selected active' : ''; ?>
						<?= ($progress == 1) ? ' active' : ''; ?>
						<?= ($progress > 1) ? ' complete' : ''; ?>
						<?= ($progress == 0 && !Url::isRoute('/install/config')) ? ' disabled' : ''; ?>
					">
						<div class="text-center bs-wizard-stepnum">Configure</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="<?= Url::toRoute(['/install/config']); ?>" class="bs-wizard-dot"></a>
						<div class="bs-wizard-info text-center">Configure neon to talk to the database.</div>
					</div>
					<div style="width:20%" class="col-xs-3 bs-wizard-step <?= Url::isRoute('/install/database') ? 'selected' : ''; ?> <?= ($progress == 2) ? 'active' : (($progress > 2) ? 'complete' : 'disabled') ?>">
						<div class="text-center bs-wizard-stepnum">Database Install</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="<?= Url::toRoute(['/install/database']); ?>" class="bs-wizard-dot"></a>
						<div class="bs-wizard-info text-center">Install the database tables</div>
					</div>
					<div style="width:20%" class="col-xs-3 bs-wizard-step <?= Url::isRoute('/install/account/index') ? 'selected' : ''; ?> <?= ($progress == 3) ? 'active' : (($progress > 3) ? 'complete' : 'disabled') ?>"><!-- complete -->
						<div class="text-center bs-wizard-stepnum">Account</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="<?= Url::toRoute(['/install/account']); ?>" class="bs-wizard-dot"></a>
						<div class="bs-wizard-info text-center">Create an administrative user account</div>
					</div>
					<div style="width:20%" class="col-xs-3 bs-wizard-step <?= Url::isRoute('/install/account/complete') ? 'selected' : ''; ?> <?= ($progress == 4) ? 'active' : (($progress > 4) ? 'complete' : 'disabled') ?>"><!-- active -->
						<div class="text-center bs-wizard-stepnum">Log In</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="<?= Url::toRoute(['/install/account/complete']); ?>" class="bs-wizard-dot"></a>
						<div class="bs-wizard-info text-center">All Finished!</div>
					</div>
				</div>
			</div>

			<div class="well well-bright">
				<h2><?= $steps[$this->params['step']] ?></h2>
				<hr>
				<?php echo $content; ?>
			</div>
		</div>

	</div> <!-- /container -->


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>