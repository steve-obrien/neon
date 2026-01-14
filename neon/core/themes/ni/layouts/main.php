<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

/* @var $this \yii\web\View */
/* @var $content string */

$this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= \Yii::$app->language ?>">
<head>
	<?= $this->render('./_head.php') ?>
</head>
<body>

	<?php $this->beginBody() ?>
	<div class="wrapper">
		<?php
			NavBar::begin(['brandLabel'=>'app.name', 'options'=>['class'=>'navbar-inverse']]);
			Nav::widget(
				[
					'options' => [
					'class' => 'navbar-nav navbar-right ',
					],
					'items' =>  [
						['label'  => 'Home', 'url'  => ['/core/site/index']],
						['label'  => 'Login', 'url'  => ['/user/account/login']]
					]
				]
			);
			NavBar::end();
		?>
		<div class="container">

			<!-- flash messages -->
<!--			<div class='flash-messages'>-->
<!--				{{ use('/neon/admin/widgets') }}-->
<!--				--><?php // ?>
<!---->
<!--				{{ admin_flash_widget() }}-->
<!--			</div>-->

			<?= $content ?>


		</div>
	</div>

	<footer class="footer">
		<div class="container">
			<p class="pull-left">
				© Newicon Ltd <?= date('Y') ?>
			</p>
		</div>
	</footer>
	<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
