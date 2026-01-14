<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/12/2016 19:27
 * @package neon
 */
?>

<h1>404! <?= $message ?></h1>


<p>We could not find the page you were looking for. This is either because:</p>
<ul>
    <li>There is a typing error in the URL entered into your web browser. Please check the URL and try again.</li>
    <li>The page you are looking for has been moved or deleted.</li>
</ul>
<p>You can return to the <a href="<?= neon()->getHomeUrl() ?>">home page by clicking here</a></p>