<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @package neon/phoebe
 */


use neon\phoebe\builders\appforms\assets\AppFormBuilderAsset;

AppFormBuilderAsset::register($this);

?>

<script type="application/javascript">
    // We bootstrap the form builder with the form definition and the available classes here.
    window.APP_FORM = {
        definition: <?= $definition ?>,
        classes: <?= $classes ?>
    }
</script>
<div style="height: calc(100vh - 42px)">
    <div id=app></div>
</div>


