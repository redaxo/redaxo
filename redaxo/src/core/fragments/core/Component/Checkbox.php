<?php

use Redaxo\Core\Fragment\Component\Checkbox;
use Redaxo\Core\Fragment\Fragment;

/** @var Checkbox $this */
?>
<sl-checkbox
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= is_string($this->label) ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->checked ? 'checked' : '' ?>
    <?= $this->indeterminate ? 'indeterminate' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
</sl-checkbox>
