<?php

use Redaxo\Core\Fragment\Component\Card;

/** @var Card $this */
?>

<sl-card
    <?= $this->attributes ? rex_string::buildAttributes($this->attributes) : '' ?>
>
    <?= $this->slotImage ? $this->slotImage->prepare('image')->get() : '' ?>
    <?= $this->slotHeader ? $this->slotHeader->prepare('header')->get() : '' ?>
    <?= $this->slotFooter ? $this->slotFooter->prepare('footer')->get() : '' ?>
    <?= $this->slotDefault->get() ?>
</sl-card>
