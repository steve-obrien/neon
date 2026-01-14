<?php
use neon\core\helpers\Html;
use yii\bootstrap\ActiveForm;
use neon\admin\widgets\AdminHeader;
$this->title = 'Pending Invites';
$this->params['breadcrumbs'][] = $this->title;
?>
<?= AdminHeader::widget(['title'=>'Add User', 'buttons'=>[ Html::buttonCancel(['/user/index/index']) ]]); ?>

<?= $inviteGrid->run() ?>
