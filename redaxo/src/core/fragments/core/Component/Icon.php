<?php

use Redaxo\Core\Fragment\Attributes;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;

/** @var Icon $this */
?>
<sl-icon <?= (new Attributes([
    'name' => match ($this->name) {
        IconLibrary::Add => 'plus-lg',
        IconLibrary::AlertError => 'exclamation-octagon',
        IconLibrary::AlertInfo,
        IconLibrary::PhpInfo => 'info-circle',
        IconLibrary::AlertNeutral => 'x-diamond',
        IconLibrary::AlertSuccess => 'check-circle',
        IconLibrary::AlertWarning => 'exclamation-triangle',
        IconLibrary::Debug => 'heart-pulse',
        IconLibrary::VersionUnstable => 'egg-fried',
        IconLibrary::Save => 'database-up',
    },
    'label' => $this->label,
    'src' => $this->src,
]))->toString() ?>>
</sl-icon>
