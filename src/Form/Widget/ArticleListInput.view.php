<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Core;
use Redaxo\Core\Form\Widget\ArticleListInput;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\HtmlAttributes;

use function Redaxo\Core\View\escape;

return static function (ArticleListInput $input): void {
    $id = escape($input->id, 'js');
    $params = '&language=' . Language::getCurrentId() . '&category_id=' . ($input->categoryId ?? Category::getCurrent()->id ?? 0);
    $allowed = Core::requireUser()->getComplexPerm('structure')->hasStructurePerm();

    $button = static fn (string $onclick, string $title, string $icon, bool $enabled = true): string => '<a ' . new HtmlAttributes([
        'href' => '#',
        'class' => ['btn', 'btn-popup'],
        'onclick' => ($enabled ? $onclick : '') . 'return false;',
        'title' => I18n::rawMsg($title),
        'disabled' => !$enabled,
    ])->toString() . '><i class="rex-icon ' . $icon . '"></i></a>' ?>
    <div class="input-group">
        <select class="form-control" id="<?= escape($input->labelTarget()) ?>" size="10">
            <?php foreach ($input->articleIds as $articleId): ?>
                <?php if ($article = Article::get($articleId)): ?>
                    <option value="<?= $article->id ?>"><?= escape($article->name . ' [' . $article->id . ']') ?></option>
                <?php endif ?>
            <?php endforeach ?>
        </select>
        <input type="hidden" name="<?= escape($input->name) ?>" id="REX_LINKLIST_<?= escape($input->id) ?>" value="<?= escape(implode(',', $input->articleIds)) ?>">
        <span class="input-group-addon">
            <div class="btn-group-vertical">
                <?= $button("moveREXLinklist('$id', 'top');", 'form_articlelist_move_top', 'rex-icon-top') ?>
                <?= $button("moveREXLinklist('$id', 'up');", 'form_articlelist_move_up', 'rex-icon-up') ?>
                <?= $button("moveREXLinklist('$id', 'down');", 'form_articlelist_move_down', 'rex-icon-down') ?>
                <?= $button("moveREXLinklist('$id', 'bottom');", 'form_articlelist_move_bottom', 'rex-icon-bottom') ?>
            </div>
            <div class="btn-group-vertical">
                <?= $button("openREXLinklist('$id', '" . escape($params, 'js') . "');", 'form_article_open', 'rex-icon-open-linkmap', $allowed) ?>
                <?= $button("deleteREXLinklist('$id');", 'form_article_delete', 'rex-icon-delete-link', $allowed) ?>
            </div>
        </span>
    </div>
<?php };
