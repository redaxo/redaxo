<?php
/**
 * @var Icon $this
 * @psalm-scope-this Icon
 */

use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;

$name = match ($this->name) {
    IconLibrary::Add => 'plus-lg',
    IconLibrary::Debug => 'heart-pulse',
    IconLibrary::Save => 'database-up',
};
?>
<sl-icon name="<?= $name ?>"
    <?= $this->label ? 'label="'.$this->label.'"' : '' ?>
    <?= $this->src ? 'src="'.$this->src.'"' : '' ?>
    <?= $this->slot ? 'slot="'.$this->slot.'"' : '' ?>
>
</sl-icon>
