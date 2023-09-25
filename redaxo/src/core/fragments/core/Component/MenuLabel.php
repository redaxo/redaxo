<?php

use Redaxo\Core\Fragment\Component\MenuLabel;
use Redaxo\Core\Fragment\Fragment;

/** @var MenuLabel $this */
?>

<sl-menu-label <?= $this->attributes->toString() ?>>
    <?= Fragment::slot($this->label) ?>
</sl-menu-label>
