<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 13/06/2017
 * Time: 17:04
 */


$form = new \neon\core\form\Form('butt');
$form->addFieldImage('profile');
$form->addFieldImage('thumbnail');
?>

<?= $form->run() ?>

<!--<pre>{{$store.state}}</pre>-->