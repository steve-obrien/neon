<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
use yii\helpers\Html;
use yii\widgets\Menu;
use neon\user\widgets\Impersonating;
use neon\admin\widgets\AdminFlash;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= neon()->language ?>">
	<head>
		<meta charset="<?= neon()->charset ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>
<!--		<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">-->
		<?php \neon\core\assets\AppAsset::register($this); ?>
		<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
		<?php $this->head() ?>
	</head>
	<body>
		<?php $this->beginBody(); ?>
		<div class="wrapper">
			<header>
				<?= Page::menuAdmin(); ?>
			</header>
			<div class="content">
				<div class="container-fluid">
					<!-- impersonating -->
					<?= Impersonating::widget() ?>

					<!-- flash messages -->
					<div class='flash-messages'>
						<?= AdminFlash::widget() ?>
					</div>

					<?php $this->beginBlock('header', true); $this->endBlock(); ?>

					<!-- page content -->
					<div class='page-content'>
						<?= $content; ?>
					</div>

				</div>
			</div>
		</div>
		<footer class="footer">
			<div class="container">
				<p class="pull-left">&copy; <?= neon()->name; ?> <?= date('Y'); ?></p>
				<p class="pull-right"><?= neon()->powered(); ?></p>
			</div>
		</footer>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
