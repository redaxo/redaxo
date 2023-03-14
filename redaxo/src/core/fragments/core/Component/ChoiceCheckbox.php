<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/** @var Choice $this */
?><?php

$counter = 1;
$random = random_int(100, 999).random_int(100, 999);
$values = $this->getValues();

?>
<fieldset <?= $this->attributes->with([
    'aria-labelledby' => $this->label ? 'label-'.$random : null,
    'aria-describedby' => $this->notice ? 'help-text-'.$random : null,
    'class' => [
        'form-control',
        'form-control--medium',
        'form-control--has-label' => (bool) $this->label,
        'form-control--has-help-text' => (bool) $this->notice,
    ],
])->toString() ?>>
    <?php if ($this->label): ?>
        <label class="form-control__label" id="label-<?= $random ?>" aria-hidden="false">
            <?php if (is_string($this->label)): ?>
                <?= rex_escape($this->label) ?>
            <?php else: ?>
                <?= Fragment::slot($this->label) ?>
            <?php endif ?>
        </label>
    <?php endif ?>

    <div class="form-control-input">
        <?php foreach ($this->getChoices() as $groupLabel => $group): ?>
            <?php if (1 !== $counter): ?>
                <sl-divider></sl-divider>
            <?php endif ?>
            <?php if (null !== $groupLabel): ?>
                <small><?= rex_escape($groupLabel) ?></small>
            <?php endif ?>
            <?php foreach ($group as $label => $value): ?>
                <sl-checkbox <?= (new HtmlAttributes([
                    'value' => $value,
                    'name' => $this->name,
                    'disabled' => $this->disabled,
                    'checked' => in_array($value, $values),
                ]))->toString() ?>>
                    <?= rex_escape($label) ?>
                </sl-checkbox>
            <?php endforeach ?>
            <?php $counter++ ?>
        <?php endforeach ?>
    </div>
    <?php if ($this->notice): ?>
        <div class="form-control__help-text" id="help-text-<?= $random ?>" aria-hidden="false">
            <?php if (is_string($this->notice)): ?>
                <?= rex_escape($this->notice) ?>
            <?php elseif ($this->label instanceof Fragment): ?>
                <?= Fragment::slot($this->notice) ?>
            <?php endif ?>
        </div>
    <?php endif ?>
</fieldset>
