<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 08/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
use yii\helpers\Html;
use neon\user\widgets\Impersonating;
use neon\admin\widgets\AdminFlash;
use neon\core\helpers\Page;
?>
<?php $this->beginPage(); ?>
	<!DOCTYPE html>
	<html lang="<?= neon()->language ?>">
	<head>
		<?= $this->render('./_head.php') ?>
	</head>
	<body>
	<?php $this->beginBody(); ?>
		<div id="neon">
			<header>
				<?= Page::menuAdmin(); ?>
			</header>

			<!-- page content -->
			<?= $content; ?>

		</div>

		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>
