<?php

use Redaxo\Core\Form\Widget\CheckboxInput;

use function Redaxo\Core\View\escape;

return static function (CheckboxInput $checkbox): void { ?>
    <div class="checkbox">
        <label>
            <input <?= $checkbox->attributes->with([
                'type' => 'checkbox',
                'name' => $checkbox->name,
                'id' => $checkbox->id,
                'value' => $checkbox->value,
                'checked' => $checkbox->checked,
                'required' => $checkbox->required,
            ]) ?>>
            <?= escape((string) $checkbox->label) ?>
        </label>
    </div>
<?php };
