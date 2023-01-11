<?php
/**
 * @var Icon $this
 * @psalm-scope-this Icon
 */

use Redaxo\Core\Fragment\Component\Icon;

?>

<sl-icon name="<?= $this->name->get() ?>"
    <?= $this->label ? 'label="'.$this->label.'"' : '' ?>
    <?= $this->src ? 'src="'.$this->src.'"' : '' ?>
    <?= $this->slot ? 'slot="'.$this->slot.'"' : '' ?>
>
</sl-icon>
