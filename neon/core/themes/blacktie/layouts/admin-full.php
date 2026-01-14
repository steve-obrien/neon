<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
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
		<header>
			<?= Page::menuAdmin(); ?>
		</header>

		<?php //$this->beginBlock('header', true); $this->endBlock(); ?>
		<!-- page content -->
		<?= $content; ?>


		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>