<?php

use Redaxo\Core\Fragment\Component\Checkbox;

/** @var Checkbox $this */
?>
<sl-checkbox
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->checked ? 'checked' : '' ?>
    <?= $this->indeterminate ? 'indeterminate' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->slotLabel ? $this->slotLabel->prepare('label')->get() : '' ?>
</sl-checkbox>
