<?php

use Redaxo\Core\Form\FieldGroup;
use Redaxo\Core\View\Html;

return static function (FieldGroup $group): void { ?>
    <fieldset>
        <?php if (null !== $group->legend): ?>
            <legend><?= Html::from($group->legend) ?></legend>
        <?php endif ?>
        <?= $group->content ?>
    </fieldset>
<?php };
