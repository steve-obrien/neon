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

		<?php \neon\core\themes\neon\Assets::register($this); ?>
		<?php $this->head() ?>
	</head>
	<body>
	<?php $this->beginBody(); ?>
		<header>
			<?= Page::menuAdmin(); ?>
		</header>
		<!-- page content -->
	<style>
		#app {
			display: grid;
			width: 100vw;
			height: calc(100vh - 50px);
			grid-template-areas: 'header1 header2' 'sidebar content';
			grid-template-columns: 250px auto;
			grid-template-rows: 57px auto;
		}
		.app_sidebar {
			padding-bottom: 48px;
			background-color:#fbfcfd;
			box-shadow: inset -1px 0 0 #e3e5e8;
			grid-area: sidebar;
		}
		.app_content {
			grid-area: content;
			position:relative;
		}
		.app_header2,.app_header {
			box-shadow: 0 1px 0 rgba(0, 0, 20, 0.1);
			grid-area: header1;
			padding-left: 20px;
			padding-top: 12px;
			background: #e7eef3;
		}
		.app_header2{ grid-area: header2; }

		.list-group-item {font-size:14px; background:transparent; display:flex; align-items: center;}
		.list-group-item:first-child,.list-group-item:last-child  {border-radius: 0;}
		.list-group-item > .badge {margin-left:auto;}
		.utility { margin-left: 4px; }
		.mb-4 {margin-bottom: 1rem;}
		.pl-4 {padding-left:1rem;}
		.p-4 {padding:1rem;}
		.p-8 {padding:2rem;}
		.border-b {border-bottom:1px solid #eee;}
		.flash-messages { position:absolute; margin:0 auto; display:flex; left:0; right:0; width: 375px;}
	</style>
	<div class='flash-messages' style="padding-bottom:10px;">
		<?= AdminFlash::widget() ?>
	</div>
	<div id="app">
		<header class="app_header" style="top: 0px;">
			<h2 class="neonToolbar_title" title="System Report"><?= isset($this->title) ? $this->title : '' ?></h2>
		</header>
		<header class="app_header2" style="top: 0px;">

		</header>
		<div class="app_sidebar">
			<div class="list-group">
				<?php include __DIR__."/_sidebar.php" ?>
			</div>
		</div>
		<div class="app_content">
			<?= $content ?>
		</div>
	</div>
	<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
