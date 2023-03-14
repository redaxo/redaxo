<?php

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Fragment;

/** @var Button $this */
?>

<sl-button <?= $this->attributes->with([
    'variant' => $this->variant,
    'size' => $this->size,
    'caret' => $this->caret,
    'disabled' => $this->disabled,
    'outline' => $this->outline,
    'pill' => $this->pill,
    'circle' => $this->circle,
    'type' => $this->type,
    'name' => $this->name,
    'value' => $this->value,
    'href' => $this->href,
    'target' => $this->target,
])->toString() ?>>
    <?= Fragment::slot($this->prefix, 'prefix') ?>
    <?= Fragment::slot($this->suffix, 'suffix') ?>
    <?= Fragment::slot($this->label) ?>
</sl-button>
