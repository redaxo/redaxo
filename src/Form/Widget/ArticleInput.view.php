<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Core;
use Redaxo\Core\Form\Widget\ArticleInput;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\HtmlAttributes;

use function Redaxo\Core\View\escape;

return static function (ArticleInput $input): void {
    $article = null === $input->articleId ? null : Article::get($input->articleId);
    $valueId = $input->id . '-value';

    $params = '&opener_input_field_name=' . $input->id
        . '&language=' . Language::getCurrentId()
        . '&category_id=' . ($article->categoryId ?? $input->categoryId ?? Category::getCurrent()->id ?? 0);

    $allowed = !$input->disabled && Core::requireUser()->getComplexPerm('structure')->hasStructurePerm();
    $open = sprintf('openLinkMap("%s", "%s"); return false;', escape($valueId, 'js'), escape($params, 'js'));
    $delete = sprintf(
        'document.getElementById("%s").value = ""; document.getElementById("%s").value = ""; return false;',
        escape($valueId, 'js'),
        escape($input->id, 'js'),
    ) ?>
    <div class="input-group">
        <input <?= new HtmlAttributes([
            'class' => ['form-control'],
            'type' => 'text',
            'id' => $input->id,
            'value' => null === $article ? '' : $article->name . ' [' . $article->id . ']',
            'readonly' => true,
            'disabled' => $input->disabled,
        ]) ?>>
        <input type="hidden" name="<?= escape($input->name) ?>" id="<?= escape($valueId) ?>" value="<?= escape((string) $input->articleId) ?>">
        <span class="input-group-btn">
            <a href="#" <?= new HtmlAttributes(['class' => ['btn', 'btn-popup', 'rex-disabled' => !$allowed], 'onclick' => $allowed ? $open : 'return false;', 'title' => I18n::rawMsg('form_article_open')]) ?>><i class="rex-icon rex-icon-open-linkmap"></i></a>
            <a href="#" <?= new HtmlAttributes(['class' => ['btn', 'btn-popup', 'rex-disabled' => !$allowed], 'onclick' => $allowed ? $delete : 'return false;', 'title' => I18n::rawMsg('form_article_delete')]) ?>><i class="rex-icon rex-icon-delete-link"></i></a>
        </span>
    </div>
<?php };
