<?php
use neon\core\helpers\Html;
use neon\admin\widgets\AdminHeader;
use \yii\widgets\Pjax;
/* @var $searchModel neon\user\models\UserSearch */

$this->title = 'Users';
$this->params['breadcrumbs'] = [
	$this->title
];
?>
<?= 
	AdminHeader::widget([
		'title' => 'Users',
		'buttons' => [
			Html::buttonAdd(['/user/index/add'], 'Add User'),
			setting('user', 'allowUserInvitations', false) ? Html::buttonAdd(['/user/invite/index'], 'Invite User') : null,
			setting('user', 'allowUserInvitations', false) ? Html::buttonCustom(['/user/invite/pending'], 'Pending Invites', ['icon'=>'fa fa-envelope', 'class'=>'btn-primary']) : null,
		]
	]); 
?>

<?= $userGrid->run() ?>
