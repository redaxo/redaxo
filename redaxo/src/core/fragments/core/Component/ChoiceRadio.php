<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Fragment;

/** @var Choice $this */
?><?php

$counter = 1;
?>
<sl-radio-group
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape(implode(' ', $this->value)).'"' : '' ?>
    <?= is_string($this->label) ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= is_string($this->notice) ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?php foreach ($this->getChoices() as $label => $value): ?>
        <?php if (!is_array($value)): ?>
            <sl-radio
                value="<?= rex_escape($value) ?>"
                <?= $this->disabled ? 'disabled' : '' ?>
            >
                <?= rex_escape($label) ?>
            </sl-radio>
        <?php else: ?>
            <?php if (1 !== $counter): ?>
                <sl-divider></sl-divider>
            <?php endif ?>
            <small><?= rex_escape($label) ?></small>
            <?php foreach ($value as $nestedLabel => $nestedValue): ?>
                <sl-radio
                    value="<?= rex_escape($nestedValue) ?>"
                    <?= $this->disabled ? 'disabled' : '' ?>
                >
                    <?= rex_escape($nestedLabel) ?>
                </sl-radio>
            <?php endforeach ?>
        <?php endif ?>
        <?php $counter++ ?>
    <?php endforeach ?>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
</sl-radio-group>
