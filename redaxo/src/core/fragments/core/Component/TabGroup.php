<?php

use Redaxo\Core\Fragment\Component\TabGroup;
use Redaxo\Core\Fragment\Fragment;

/** @var TabGroup $this */
?>

<sl-tab-group <?= $this->attributes->with([
    'placement' => $this->placement,
])->toString() ?>>
    <?php foreach ($this->tabs as $tab): ?>
        <?= Fragment::slot($tab) ?>
    <?php endforeach ?>
</sl-tab-group>
