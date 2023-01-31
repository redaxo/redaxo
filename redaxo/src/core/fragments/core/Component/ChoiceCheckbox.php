<?php
/**
 * @var Choice $this
 * @psalm-scope-this Choice
 */

use Redaxo\Core\Fragment\Component\Choice;

$counter = 1;

/** @TODO Built better group wrapper, label and notice. */
?>
<div class="form-control form-control--medium form-control--has-label">
    <?php if ($this->label): ?>
        <label class="form-control__label" aria-hidden="false">
            <?= rex_escape($this->label) ?>
        </label>
    <?php endif ?>
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
            <?php endforeach; ?>
        <?php endif ?>
        <?php $counter++ ?>
    <?php endforeach; ?>
    <?php if ($this->notice): ?>
        <span class="form-control__help-text" aria-hidden="false">
            <?= rex_escape($this->notice) ?>
        </span>
    <?php endif ?>
</div>
