<?php use yii\helpers\Html; ?>
<meta charset="<?= neon()->charset ?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= Html::csrfMetaTags() ?>
<title><?= Html::encode($this->title) ?></title>
<?php \neon\core\themes\ni\Assets::register($this); ?>
<?php $this->head() ?>
