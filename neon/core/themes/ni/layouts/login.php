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
	<?= $this->render('./_head.php') ?>
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
