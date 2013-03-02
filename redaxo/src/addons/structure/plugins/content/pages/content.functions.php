<?php

$content .= '
                <div class="rex-form" id="rex-form-content-metamode">
                    <form action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data" id="REX_FORM">
                                <input type="hidden" name="save" value="1" />
                                <input type="hidden" name="ctype" value="' . $ctype . '" />
                                <input type="hidden" name="rex-api-call" id="apiField">
                                ';


$isStartpage = $article->getValue('startarticle') == 1;
$out = '';

// --------------------------------------------------- ZUM STARTARTICLE MACHEN START
if (rex::getUser()->hasPerm('article2startarticle[]')) {
    $out .= '
                        <fieldset>
                            <h2>' . rex_i18n::msg('content_startarticle') . '</h2>';

    $formElements = array();

    $n = array();
    if (!$isStartpage && $article->getValue('re_id') == 0)
        $n['field'] = '<span class="rex-form-read">' . rex_i18n::msg('content_nottostartarticle') . '</span>';
    elseif ($isStartpage)
        $n['field'] = '<span class="rex-form-read">' . rex_i18n::msg('content_isstartarticle') . '</span>';
    else
        $n['field'] = '<button class="rex-button" type="submit" name="article2startarticle" data-confirm="' . rex_i18n::msg('content_tostartarticle') . '?" onclick="jQuery(\'#apiField\').val(\'article2startarticle\');">' . rex_i18n::msg('content_tostartarticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');



    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);
}



// --------------------------------------------------- ZUM STARTARTICLE MACHEN END

// --------------------------------------------------- IN KATEGORIE UMWANDELN START
$out = '';
if (!$isStartpage && rex::getUser()->hasPerm('article2category[]')) {
    $out .= '
                        <fieldset>
                            <h2>' . rex_i18n::msg('content_category') . '</h2>';


    $formElements = array();

    $n = array();
    $n['field'] = '<button class="rex-button" type="submit" name="article2category" data-confirm="' . rex_i18n::msg('content_tocategory') . '?" onclick="jQuery(\'#apiField\').val(\'article2category\');">' . rex_i18n::msg('content_tocategory') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');


    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);
}
// --------------------------------------------------- IN KATEGORIE UMWANDELN END

// --------------------------------------------------- IN ARTIKEL UMWANDELN START
$out = '';
if ($isStartpage && rex::getUser()->hasPerm('category2article[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id'))) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT pid FROM ' . rex::getTablePrefix() . 'article WHERE re_id=' . $article_id . ' LIMIT 1');
    $emptyCategory = $sql->getRows() == 0;

    $out .= '
                        <fieldset>
                            <h2>' . rex_i18n::msg('content_article') . '</h2>';


    $formElements = array();

    $n = array();
    if (!$emptyCategory)
        $n['field'] = '<span class="rex-form-read">' . rex_i18n::msg('content_nottoarticle') . '</span>';
    else
        $n['field'] = '<button class="rex-button" type="submit" name="category2article" data-confirm="' . rex_i18n::msg('content_toarticle') . '?" onclick="jQuery(\'#apiField\').val(\'category2article\');">' . rex_i18n::msg('content_toarticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');

    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);
}
// --------------------------------------------------- IN ARTIKEL UMWANDELN END

