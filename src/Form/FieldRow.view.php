<?php

use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\HtmlAttributes;

return static function (FieldRow $row): void { ?>
    <dl <?= new HtmlAttributes(['class' => ['rex-form-group', 'form-group', 'rex-form-group-vertical' => $row->vertical, 'has-error' => null !== $row->error, 'rex-is-required' => $row->required]]) ?>>
        <?php if (null !== $row->label): ?>
            <dt><label <?= new HtmlAttributes(['for' => $row->labelFor()]) ?>><?= Html::from($row->label) ?></label></dt>
        <?php endif ?>
        <dd>
            <?= $row->widget->render() ?>
            <?php if (null !== $row->note): ?>
                <p class="help-block rex-note"><?= Html::from($row->note) ?></p>
            <?php endif ?>
            <?php if (null !== $row->error): ?>
                <p class="rex-form-error"><?= Html::from($row->error) ?></p>
            <?php endif ?>
        </dd>
    </dl>
<?php };
