<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 05/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 *
 * @var array $pages
 * @var string $id - the nice id of the page to load
 */

use neon\core\assets\JQueryUiAsset;
use \neon\core\widgets\Js;
use \neon\core\helpers\Url;
use \neon\core\helpers\Page;
use \neon\core\helpers\Html;
neon()->view->registerAssetBundle(\neon\cms\assets\CmsEditorAsset::class);
$slug = new \neon\cms\form\fields\Slug('slug');
$slug->registerScripts($this);
?>

<style>
	#neon {height:100%;}
</style>
<div id="cobe">
	<cobe :load-pages='<?= Html::encode(json_encode($pages)) ?>' start-page='<?= $id; ?>'></cobe>
</div>

<!--<script src="https://cdn.jsdelivr.net/npm/vuex-iframe-sync/dist/vuex-iframe-sync.umd.js"></script>-->
<?php Page::JsBegin(); ?>
<script>
	new Vue({el:'#cobe'});
</script>
<?php Page::JsEnd(); ?>