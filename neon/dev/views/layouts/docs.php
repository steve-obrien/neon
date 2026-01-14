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
		<meta charset="<?= neon()->charset ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>

		<?php \neon\dev\assets\DocsAsset::register($this); ?>
		<?php \yii\bootstrap\BootstrapAsset::register($this); ?>
		<?php \neon\core\themes\blacktie\AppAsset::register($this); ?>
		<link rel="stylesheet" href="https://unpkg.com/purecss@0.6.0/build/pure-min.css">

		<?php $this->head() ?>
	</head>
	<body class="hljs-light">
		<?php $this->beginBody(); ?>

		<div id="layout" class="docs">
			<!-- Menu toggle -->
			<a href="#menu" id="menuLink" class="menu-link">
				<!-- Hamburger icon -->
				<span></span>
			</a>

			<section id="menu" class="docs__sidebar">
				<?= $this->render('_sidebar', ['menu' => $this->params['menu']]); ?>
			</section>

			<div id="main">
				<article class="docs__article">
					<!-- page content -->
					<?= $content; ?>
				</article>
			</div>
		</div>


		<?php $this->endBody(); ?>
		<script>
			(function (window, document) {

				var layout   = document.getElementById('layout'),
					menu     = document.getElementById('menu'),
					menuLink = document.getElementById('menuLink'),
					content  = document.getElementById('main');

				function toggleClass(element, className) {
					var classes = element.className.split(/\s+/),
						length = classes.length,
						i = 0;

					for(; i < length; i++) {
						if (classes[i] === className) {
							classes.splice(i, 1);
							break;
						}
					}
					// The className is not found
					if (length === classes.length) {
						classes.push(className);
					}

					element.className = classes.join(' ');
				}

				function toggleAll(e) {
					var active = 'active';

					e.preventDefault();
					toggleClass(layout, active);
					toggleClass(menu, active);
					toggleClass(menuLink, active);
				}

				menuLink.onclick = function (e) {
					toggleAll(e);
				};

				content.onclick = function(e) {
					if (menu.className.indexOf('active') !== -1) {
						toggleAll(e);
					}
				};

			}(this, this.document));
			$(function(){
				$('#theme-light').click(function(){$('body,html').removeClass('hljs-light').removeClass('hljs-dark').addClass('hljs-light'); return false;});
				$('#theme-dark').click(function(){$('body,html').removeClass('hljs-light').removeClass('hljs-dark').addClass('hljs-dark'); return false;})
			});
		</script>
	</body>
	</html>
<?php $this->endPage(); ?>