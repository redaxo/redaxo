<?php
/**
 * @var Choice $this
 * @psalm-scope-this Choice
 */

use Redaxo\Core\Fragment\Component\Choice;

$counter = 1;
?>
<sl-select
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape(implode(' ', $this->value)).'"' : '' ?>
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->notice ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->multiple ? 'multiple' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->placeholder ? 'placeholder="'.rex_escape($this->placeholder).'"' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?php foreach ($this->getChoices() as $label => $value): ?>
        <?php if (!is_array($value)): ?>
            <sl-option value="<?= rex_escape($value) ?>"><?= rex_escape($label) ?></sl-option>
        <?php else: ?>
            <?php if (1 !== $counter): ?>
            <sl-divider></sl-divider>
            <?php endif ?>
            <small><?= rex_escape($label) ?></small>
            <?php foreach ($value as $nestedLabel => $nestedValue): ?>
                <sl-option value="<?= rex_escape($nestedValue) ?>"><?= rex_escape($nestedLabel) ?></sl-option>
            <?php endforeach; ?>
        <?php endif ?>
        <?php $counter++ ?>
    <?php endforeach; ?>
    <?= $this->slotLabel ? $this->slotLabel->prepare('label')->get() : '' ?>
    <?= $this->slotNotice ? $this->slotNotice->prepare('help-text')->get() : '' ?>
</sl-select>
