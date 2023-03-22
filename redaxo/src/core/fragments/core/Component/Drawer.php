<?php

use Redaxo\Core\Fragment\Component\Drawer;
use Redaxo\Core\Fragment\Fragment;

/** @var Drawer $this */
?>

<sl-drawer <?= $this->attributes->with([
    'open' => $this->open,
    'placement' => $this->placement,
    'contained' => $this->contained,
])->toString() ?>>
    <?= Fragment::slot($this->label, 'label') ?>
    <?= Fragment::slot($this->header, 'header-actions') ?>
    <?= Fragment::slot($this->footer, 'footer') ?>
    <?= Fragment::slot($this->body) ?>
</sl-drawer>
