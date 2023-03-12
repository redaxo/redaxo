<?php

use Redaxo\Core\Fragment\Component\Textarea;
use Redaxo\Core\Fragment\Fragment;

/** @var Textarea $this */
?>

<sl-textarea
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= is_string($this->label) ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= is_string($this->notice) ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->placeholder ? 'placeholder="'.rex_escape($this->placeholder).'"' : '' ?>
    rows="<?= $this->rows ?>"
    resize="<?= $this->resize->value ?>"
    <?= $this->readonly ? 'readonly' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->minlength ? 'minlength="'.$this->minlength.'"' : '' ?>
    <?= $this->maxlength ? 'maxlength="'.$this->maxlength.'"' : '' ?>
    <?= $this->autofocus ? 'autofocus' : '' ?>
    <?= $this->autocapitalize ? 'autocapitalize="'.$this->autocapitalize->value.'"' : '' ?>
    <?= null !== $this->spellcheck ? 'spellcheck="'.($this->spellcheck ? 'true' : 'false').'"' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
</sl-textarea>
