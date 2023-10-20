<?php

use Redaxo\Core\Fragment\Component\Range;
use Redaxo\Core\Fragment\Fragment;

/** @var Range $this */
?>

<sl-range <?= $this->attributes->with([
    'name' => $this->name,
    'value' => $this->value,
    'label' => is_string($this->label) ? $this->label : null,
    'help-text' => is_string($this->notice) ? $this->notice : null,
    'disabled' => $this->disabled,
    'min' => $this->min,
    'max' => $this->max,
    'step' => $this->step,
    'tooltip' => $this->tooltipPlacement,
])->toString() ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= $this->notice instanceof Fragment ? Fragment::slot($this->notice, 'help-text') : '' ?>
</sl-range>
