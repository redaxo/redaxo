<?php
/**
 * @var Textarea $this
 * @psalm-scope-this Textarea
 */

use Redaxo\Core\Fragment\Component\Textarea;

?>

<sl-textarea
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->notice ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->placeholder ? 'placeholder="'.rex_escape($this->placeholder).'"' : '' ?>
    rows="<?= rex_escape($this->rows) ?>"
    resize="<?= rex_escape($this->resize->value) ?>"
    <?= $this->readonly ? 'readonly' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->minlength ? 'minlength="'.rex_escape($this->minlength).'"' : '' ?>
    <?= $this->maxlength ? 'maxlength="'.rex_escape($this->maxlength).'"' : '' ?>
    <?= $this->autofocus ? 'autofocus' : '' ?>
    <?= $this->autocapitalize ? 'autocapitalize="'.$this->autocapitalize->value.'"' : '' ?>
    <?= $this->spellcheck ? 'spellcheck="true"' : 'spellcheck="false"' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->slotLabel ? $this->slotLabel->prepare('label')->get() : '' ?>
    <?= $this->slotNotice ? $this->slotNotice->prepare('help-text')->get() : '' ?>
</sl-textarea>
