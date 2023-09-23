<?php

use Redaxo\Core\Fragment\Component\Tab;
use Redaxo\Core\Fragment\Fragment;

/** @var Tab $this */
?>

<sl-tab <?= $this->attributes->with([
    'panel' => $this->name,
    'active' => $this->active,
    'closable' => $this->closable,
    'disabled' => $this->disabled,
])->toString() ?>>
    <?= Fragment::slot($this->label) ?>
</sl-tab>
<sl-tab-panel <?= $this->attributes->with([
    'name' => $this->name,
    'active' => $this->active,
])->toString() ?>>
    <?= Fragment::slot($this->panel) ?>
</sl-tab-panel>
