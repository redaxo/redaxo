<?php
/**
 * @var Button $this
 * @psalm-scope-this Button
 */

use Redaxo\Core\Fragment\Component\Button;

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
    <?= $this->name ? 'name="'.$this->name.'"' : '' ?>
    <?= $this->value ? 'value="'.$this->value.'"' : '' ?>
    <?= $this->href ? 'href="'.$this->href.'"' : '' ?>
    <?= $this->target ? 'target="'.$this->target->value.'"' : '' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->slotPrefix ? $this->slotPrefix->prepare('prefix')->get() : '' ?>
    <?= $this->slotSuffix ? $this->slotSuffix->prepare('suffix')->get() : '' ?>
    <?= $this->slotDefault->get() ?>
</sl-button>
