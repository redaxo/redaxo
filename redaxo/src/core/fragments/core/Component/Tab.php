<?php

use Redaxo\Core\Fragment\Component\Tab;
use Redaxo\Core\Fragment\Fragment;

/** @var Tab $this */
?>

<sl-tab <?= $this->tabAttributes->with([
    'panel' => $this->name,
    'active' => $this->active,
    'closable' => $this->closable,
    'disabled' => $this->disabled,
]) ?>>
    <?= Fragment::slot($this->label) ?>
</sl-tab>
<sl-tab-panel <?= $this->panelAttributes->with([
    'name' => $this->name,
    'active' => $this->active,
]) ?>>
    <?= Fragment::slot($this->panel) ?>
</sl-tab-panel>
