<?php

use Redaxo\Core\Core;
use Redaxo\Core\Form\Widget\MediaInput;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\HtmlAttributes;

use function Redaxo\Core\View\escape;

return static function (MediaInput $input): void {
    $id = escape($input->id, 'js');
    $params = escape(
        (null === $input->categoryId ? '' : '&rex_file_category=' . $input->categoryId)
        . ($input->types ? '&types=' . urlencode(implode(',', $input->types)) : ''),
        'js',
    );
    $allowed = Core::requireUser()->getComplexPerm('media')->hasMediaPerm();

    $button = static fn (string $onclick, string $title, string $icon): string => '<a ' . new HtmlAttributes([
        'href' => '#',
        'class' => ['btn', 'btn-popup'],
        'onclick' => ($allowed ? $onclick : '') . 'return false;',
        'title' => I18n::rawMsg($title),
        'disabled' => !$allowed,
    ])->toString() . '><i class="rex-icon ' . $icon . '"></i></a>' ?>
    <div <?= new HtmlAttributes(['class' => ['rex-js-widget', 'rex-js-widget-media', 'rex-js-widget-preview' => $input->preview, 'rex-js-widget-preview-media-manager' => $input->preview]]) ?>>
        <div class="input-group">
            <input class="form-control" type="text" name="<?= escape($input->name) ?>" value="<?= escape((string) $input->fileName) ?>" id="<?= escape($input->labelTarget()) ?>" readonly>
            <span class="input-group-btn">
                <?= $button("openREXMedia('$id', '$params');", 'var_media_open', 'rex-icon-open-mediapool') ?>
                <?= $button("addREXMedia('$id', '$params');", 'var_media_new', 'rex-icon-add-media') ?>
                <?= $button("deleteREXMedia('$id');", 'var_media_remove', 'rex-icon-delete-media') ?>
                <?= $button("viewREXMedia('$id', '$params');", 'var_media_view', 'rex-icon-view-media') ?>
            </span>
        </div>
        <div class="rex-js-media-preview"></div>
    </div>
<?php };
