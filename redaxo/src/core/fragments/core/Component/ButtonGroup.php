<?php

use Redaxo\Core\Fragment\Component\ButtonGroup;

/** @var ButtonGroup $this */
?>

<sl-button-group <?= $this->attributes->with([
    'label' => $this->label,
])->toString() ?>>
    <?php foreach ($this->elements as $element): ?>
        <?= $element->render() ?>
    <?php endforeach ?>
</sl-button-group>
