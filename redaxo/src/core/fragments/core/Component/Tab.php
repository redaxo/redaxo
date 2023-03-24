<?php

use Redaxo\Core\Fragment\Component\Tab;
use Redaxo\Core\Fragment\Fragment;

/** @var Tab $this */

$random = random_int(100, 999).random_int(100, 999);
?>

<sl-tab <?= $this->attributes->with([
    'slot' => 'nav',
    'panel' => 'tab-'.$random,
    'disabled' => $this->disabled,
    'active' => $this->active,
])->toString() ?>>
    <?= Fragment::slot($this->label) ?>
</sl-tab>
<sl-tab-panel name="tab-<?= $random ?>">
    <?= Fragment::slot($this->body) ?>
</sl-tab-panel>