// --------------------------------------------------- INHALTE KOPIEREN START
$out = '';
$user = rex::getUser();
if ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->count() > 1) {
    $clang_perm = $user->getComplexPerm('clang')->getClangs();

    $lang_a = new rex_select;
    $lang_a->setId('clang_a');
    $lang_a->setName('clang_a');
    $lang_a->setSize('1');
    foreach ($clang_perm as $key) {
        $val = rex_i18n::translate(rex_clang::get($key)->getName());
        $lang_a->addOption($val, $key);
    }

    $lang_b = new rex_select;
    $lang_b->setId('clang_b');
    $lang_b->setName('clang_b');
    $lang_b->setSize('1');
    foreach ($clang_perm as $key) {
        $val = rex_i18n::translate(rex_clang::get($key)->getName());
        $lang_b->addOption($val, $key);
    }

    $lang_a->setSelected(rex_request('clang_a', 'int', null));
    $lang_b->setSelected(rex_request('clang_b', 'int', null));

    $out .= '
                            <fieldset>
                                <h2>' . rex_i18n::msg('content_submitcopycontent') . '</h2>';

    $formElements = array();

    $n = array();
    $n['label'] = '<label for="clang_a">' . rex_i18n::msg('content_contentoflang') . '</label>';
    $n['field'] = $lang_a->get();
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="clang_b">' . rex_i18n::msg('content_to') . '</label>';
    $n['field'] = $lang_b->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('group', true);
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');


    $formElements = array();

    $n = array();
    $n['field'] = '<button class="rex-button" type="submit" name="copycontent" data-confirm="' . rex_i18n::msg('content_submitcopycontent') . '?">' . rex_i18n::msg('content_submitcopycontent') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');


    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);

}
// --------------------------------------------------- INHALTE KOPIEREN ENDE

// --------------------------------------------------- ARTIKEL VERSCHIEBEN START
$out = '';
if (!$isStartpage && rex::getUser()->hasPerm('moveArticle[]')) {

    // Wenn Artikel kein Startartikel dann Selectliste darstellen, sonst...
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setId('category_id_new');
    $move_a->setName('category_id_new');
    $move_a->setSize('1');
    $move_a->setSelected($category_id);

    $out .= '
                            <fieldset>
                                <h2>' . rex_i18n::msg('content_submitmovearticle') . '</h2>';


    $formElements = array();

    $n = array();
    $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_article') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $n = array();
    $n['field'] = '<button class="rex-button" type="submit" name="movearticle" data-confirm="' . rex_i18n::msg('content_submitmovearticle') . '?">' . rex_i18n::msg('content_submitmovearticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');

    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);

}
// ------------------------------------------------ ARTIKEL VERSCHIEBEN ENDE

// -------------------------------------------------- ARTIKEL KOPIEREN START
$out = '';
if (rex::getUser()->hasPerm('copyArticle[]')) {
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setName('category_copy_id_new');
    $move_a->setId('category_copy_id_new');
    $move_a->setSize('1');
    $move_a->setSelected($category_id);

    $out .= '
                            <fieldset>
                                <h2>' . rex_i18n::msg('content_submitcopyarticle') . '</h2>';


    $formElements = array();

    $n = array();
    $n['label'] = '<label for="category_copy_id_new">' . rex_i18n::msg('copy_article') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $n = array();
    $n['field'] = '<button class="rex-button" type="submit" name="copyarticle" data-confirm="' . rex_i18n::msg('content_submitcopyarticle') . '?">' . rex_i18n::msg('content_submitcopyarticle') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');

    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);

}
// --------------------------------------------------- ARTIKEL KOPIEREN ENDE

// --------------------------------------------------- KATEGORIE/STARTARTIKEL VERSCHIEBEN START
$out = '';
if ($isStartpage && rex::getUser()->hasPerm('moveCategory[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id'))) {
    $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
    $move_a->setId('category_id_new');
    $move_a->setName('category_id_new');
    $move_a->setSize('1');
    $move_a->setSelected($article_id);

    $out .= '
                            <fieldset>
                                <h2>' . rex_i18n::msg('content_submitmovecategory') . '</h2>';


    $formElements = array();

    $n = array();
    $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_category') . '</label>';
    $n['field'] = $move_a->get();
    $formElements[] = $n;

    $n = array();
    $n['field'] = '<button class="rex-button" type="submit" name="movecategory" data-confirm="' . rex_i18n::msg('content_submitmovecategory') . '?">' . rex_i18n::msg('content_submitmovecategory') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $out .= $fragment->parse('core/form/form.tpl');

    $out .= '</fieldset>';

    $content .= rex_view::contentBlock($out);

}

// ------------------------------------------------ KATEGROIE/STARTARTIKEL VERSCHIEBEN ENDE

$content .= '
                                    </form>
                                </div>';

echo rex_view::contentBlock($content, '', true, false);
