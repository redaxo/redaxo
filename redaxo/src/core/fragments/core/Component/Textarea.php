<?php

use Redaxo\Core\Fragment\Component\Textarea;
use Redaxo\Core\Fragment\Fragment;

/** @var Textarea $this */
?>

<sl-textarea <?= $this->attributes->with([
    'name' => $this->name,
    'value' => $this->value,
    'label' => is_string($this->label) ? $this->label : null,
    'help-text' => is_string($this->notice) ? $this->notice : null,
    'disabled' => $this->disabled,
    'placeholder' => $this->placeholder,
    'rows' => $this->rows,
    'resize' => $this->resize,
    'readonly' => $this->readonly,
    'required' => $this->required,
    'minlength' => $this->minlength,
    'maxlength' => $this->maxlength,
    'autofocus' => $this->autofocus,
    'autocapitalize' => $this->autocapitalize,
    'spellcheck' => null !== $this->spellcheck ? ($this->spellcheck ? 'true' : 'false') : null,
])->toString() ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
</sl-textarea>
