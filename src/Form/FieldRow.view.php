<?php

use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\View\HtmlAttributes;

use function Redaxo\Core\View\escape;

return static function (FieldRow $row): void { ?>
    <dl <?= new HtmlAttributes(['class' => ['rex-form-group', 'form-group', 'has-error' => null !== $row->error, 'rex-is-required' => $row->required]]) ?>>
        <?php if (null !== $row->label): ?>
            <dt><label <?= new HtmlAttributes(['for' => $row->labelFor()]) ?>><?= escape((string) $row->label) ?></label></dt>
        <?php endif ?>
        <dd>
            <?= $row->widget->render() ?>
            <?php if (null !== $row->note): ?>
                <p class="help-block rex-note"><?= escape((string) $row->note) ?></p>
            <?php endif ?>
            <?php if (null !== $row->error): ?>
                <p class="rex-form-error"><?= escape((string) $row->error) ?></p>
            <?php endif ?>
        </dd>
    </dl>
<?php };
