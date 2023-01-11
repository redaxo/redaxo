<?php
/**
 * @var Button $this
 * @psalm-scope-this Button
 */

use Redaxo\Core\Fragment\Component\Button;

if ($this->prefix && !str_contains($this->prefix, 'slot="prefix"')) {
    throw new rex_functional_exception('The prefix property requires the attribute `slot="prefix"`');
}
if ($this->suffix && !str_contains($this->suffix, 'slot="suffix"')) {
    throw new rex_functional_exception('The prefix property requires the attribute `slot="suffix"`');
}
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
    <?= $this->value ? 'name="'.$this->value.'"' : '' ?>
    <?= $this->href ? 'href="'.$this->href.'"' : '' ?>
    <?= $this->target ? 'target="'.$this->target->value.'"' : '' ?>
    <?= $this->slot ? 'slot="'.$this->slot.'"' : '' ?>
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->prefix ?: '' ?>
    <?= $this->suffix ?: '' ?>
    <?= $this->label ?>
</sl-button>
