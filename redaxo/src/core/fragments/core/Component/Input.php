<?php

use Redaxo\Core\Fragment\Component\Input;
use Redaxo\Core\Fragment\Fragment;

/** @var Input $this */
?>

<sl-input <?= $this->attributes->with([
    'type' => $this->type,
    'name' => $this->name,
    'value' => $this->value,
    'label' => is_string($this->label) ? $this->label : null,
    'help-text' => is_string($this->notice) ? $this->notice : null,
    'disabled' => $this->disabled,
    'placeholder' => $this->placeholder,
    'readonly' => $this->readonly,
    'required' => $this->required,
    'pattern' => $this->pattern,
    'minlength' => $this->minlength,
    'maxlength' => $this->maxlength,
    'min' => $this->min,
    'max' => $this->max,
    'step' => $this->step,
    'autocapitalize' => $this->autocapitalize,
    'spellcheck' => null !== $this->spellcheck ? ($this->spellcheck ? 'true' : 'false') : null,
]) ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
    <?= Fragment::slot($this->prefix, 'prefix') ?>
    <?= Fragment::slot($this->suffix, 'suffix') ?>
</sl-input>
