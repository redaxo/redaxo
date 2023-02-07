<?php

use Redaxo\Core\Fragment\Component\Choice;

/** @var Choice $this */
?><?php

$counter = 1;

/** @TODO Built better group wrapper and use label|SlotLabel, notice|SlotNotice and attributes.
 */
?>
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
