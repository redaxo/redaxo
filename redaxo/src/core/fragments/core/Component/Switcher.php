<?php

use Redaxo\Core\Fragment\Component\Switcher;
use Redaxo\Core\Fragment\Fragment;

/** @var Switcher $this */
?>

<sl-switch <?= $this->attributes->with([
    'name' => $this->name,
    'value' => $this->value,
    'size' => $this->size,
    'disabled' => $this->disabled,
    'checked' => $this->checked,
    'required' => $this->required,
])->toString() ?>>
    <?= Fragment::slot($this->label) ?>
</sl-switch>
