<?php

use Redaxo\Core\Fragment\Component\Details;
use Redaxo\Core\Fragment\Fragment;

/** @var Details $this */
?>
<sl-details <?= $this->attributes->with([
    'summary' => is_string($this->summary) ? $this->summary : null,
    'open' => $this->open,
    'disabled' => $this->disabled,
])->toString() ?>>
    <?= $this->summary instanceof Fragment ? Fragment::slot($this->summary, 'summary') : '' ?>
    <?= Fragment::slot($this->expandIcon, 'expand-icon') ?>
    <?= Fragment::slot($this->collapseIcon, 'collapse-icon') ?>
    <?= Fragment::slot($this->body) ?>
</sl-details>
