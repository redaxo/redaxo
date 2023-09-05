<?php

use Redaxo\Core\Fragment\Component\CopyButton;
use Redaxo\Core\Fragment\Fragment;

/** @var CopyButton $this */
?>

<sl-copy-button <?= $this->attributes->with([
    'value' => $this->value,
    'from' => $this->from,
    'copy-label' => $this->copyLabel,
    'success-label' => $this->successLabel,
    'error-label' => $this->errorLabel,
    'disabled' => $this->disabled,
    'tooltip-placement' => $this->tooltipPlacement,
])->toString() ?>>
    <?= Fragment::slot($this->copyIcon, 'copy-icon') ?>
    <?= Fragment::slot($this->successIcon, 'success-icon') ?>
    <?= Fragment::slot($this->errorIcon, 'error-icon') ?>
</sl-copy-button>
