<?php
use yii\helpers\Html;
use neon\admin\widgets\AdminFlash;
/* @var $this \yii\web\View */
/* @var $content string */

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= neon()->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<!-- <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css"> -->
	<?php \neon\core\themes\neon\Assets::register($this); ?>
	<?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
	<div class="container block block-fill-height" style="height:100vh;text-align:center">
		<div class="block-sm-middle">
			<div class="container" style="width:400px">
				<!-- flash messages -->
				<div class="panel">
					<div class="flash-messages">
						<?= AdminFlash::widget() ?>
					</div>
					<div class="panel-body">
						<?= $content ?>
					</div>
				</div>
				<footer class="footer">
					<p class="pull-left">&copy; <a href='https://newicon.net' target='_blank'>Newicon</a> <?= date('Y'); ?></p>
					<p class="pull-right"><?= neon()->powered(); ?></p>
				</footer>
			</div>
		</div>
	</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
