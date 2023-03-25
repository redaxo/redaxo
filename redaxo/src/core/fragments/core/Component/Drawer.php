<?php

use Redaxo\Core\Fragment\Component\Drawer;
use Redaxo\Core\Fragment\Fragment;

/** @var Drawer $this */
?>

<sl-drawer <?= $this->attributes->with([
    'label' => is_string($this->label) ? $this->label : null,
    'open' => $this->open,
    'placement' => $this->placement,
    'contained' => $this->contained,
])->toString() ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= Fragment::slot($this->headerActions, 'header-actions') ?>
    <?= Fragment::slot($this->footer, 'footer') ?>
    <?= Fragment::slot($this->body) ?>
</sl-drawer>
