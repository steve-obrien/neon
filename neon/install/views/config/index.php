<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url as Url;
use yii\helpers\Html as Html;

?>

<!-- @test: enter database details -->
<h3>Database Connection</h3>
<p>Enter your database connection details below:</p>

<?php $form = ActiveForm::begin([
	'layout' => 'horizontal',
	'options'=>['class' => 'neon-form'],
	'enableAjaxValidation' => true,
	'fieldConfig' => [
		'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
		'horizontalCssClasses' => [
			'label' => 'col-sm-3',
			'offset' => 'col-sm-offset-4',
			'wrapper' => 'col-sm-8',
			'error' => '',
			'hint' => '',
		]
	]
]); ?>

	<?php if ($model->hasErrors()): ?>
		<div class="well " role="alert" >
			<?php if ($model->error_message == 'unknown_database') { ?>
				<!-- @test: cant select database -->
				<h3 style="margin-top:0">Can’t select database</h3>
				<p>We were able to connect to the database server (which means your username and password are fine) but we were not able to select the <?= $model->name ?> database.</p>
				<ul style="padding-top:10px;">
					<li>Are you sure it exists?</li>
					<li>Does the user <?= $model->username ?> have permission to use the <?= $model->name ?> database?</li>
				</ul>
				<span class="help-block">You could try the "Submit and Create Database" button below which will attempt to create the &quot;<?= $model->name ?>&quot; database.</span>
			<?php } elseif ($model->error_message == 'cant_create_database') { ?>
				<h3 style="margin-top:0">Failed to create the database</h3>
				<p>Is the database name in a suitable format for your database?</p>
			<?php } elseif ($model->error_message == 'access_denied') { ?>
				<h3 style="margin-top:0">Error authenticating database connection</h3>
				<p>We seem to have found a database running at <?= $model->host ?>. But the username and password information seems incorrect</p>
				<ul style="padding-top:10px;">
					<li>Are you sure you have the correct username and password?</li>
				</ul>
			<?php } else { ?>
				<h3 style="margin-top:0">Error establishing database connection</h3>
				<p>We can't contact the database server at <?= $model->host ?>. This could mean your host's database server is down.</p>
				<ul style="padding-top:10px;">
					<li>Are you sure that you have typed the correct hostname?</li>
					<li>Are you sure you have the correct username and password?</li>
					<li>Are you sure that the database server is running?</li>
				</ul>
			<?php } ?>
		</div>
	<?php endif; ?>
	<hr/>

	<?= $form->field($model, 'username', ['inputOptions'=>['autocomplete'=>'false']])->hint('Your MySQL username.') ?>
	<?= $form->field($model, 'password')->passwordInput()->hint('Your MySQL password.') ?>
	<?= $form->field($model, 'host')->hint('The MySQL host') ?>
	<hr/>
	<?= $form->field($model, 'name')->hint('The name of the database you want to install neon in.') ?>
	<hr/>
	<?= $form->field($model, 'table_prefix')->hint('Optional: If you want to run multiple neon installations in a single database, change this.') ?>
	<hr/>
	<p>Enter the environment information:</p>
	<hr/>
	<?= $form->field($model, 'environment')->hint('The environment you are installing to e.g. prod, dev, test')->dropDownList(['prod'=>'Production', 'dev'=>'Development', 'test'=>'Testing']) ?>
	<?= $form->field($model, 'debug')->hint('Set whether or not this is in debug mode')->dropDownList(['true'=>'In Debug', 'false'=>'Not in Debug']) ?>
	<?php
		$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		$choices = array_combine($timezones, $timezones);
		echo $form->field($model, 'timezone')->hint('Set the timezone')->dropDownList($choices);
	?>

	<div class="form-group">
		<div class="col-sm-8 col-sm-push-3">
			<button id="submit_database_install" type="submit" class="btn btn-primary" data-cmd="submit_database_install">Submit <span class="glyphicon glyphicon-chevron-right"></span></button>
			<?php if ($model->error_message == 'unknown_database'): ?>
				<!-- @test: create database table -->
				<button type="submit" name="InstallDatabase[create_database]" value="1" class="btn btn-primary" data-cmd="submit_create_database_install">
					<span class="fa fa-database" aria-hidden="true"></span> Submit and Create Database
					<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>

	</div>

<?php ActiveForm::end(); ?>