<?php

use Redaxo\Core\Fragment\Component\Choice;
use Redaxo\Core\Fragment\Fragment;

/** @var Choice $this */
?><?php

$counter = 1;
?>
<sl-radio-group <?= $this->attributes->with([
    'name' => $this->name,
    'value' => implode(' ', $this->getValues()),
    'label' => is_string($this->label) ? $this->label : null,
    'help-text' => is_string($this->notice) ? $this->notice : null,
    'required' => $this->required,
])->toString() ?>>
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
