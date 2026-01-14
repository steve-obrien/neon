<?php
/**
 * @var \yii\web\View $this
 * @var string $url
 * @var int $count
 */
?>
<div class="yii-debug-toolbar__block">
	<a href="<?= $panel->getUrl() ?>">
		Neon
		<span class="yii-debug-toolbar__label yii-debug-toolbar__label_info">
            <?= $panel->data['application']['version'] ?>
        </span>
		Yii
		<span class="yii-debug-toolbar__label"><?= $panel->data['application']['yii'] ?></span>
		PHP
		<span class="yii-debug-toolbar__label"><?= $panel->data['php']['version'] ?></span>
		<span onclick="var e=document.getElementById('yii-debug-toolbar');e.parentNode.removeChild(e);">X</span>
	</a>
</div>
