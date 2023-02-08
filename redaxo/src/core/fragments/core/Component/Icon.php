<?php

use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;

/** @var Icon $this */

$name = match ($this->name) {
    IconLibrary::Add => 'plus-lg',
    IconLibrary::AlertError => 'exclamation-octagon',
    IconLibrary::AlertInfo => 'info-circle',
    IconLibrary::AlertNeutral => 'x-diamond',
    IconLibrary::AlertSuccess => 'check-circle',
    IconLibrary::AlertWarning => 'exclamation-triangle',
    IconLibrary::Debug => 'heart-pulse',
    IconLibrary::Save => 'database-up',
};
?>
<sl-icon name="<?= $name ?>"
    <?= $this->label ? 'label="'.rex_escape($this->label).'"' : '' ?>
    <?= $this->src ? 'src="'.rex_escape($this->src).'"' : '' ?>
>
</sl-icon>
