<?php

use Redaxo\Core\Fragment\Component\Badge;
use Redaxo\Core\Fragment\Fragment;

/** @var Badge $this */
?>

<sl-badge <?= $this->attributes->with([
    'variant' => $this->variant,
    'pill' => $this->pill,
    'pulse' => $this->pulse,
]) ?>>
    <?= Fragment::slot($this->body) ?>
</sl-badge>
