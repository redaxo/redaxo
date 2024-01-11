<?php

use Redaxo\Core\Fragment\Component\MenuItem;
use Redaxo\Core\Fragment\Fragment;

/** @var MenuItem $this */
?>

<sl-menu-item <?= $this->attributes->with([
    'type' => $this->type,
    'checked' => $this->checked,
    'value' => $this->value,
    'disabled' => $this->disabled,
])->toString() ?>>
    <?= Fragment::slot($this->submenu, 'submenu') ?>
    <?= Fragment::slot($this->prefix, 'prefix') ?>
    <?= Fragment::slot($this->suffix, 'suffix') ?>
    <?= Fragment::slot($this->label) ?>
</sl-menu-item>
