<?php

use Redaxo\Core\Backend\Accesskey;
use Redaxo\Core\Form\ButtonStyle;
use Redaxo\Core\Form\Widget\Button;

use function Redaxo\Core\View\escape;

return static function (Button $button): void {
    $accesskey = null === $button->accesskey ? null : Accesskey::get($button->accesskey) ?>
    <button <?= $button->attributes->with([
        'class' => ['btn', match ($button->style) {
            // standard.js clicks `.btn-apply` on cmd+enter to save without leaving the form
            ButtonStyle::Primary => 'apply' === $button->value ? 'btn-apply' : 'btn-save',
            ButtonStyle::Secondary => 'btn-default',
            ButtonStyle::Danger => 'btn-delete',
        }],
        'type' => 'submit',
        'name' => $button->name,
        'value' => $button->value,
        'accesskey' => $accesskey,
        'title' => null === $accesskey ? $button->label : $button->label . ' [' . $accesskey . ']',
    ]) ?>><?= escape($button->label) ?></button>
<?php };
