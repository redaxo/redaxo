<?php

use Redaxo\Core\Fragment\Component\Dialog;
use Redaxo\Core\Fragment\Fragment;

/** @var Dialog $this */
?>
<sl-dialog <?= $this->attributes->with([
    'label' => $this->label,
])->toString() ?>>
    <?= Fragment::slot($this->body) ?>
</sl-dialog>
<?php if ($this->button): ?>
    <?= Fragment::slot($this->button) ?>
<?php endif ?>
