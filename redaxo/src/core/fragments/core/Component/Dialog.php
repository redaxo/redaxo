<?php

use Redaxo\Core\Fragment\Component\Dialog;
use Redaxo\Core\Fragment\Fragment;

/** @var Dialog $this */
?>
<sl-dialog <?= $this->attributes->with([
    'label' => is_string($this->label) ? $this->label : null,
]) ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
    <?= Fragment::slot($this->body) ?>
</sl-dialog>
<?= Fragment::slot($this->button) ?>
