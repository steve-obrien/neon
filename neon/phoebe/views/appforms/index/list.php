<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 21/10/2016 01:37
 * @package neon
 *
 *
 * @var \neon\core\form\Form $form
 */
use \neon\core\helpers\Page;

/** A local fix to prevent image previews being huge in the grid or columns too wide**/
neon()->view->registerCss('.table img { max-width:100px; max-height:100px } .table td, .table tr { max-width:16em; }');
?>
<header class="workbenchHeader">
	<div class="workbenchHeader_left">
		<h1 class="workbenchHeader_title"><?= $class['label']; ?></h1>
	</div>
	<div class="toolbar">
		<a href="<?= url(['/phoebe/appforms/index/list-objects', 'type' => $class['class_type'] ]) ?>" class="btn btn-default active">
			<i class="fa fa-list"></i> List
		</a>
		<a href="<?= url(['/phoebe/appforms/index/add-object', 'type' => $class['class_type'] ]) ?>" class="btn btn-default">
			<i class="fa fa-plus"></i> Add New
		</a>
		<?php if ($can_develop) { ?>
			<span class="toolbar__divider">|</span>
			<a href="<?= url(['/phoebe/appforms/index/edit', 'type' => $class['class_type'] ]) ?>" class="btn btn-warning">
				<i class="fa fa-pencil"></i> Edit Definition
			</a>
		<?php } ?>
		<span class="toolbar__divider">|</span>
		<a href="<?= url(['/phoebe/appforms/index/index']) ?>" class="btn btn-default">
			<i class="fa fa-chevron-left"></i> Back to Index
		</a>
	</div>
</header>
<div class="workbenchBody_content">
	<div style="padding:0px 20px;">
		<?= $grid->run() ?>
	</div>
</div>
