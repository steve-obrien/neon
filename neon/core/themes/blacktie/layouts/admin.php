<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
/**
 * @var \neon\core\web\View $this
 */
use yii\helpers\Html;
use neon\user\widgets\Impersonating;
use neon\admin\widgets\AdminFlash;
use neon\core\helpers\Page;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= neon()->language ?>">
	<head>
		<meta charset="<?= neon()->charset ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>

		<?php \yii\bootstrap\BootstrapAsset::register($this); ?>
		<?php \neon\core\themes\blacktie\AppAsset::register($this); ?>
		<?php $this->head() ?>
	</head>
	<body>
		<?php $this->beginBody(); ?>
		<div class="wrapper" id="neon">
			<header>
				<?= Page::menuAdmin(); ?>
			</header>
			<!-- impersonating -->
			<?= Impersonating::widget() ?>

			<!-- flash messages -->
			<div class='flash-messages'>
				<?= AdminFlash::widget() ?>
			</div>

			<div class="content">
				<div class="container-fluid">

					<?php $this->beginBlock('header', true); $this->endBlock(); ?>

					<!-- page content -->
					<div class="page-content">
						<?= $content; ?>
					</div>

				</div>
			</div>

			<footer class="footer">
				<div class="container">
					<p class="pull-left">&copy; <a href='http://newicon.net' target='_blank'>Newicon Ltd</a> <?= date('Y'); ?></p>
					<p class="pull-right"><?= neon()->powered(); ?></p>
				</div>
			</footer>

		</div>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
