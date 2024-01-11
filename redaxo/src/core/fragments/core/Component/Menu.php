<?php

use Redaxo\Core\Fragment\Component\Menu;
use Redaxo\Core\Fragment\Fragment;

/** @var Menu $this */
?>

<sl-menu <?= $this->attributes->toString() ?>>
    <?php foreach ($this->elements as $element): ?>
        <?= $element->render() ?>
    <?php endforeach ?>
</sl-menu>
