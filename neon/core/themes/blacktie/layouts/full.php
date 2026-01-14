<?php
use yii\helpers\Html;
use \neon\core\themes\blacktie\AppAsset;
use neon\core\helpers\Url as Url;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
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
	</head>
	<body>
		<?php $this->beginBody() ?>
			<?php echo $content; ?>
		<?php $this->endBody() ?>
	</body>
</html>
<?php $this->endPage() ?>