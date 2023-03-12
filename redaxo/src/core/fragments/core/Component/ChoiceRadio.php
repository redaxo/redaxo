<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Fragment;

/** @var Choice $this */
?><?php

$counter = 1;
?>
<sl-radio-group
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape(implode(' ', $this->getValues())).'"' : '' ?>
    <?= is_string($this->label) ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= is_string($this->notice) ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?php foreach ($this->getChoices() as $groupLabel => $group): ?>
        <?php if (1 !== $counter): ?>
            <sl-divider></sl-divider>
        <?php endif ?>
        <?php if (null !== $groupLabel): ?>
            <small><?= rex_escape($groupLabel) ?></small>
        <?php endif ?>
        <?php foreach ($group as $label => $value): ?>
            <sl-radio
                value="<?= rex_escape($value) ?>"
                <?= $this->disabled ? 'disabled' : '' ?>
            >
                <?= rex_escape($label) ?>
            </sl-radio>
        <?php endforeach ?>
        <?php $counter++ ?>
    <?php endforeach ?>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
</sl-radio-group>
