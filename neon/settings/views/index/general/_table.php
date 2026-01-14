<?php
use yii\helpers\Html;

/* @var $caption string */
/* @var $values array */
?>

<h4><?= $caption ?></h4>

<?php if (empty($values)): ?>

    <p>Empty.</p>

<?php else: ?>
    <div class="table-responsive">
        <table class="table table-condensed      table-hover" style="table-layout: fixed;">
            <tbody>
            <?php foreach ($values as $name => $value): ?>
                <tr>
                    <td style="white-space: normal"><?= Html::encode($name) ?></td>
                    <td style="overflow:auto"><?= Html::encode($value) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
