<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Fragment;

/** @var Choice $this */
?><?php

$counter = 1;

$random = rand(100, 999).rand(100, 999);
$attributes = [
    'class' => 'form-control form-control--medium',
];
if ($this->label) {
    $attributes['aria-labelledby'] = 'label-'.$random;
    $attributes['class'] .= ' form-control--has-label';
}
if ($this->notice) {
    $attributes['aria-describedby'] = 'help-text-'.$random;
    $attributes['class'] = ' form-control--has-help-text';
}
$this->attributes = array_merge_recursive($this->attributes, $attributes);
?>
<fieldset<?= rex_string::buildAttributes($this->attributes) ?>>
    <?php if ($this->label): ?>
        <label class="form-control__label" id="label-<?= $random ?>" aria-hidden="false">
            <?php if (is_string($this->label)): ?>
                <?= rex_escape($this->label) ?>
            <?php elseif ($this->label instanceof Fragment): ?>
                <?= Fragment::slot($this->label) ?>
            <?php endif ?>
        </label>
    <?php endif ?>

    <div class="form-control-input">
        <?php foreach ($this->getChoices() as $label => $value): ?>
            <?php if (!is_array($value)): ?>
                <sl-checkbox
                    value="<?= rex_escape($value) ?>"
                    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
                    <?= $this->disabled ? 'disabled' : '' ?>
                    <?= in_array($value, $this->value) ? 'checked' : '' ?>
                >
                    <?= rex_escape($label) ?>
                </sl-checkbox>
            <?php else: ?>
                <?php if (1 !== $counter): ?>
                    <sl-divider></sl-divider>
                <?php endif ?>
                <small><?= rex_escape($label) ?></small>
                <?php foreach ($value as $nestedLabel => $nestedValue): ?>
                    <sl-checkbox
                        value="<?= rex_escape($nestedValue) ?>"
                        <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
                        <?= $this->disabled ? 'disabled' : '' ?>
                        <?= in_array($nestedValue, $this->value) ? 'checked' : '' ?>
                    >
                        <?= rex_escape($nestedLabel) ?>
                    </sl-checkbox>
                <?php endforeach ?>
            <?php endif ?>
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
