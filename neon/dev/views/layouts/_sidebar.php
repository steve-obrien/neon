<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 11/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
use \neon\core\helpers\Url;
?>
<div class="pure-menu">
	<a class="pure-menu-heading" href="#">NEON</a>
	<a href id="theme-light">Light</a> | <a href id="theme-dark">Dark</a>
	<ul class="nav--docs pure-menu-list">
		<?php foreach($menu as $app => $appMenuItems): ?>
			<li class="pure-menu-item pure-menu-section"><?= Ucwords($app) ?></li>
			<?php foreach($appMenuItems as $item): ?>
				<li class="pure-menu-item <?= (neon()->request->url == $item['link']) ? 'pure-menu-selected' : ''; ?>">
					<a class="pure-menu-link" href="<?= $item['link'] ?>" > - <?= $item['title'] ?></a>
				</li>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</ul>
</div>