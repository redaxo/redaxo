<?php

use Redaxo\Core\Core;
use Redaxo\Core\Form\Widget\MediaListInput;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\HtmlAttributes;

use function Redaxo\Core\View\escape;

return static function (MediaListInput $input): void {
    $id = escape($input->id, 'js');
    $params = escape(
        (null === $input->categoryId ? '' : '&rex_file_category=' . $input->categoryId)
        . ($input->types ? '&types=' . urlencode(implode(',', $input->types)) : ''),
        'js',
    );
    $allowed = Core::requireUser()->getComplexPerm('media')->hasMediaPerm();

    $button = static fn (string $onclick, string $title, string $icon, bool $enabled = true): string => '<a ' . new HtmlAttributes([
        'href' => '#',
        'class' => ['btn', 'btn-popup'],
        'onclick' => ($enabled ? $onclick : '') . 'return false;',
        'title' => I18n::rawMsg($title),
        'disabled' => !$enabled,
    ])->toString() . '><i class="rex-icon ' . $icon . '"></i></a>' ?>
    <div <?= new HtmlAttributes(['class' => ['rex-js-widget', 'rex-js-widget-medialist', 'rex-js-widget-preview' => $input->preview, 'rex-js-widget-preview-media-manager' => $input->preview]]) ?>>
        <div class="input-group">
            <select class="form-control" id="<?= escape($input->labelTarget()) ?>" size="10">
                <?php foreach ($input->fileNames as $fileName): ?>
                    <option value="<?= escape($fileName) ?>"><?= escape($fileName) ?></option>
                <?php endforeach ?>
            </select>
            <input type="hidden" name="<?= escape($input->name) ?>" id="REX_MEDIALIST_<?= escape($input->id) ?>" value="<?= escape(implode(',', $input->fileNames)) ?>">
            <span class="input-group-addon">
                <div class="btn-group-vertical">
                    <?= $button("moveREXMedialist('$id', 'top');", 'var_medialist_move_top', 'rex-icon-top') ?>
                    <?= $button("moveREXMedialist('$id', 'up');", 'var_medialist_move_up', 'rex-icon-up') ?>
                    <?= $button("moveREXMedialist('$id', 'down');", 'var_medialist_move_down', 'rex-icon-down') ?>
                    <?= $button("moveREXMedialist('$id', 'bottom');", 'var_medialist_move_bottom', 'rex-icon-bottom') ?>
                </div>
                <div class="btn-group-vertical">
                    <?= $button("openREXMedialist('$id', '$params');", 'var_media_open', 'rex-icon-open-mediapool', $allowed) ?>
                    <?= $button("addREXMedialist('$id', '$params');", 'var_media_new', 'rex-icon-add-media', $allowed) ?>
                    <?= $button("deleteREXMedialist('$id');", 'var_media_remove', 'rex-icon-delete-media', $allowed) ?>
                    <?= $button("viewREXMedialist('$id', '$params');", 'var_media_view', 'rex-icon-view-media', $allowed) ?>
                </div>
            </span>
        </div>
        <div class="rex-js-media-preview"></div>
    </div>
<?php };
