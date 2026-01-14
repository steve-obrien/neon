<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

\neon\core\themes\neon\Assets::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" style="background-color:#f1f1f1;">
	<head>
		<?= $this->render('./_head.php') ?>
	</head>
	<body>
		<?php $this->beginBody() ?>
			<?php echo $content; ?>
		<?php $this->endBody() ?>
	</body>
</html>
<?php $this->endPage() ?>
