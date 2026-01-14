<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Please choose your new password:</p>

<?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
	<?= $form->field($model, 'password')->passwordInput() ?>
	<div class="form-group">
		<?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-lg btn-block']) ?>
	</div>
<?php ActiveForm::end(); ?>
