<?php

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;
use Redaxo\Core\Fragment\Fragment;

/** @var Alert $this */
?>

<sl-alert <?= $this->attributes->with([
    'variant' => match ($this->type) {
        AlertType::Error => 'danger',
        AlertType::Info => 'primary',
        AlertType::Neutral => 'neutral',
        AlertType::Success => 'success',
        AlertType::Warning => 'warning',
    },
    'open' => $this->open,
    'closeable' => $this->closeable,
    'duration' => $this->duration,
])->toString() ?>>
    <?= Fragment::slot($this->icon, 'icon') ?>
    <?= Fragment::slot($this->body) ?>
</sl-alert>
