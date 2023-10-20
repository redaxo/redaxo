<?php

use Redaxo\Core\Fragment\Component\Divider;

/** @var Divider $this */
?>

<sl-divider <?= $this->attributes->with([
    'vertical' => $this->vertical,
])->toString() ?>>
</sl-divider>
