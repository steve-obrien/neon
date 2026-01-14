<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 11/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
use \neon\core\helpers\Page;
list($path, $url) = neon()->dev->publishAssets();
neon()->view->registerJsFile("$url/vendor/highlight.js");
?>
<link href="<?= "$url/docs-style.css" ?>" rel="stylesheet" type="text/css" />

<?= $content; ?>

<?php Page::jsBegin();?>
<script>
$(function(){
	$('code:not(language-*)').each(function(i, block) {
		hljs.highlightBlock(block);
	});
})
</script>
<?php Page::jsEnd();?>
