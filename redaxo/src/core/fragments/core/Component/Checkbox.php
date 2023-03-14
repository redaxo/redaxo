<?php

use Redaxo\Core\Fragment\Component\Checkbox;
use Redaxo\Core\Fragment\Fragment;

/** @var Checkbox $this */
?>
<sl-checkbox <?= $this->attributes->with([
    'name' => $this->name,
    'value' => $this->value,
    'label' => is_string($this->label) ? $this->label : null,
    'disabled' => $this->disabled,
    'checked' => $this->checked,
    'indeterminate' => $this->indeterminate,
    'required' => $this->required,
])->toString() ?>>
    <?= $this->label instanceof Fragment ? Fragment::slot($this->label, 'label') : '' ?>
</sl-checkbox>
