<?php

use Redaxo\Core\Fragment\Component\ButtonGroup;
use Redaxo\Core\Fragment\Fragment;

/** @var ButtonGroup $this */
?>

<sl-button-group <?= $this->attributes->with([
    'label' => $this->label,
])->toString() ?>>
    <?= Fragment::slot($this->body) ?>
</sl-button-group>
