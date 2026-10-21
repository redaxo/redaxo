<?php

use Redaxo\Core\Backend\Form\PermissionsInput;
use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\Form\Widget\CheckboxInput;
use Redaxo\Core\Form\Widget\ChoiceList;
use Redaxo\Core\Form\Widget\SelectInput;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\HtmlAttributes;

return static function (PermissionsInput $input): void { ?>
    <?php if (null !== $input->error): ?>
        <p class="rex-form-error"><?= Html::from($input->error) ?></p>
    <?php endif ?>
    <?php foreach ($input->groups as $group): ?>
        <?php
        $id = $input->groupId($group['key']);
        $allLabel = $group['allLabel'];
        $select = new SelectInput($input->name, $group['choices'], $group['selected'], $id, multiple: true);
        $widget = null === $allLabel ? new ChoiceList($input->name, $group['choices'], $group['selected'], $id, multiple: true) : Html::capture(static function () use ($input, $group, $allLabel, $id, $select): void { ?>
            <?= new CheckboxInput(
                $input->name . '[]',
                $allLabel,
                $group['all'],
                $group['key'],
                $id . '-all',
                attributes: new HtmlAttributes(['data-rex-hides' => '#' . $id . '-items']),
            )->render() ?>
            <div <?= new HtmlAttributes(['id' => $id . '-items']) ?>><?= $select->render() ?></div>
        <?php });
        ?>
        <?= new FieldRow($widget, $group['label'], null === $allLabel ? null : $id)->render() ?>
    <?php endforeach ?>
<?php };
