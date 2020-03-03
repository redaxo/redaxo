<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($ctype) && is_int($ctype));
assert(isset($article) && $article instanceof rex_sql);
assert(isset($category_id) && is_int($category_id));
assert(isset($article_id) && is_int($article_id));

$content = '
        <form id="rex-form-content-metamode" action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data" data-pjax-container="#rex-page-main">
            <input type="hidden" name="save" value="1" />
            <input type="hidden" name="ctype" value="' . $ctype . '" />
            ';

$onclickApiFields = static function ($hiddenFields) {
    return 'onclick="$(this.form).append(\''.rex_escape($hiddenFields).'\')"';
};

$isStartpage = 1 == $article->getValue('startarticle');
// --------------------------------------------------- ZUM STARTARTICLE MACHEN START
if (rex::getUser()->hasPerm('article2startarticle[]')) {
    $panel = '<fieldset>';

    $panelClass = 'default';
    $buttons = '';
    if (!$isStartpage && 0 == $article->getValue('parent_id')) {
        $panelClass = 'info';

        $formElements = [];
        $n = [];
        $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_nottostartarticle') . '</p>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');
    } elseif ($isStartpage) {
        $panelClass = 'info';

        $formElements = [];
        $n = [];
        $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_isstartarticle') . '</p>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');
    } else {
        $formElements = [];
        $n = [];
        $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_tostartarticle') . '</p>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];
        $n = [];
        $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="article2startarticle" value="1" data-confirm="' . rex_i18n::msg('content_tostartarticle') . '?" '.$onclickApiFields(rex_api_article2startarticle::getHiddenFields()).'>' . rex_i18n::msg('content_tostartarticle') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
    }

    $panel .= '</fieldset>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', $panelClass);
    $fragment->setVar('title', rex_i18n::msg('content_startarticle'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}

// --------------------------------------------------- ZUM STARTARTICLE MACHEN END

// --------------------------------------------------- IN KATEGORIE UMWANDELN START
if (!$isStartpage && rex::getUser()->hasPerm('article2category[]')) {
    $panel = '<fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_tocategory') . '</p>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= '</fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="article2category" value="1" data-confirm="' . rex_i18n::msg('content_tocategory') . '?" '.$onclickApiFields(rex_api_article2category::getHiddenFields()).'>' . rex_i18n::msg('content_tocategory') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('content_category'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}
// --------------------------------------------------- IN KATEGORIE UMWANDELN END

// --------------------------------------------------- IN ARTIKEL UMWANDELN START
if ($isStartpage && rex::getUser()->hasPerm('article2category[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('parent_id'))) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT pid FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=? LIMIT 1', [$article_id]);
    $emptyCategory = 0 == $sql->getRows();

    $panel = '<fieldset>';

    $panelClass = 'default';
    $buttons = '';
    if (!$emptyCategory) {
        $panelClass = 'info';

        $formElements = [];
        $n = [];
        $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_nottoarticle') . '</p>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');
    } else {
        $formElements = [];
        $n = [];
        $n['field'] = '<p class="form-control-static">' . rex_i18n::msg('content_toarticle') . '</p>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];
        $n = [];
        $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="category2article" value="1" data-confirm="' . rex_i18n::msg('content_toarticle') . '?" '.$onclickApiFields(rex_api_category2Article::getHiddenFields()).'>' . rex_i18n::msg('content_toarticle') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
    }

    $panel .= '</fieldset>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', $panelClass);
    $fragment->setVar('title', rex_i18n::msg('content_article'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}
// --------------------------------------------------- IN ARTIKEL UMWANDELN END

// --------------------------------------------------- INHALTE KOPIEREN START
$user = rex::getUser();
if ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->count() > 1) {
    $clang_perm = $user->getComplexPerm('clang')->getClangs();

    $lang_a = new rex_select();
    $lang_a->setId('clang_a');
    $lang_a->setName('clang_a');
    $lang_a->setSize('1');
    $lang_a->setAttribute('class', 'form-control selectpicker');
    foreach ($clang_perm as $key) {
        $val = rex_i18n::translate(rex_clang::get($key)->getName());
        $lang_a->addOption($val, $key);
    }

    $lang_b = new rex_select();
    $lang_b->setId('clang_b');
    $lang_b->setName('clang_b');
    $lang_b->setSize('1');
    $lang_b->setAttribute('class', 'form-control selectpicker');
    foreach ($clang_perm as $key) {
        $val = rex_i18n::translate(rex_clang::get($key)->getName());
        $lang_b->addOption($val, $key);
    }

    $lang_a->setSelected(rex_request('clang_a', 'int', null));
    $lang_b->setSelected(rex_request('clang_b', 'int', null));

    $panel = '<fieldset>';

    $grid = [];

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="clang_a">' . rex_i18n::msg('content_contentoflang') . '</label>';
    $n['field'] = $lang_a->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('group', true);
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="clang_b">' . rex_i18n::msg('content_to') . '</label>';
    $n['field'] = $lang_b->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('group', true);
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= '</fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="content_copy" value="1" data-confirm="' . rex_i18n::msg('content_submitcopycontent') . '?" '.$onclickApiFields(rex_api_content_copy::getHiddenFields()).'>' . rex_i18n::msg('content_submitcopycontent') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('content_submitcopycontent'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}
// --------------------------------------------------- INHALTE KOPIEREN ENDE

// --------------------------------------------------- ARTIKEL VERSCHIEBEN START
if (!$isStartpage && rex::getUser()->hasPerm('moveArticle[]')) {
    // Wenn Artikel kein Startartikel dann Selectliste darstellen, sonst...
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setId('category_id_new');
    $move_a->setName('category_id_new');
    $move_a->setSize('1');
    $move_a->setAttribute('class', 'form-control selectpicker');
    $move_a->setSelected($category_id);

    $panel = '<fieldset>';

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_article') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= '</fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="article_move" value="1" data-confirm="' . rex_i18n::msg('content_submitmovearticle') . '?" '.$onclickApiFields(rex_api_article_move::getHiddenFields()).'>' . rex_i18n::msg('content_submitmovearticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('content_submitmovearticle'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}
// ------------------------------------------------ ARTIKEL VERSCHIEBEN ENDE

// -------------------------------------------------- ARTIKEL KOPIEREN START
if (rex::getUser()->hasPerm('copyArticle[]')) {
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setName('category_copy_id_new');
    $move_a->setId('category_copy_id_new');
    $move_a->setSize('1');
    $move_a->setAttribute('class', 'form-control selectpicker');
    $move_a->setSelected($category_id);

    $panel = '<fieldset>';

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="category_copy_id_new">' . rex_i18n::msg('copy_article') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel = $fragment->parse('core/form/form.php');

    $panel .= '</fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="article_copy" value="1" data-confirm="' . rex_i18n::msg('content_submitcopyarticle') . '?" '.$onclickApiFields(rex_api_article_copy::getHiddenFields()).'>' . rex_i18n::msg('content_submitcopyarticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('content_submitcopyarticle'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}
// --------------------------------------------------- ARTIKEL KOPIEREN ENDE

// --------------------------------------------------- KATEGORIE/STARTARTIKEL VERSCHIEBEN START
if ($isStartpage && rex::getUser()->hasPerm('moveCategory[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('parent_id'))) {
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setId('category_id_new');
    $move_a->setName('category_id_new');
    $move_a->setSize('1');
    $move_a->setAttribute('class', 'form-control selectpicker');
    $move_a->setSelected($article_id);

    $panel = '<fieldset>';

    $formElements = [];
    $n = [];
    $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_category') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= '</fieldset>';

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-send rex-form-aligned" type="submit" name="category_move" value="1" data-confirm="' . rex_i18n::msg('content_submitmovecategory') . '?" '.$onclickApiFields(rex_api_category_move::getHiddenFields()).'>' . rex_i18n::msg('content_submitmovecategory') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('content_submitmovecategory'), false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content .= $fragment->parse('core/page/section.php');
}

// ------------------------------------------------ KATEGROIE/STARTARTIKEL VERSCHIEBEN ENDE

$content .= '</form>';

return $content;
