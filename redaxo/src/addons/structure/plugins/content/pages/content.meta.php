<?php

$content .= '
                <div class="rex-form" id="rex-form-content-metamode">
                    <form action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data" id="REX_FORM">
                        <fieldset>
                            <h2>' . rex_i18n::msg('general') . '</h2>

                                <input type="hidden" name="save" value="1" />
                                <input type="hidden" name="ctype" value="' . $ctype . '" />
                                ';

$formElements = array();

$n = array();
$n['label'] = '<label for="rex-id-meta-article-name">' . rex_i18n::msg('name_description') . '</label>';
$n['field'] = '<input type="text" id="rex-id-meta-article-name" name="meta_article_name" value="' . htmlspecialchars($article->getValue('name')) . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.tpl');


// ----- EXTENSION POINT
$content .= rex_extension::registerPoint('ART_META_FORM', '', array(
    'id' => $article_id,
    'clang' => $clang,
    'article' => $article
));

$content .= '</fieldset>';

$formElements = array();

$n = array();
$n['field'] = '<button class="rex-button" type="submit" name="savemeta"' . rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save') . '>' . rex_i18n::msg('update_metadata') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.tpl');


// ----- EXTENSION POINT
$content .= rex_extension::registerPoint('ART_META_FORM_SECTION', '', array(
    'id' => $article_id,
    'clang' => $clang
));

$content .= '
                                    </form>
                                </div>';

echo rex_view::contentBlock($content);
