<?php
/**
 * @var Input $this
 * @psalm-scope-this Input
 */

use Redaxo\Core\Fragment\Component\Input;
?>

<sl-input
    type="<?= $this->type->value ?>"
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->notice ? 'help-text="'.rex_escape($this->notice).'"' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->placeholder ? 'placeholder="'.rex_escape($this->placeholder).'"' : '' ?>
    <?= $this->readonly ? 'readonly' : '' ?>
    <?= $this->required ? 'required' : '' ?>
    <?= $this->pattern ? 'pattern="'.rex_escape($this->pattern).'"' : '' ?>
    <?= $this->minlength ? 'minlength="'.rex_escape($this->minlength).'"' : '' ?>
    <?= $this->maxlength ? 'maxlength="'.rex_escape($this->maxlength).'"' : '' ?>
    <?= $this->min ? 'min="'.rex_escape($this->min).'"' : '' ?>
    <?= $this->max ? 'max="'.rex_escape($this->max).'"' : '' ?>
    <?= $this->step ? 'step="'.rex_escape($this->step).'"' : '' ?>
    <?= $this->autocapitalize ? 'autocapitalize="'.$this->autocapitalize->value.'"' : '' ?>
    <?= $this->spellcheck ? 'spellcheck="true"' : 'spellcheck="false"' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->slotLabel ? $this->slotLabel->prepare('label')->get() : '' ?>
    <?= $this->slotNotice ? $this->slotNotice->prepare('help-text')->get() : '' ?>
    <?= $this->slotPrefix ? $this->slotPrefix->prepare('prefix')->get() : '' ?>
    <?= $this->slotSuffix ? $this->slotSuffix->prepare('suffix')->get() : '' ?>
</sl-input>
