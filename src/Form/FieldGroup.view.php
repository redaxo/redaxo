<?php

use Redaxo\Core\Form\FieldGroup;

use function Redaxo\Core\View\escape;

return static function (FieldGroup $group): void { ?>
    <fieldset>
        <?php if (null !== $group->legend): ?>
            <legend><?= escape((string) $group->legend) ?></legend>
        <?php endif ?>
        <?= $group->content ?>
    </fieldset>
<?php };
