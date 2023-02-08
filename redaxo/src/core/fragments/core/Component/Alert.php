<?php

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;

/** @var Alert $this */

$variant = match ($this->type) {
    AlertType::Error => 'danger',
    AlertType::Info => 'primary',
    AlertType::Success => 'success',
    AlertType::Warning => 'warning',
    default => 'neutral',
};
?>

<sl-alert
    <?= 'variant="'.$variant.'"' ?>
    <?= $this->open ? 'open' : '' ?>
    <?= $this->closeable ? 'closeable' : '' ?>
    <?= $this->duration ? 'duration="'.$this->duration.'"' : '' ?>
    <?= rex_string::buildAttributes($this->attributes) ?>
>
    <?= $this->slotIcon ? $this->slotIcon->prepare('icon')->get() : '' ?>
    <?= $this->slotDefault->get() ?>
</sl-alert>
