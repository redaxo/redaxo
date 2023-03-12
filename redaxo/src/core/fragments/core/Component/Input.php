<?php

use Redaxo\Core\Fragment\Component\Input;
use Redaxo\Core\Fragment\Fragment;

/** @var Input $this */
?>

<sl-input
    type="<?= $this->type->value ?>"
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= is_string($this->label) ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= is_string($this->notice) ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->placeholder ? 'placeholder="'.rex_escape($this->placeholder).'"' : '' ?>
    <?= $this->readonly ? 'readonly' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->pattern ? 'pattern="'.rex_escape($this->pattern).'"' : '' ?>
    <?= $this->minlength ? 'minlength="'.$this->minlength.'"' : '' ?>
    <?= $this->maxlength ? 'maxlength="'.$this->maxlength.'"' : '' ?>
    <?= $this->min ? 'min="'.rex_escape($this->min).'"' : '' ?>
    <?= $this->max ? 'max="'.rex_escape($this->max).'"' : '' ?>
    <?= $this->step ? 'step="'.rex_escape($this->step).'"' : '' ?>
    <?= $this->autocapitalize ? 'autocapitalize="'.$this->autocapitalize->value.'"' : '' ?>
    <?= null !== $this->spellcheck ? 'spellcheck="'.($this->spellcheck ? 'true' : 'false').'"' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
    <?= Fragment::slot($this->prefix, 'prefix') ?>
    <?= Fragment::slot($this->suffix, 'suffix') ?>
</sl-input>
