<?php

use Redaxo\Core\Fragment\Component\TabGroup;

/** @var TabGroup $this */
?>

<sl-tab-group <?= $this->attributes->with([
    'placement' => $this->placement,
]) ?>>
    <?php foreach ($this->elements as $element): ?>
        <?= $element ?>
    <?php endforeach ?>
</sl-tab-group>
