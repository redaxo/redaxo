<?php

use Redaxo\Core\Form\Widget\Input;

return static function (Input $input): void { ?>
    <input <?= $input->attributes->with([
        'class' => ['form-control'],
        'type' => $input->type,
        'name' => $input->name,
        'id' => $input->id,
        'value' => $input->value,
        'maxlength' => $input->maxLength,
        'required' => $input->required,
    ]) ?>>
<?php };
