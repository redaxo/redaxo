<?php

$content = '
                <div class="rex-form" id="rex-form-content-metamode">
                    <form action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data" id="REX_FORM">
                        <fieldset>
                            <h2>' . rex_i18n::msg('general') . '</h2>

                                <input type="hidden" name="save" value="1" />
                                <input type="hidden" name="ctype" value="' . $ctype . '" />
                                ';

$metainfoHandler = new rex_metainfo_article_handler();
$form = $metainfoHandler->getForm([
    'id' => $article_id,
    'clang' => $clang,
    'article' => $article
]);

$n = [];
$n['label'] = '<label for="rex-id-meta-article-name">' . rex_i18n::msg('header_article_name') . '</label>';
$n['field'] = '<input type="text" id="rex-id-meta-article-name" name="meta_article_name" value="' . htmlspecialchars(rex_article::get($article_id, $clang)->getValue('name')) . '" />';
$formElements = [$n];

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= $form . '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="rex-button btn btn-primary" type="submit" name="savemeta"' . rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save') . ' value="1">' . rex_i18n::msg('update_metadata') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.php');

$content .= '
                                    </form>
                                </div>';

echo rex_view::content('block', $content);
