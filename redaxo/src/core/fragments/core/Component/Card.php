<?php

use Redaxo\Core\Fragment\Component\Card;
use Redaxo\Core\Fragment\Fragment;

/** @var Card $this */
?>

<sl-card <?= $this->attributes ?>>
    <?= Fragment::slot($this->image, 'image') ?>
    <?= Fragment::slot($this->header, 'header') ?>
    <?= Fragment::slot($this->footer, 'footer') ?>
    <?= Fragment::slot($this->body) ?>
</sl-card>
