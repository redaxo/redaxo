<?php

use Redaxo\Core\Fragment\Component\Button;

/** @var Button $this */
?>

<sl-button
    <?= $this->variant ? 'variant="'.$this->variant->value.'"' : '' ?>
    <?= $this->size ? 'size="'.$this->size->value.'"' : '' ?>
    <?= $this->caret ? 'caret' : '' ?>
    <?= $this->disabled ? 'disabled' : '' ?>
    <?= $this->outline ? 'outline' : '' ?>
    <?= $this->pill ? 'pill' : '' ?>
    <?= $this->circle ? 'circle' : '' ?>
    <?= $this->type ? 'type="'.$this->type->value.'"' : '' ?>
    <?= $this->name ? 'name="'.rex_escape($this->name).'"' : '' ?>
    <?= $this->value ? 'value="'.rex_escape($this->value).'"' : '' ?>
    <?= $this->href ? 'href="'.rex_escape($this->href).'"' : '' ?>
    <?= $this->target ? 'target="'.$this->target->value.'"' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?= $this->slotPrefix ? $this->slotPrefix->prepare('prefix')->get() : '' ?>
    <?= $this->slotSuffix ? $this->slotSuffix->prepare('suffix')->get() : '' ?>
    <?= $this->slotDefault->get() ?>
</sl-button>
