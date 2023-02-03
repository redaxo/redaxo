<?php
/**
 * @var Choice $this
 * @psalm-scope-this Choice
 */

use Redaxo\Core\Fragment\Component\Choice;

$counter = 1;
?>
<sl-radio-group
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape(implode(' ', $this->value)).'"' : '' ?>
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->notice ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?php foreach ($this->getChoices() as $label => $value): ?>
        <?php if (!is_array($value)): ?>
            <sl-radio value="<?= rex_escape($value) ?>"<?= $this->disabled ? 'disabled' : '' ?>><?= rex_escape($label) ?></sl-radio>
        <?php else: ?>
            <?php if (1 !== $counter): ?>
            <sl-divider></sl-divider>
            <?php endif ?>
            <small><?= rex_escape($label) ?></small>
            <?php foreach ($value as $nestedLabel => $nestedValue): ?>
                <sl-radio value="<?= rex_escape($nestedValue) ?>"<?= $this->disabled ? 'disabled' : '' ?>><?= rex_escape($nestedLabel) ?></sl-radio>
            <?php endforeach; ?>
        <?php endif ?>
        <?php $counter++ ?>
    <?php endforeach; ?>
    <?= $this->slotLabel ? $this->slotLabel->prepare('label')->get() : '' ?>
    <?= $this->slotNotice ? $this->slotNotice->prepare('help-text')->get() : '' ?>
</sl-radio-group>
