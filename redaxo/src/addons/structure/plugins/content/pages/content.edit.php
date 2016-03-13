<?php

$apiFunc = rex_api_function::factory();
if ($apiFunc && $result = $apiFunc->getResult()) {
    if ($result->isSuccessfull()) {
        $info = $result->getMessage();
    } else {
        $warning = $result->getMessage();
    }
}

$CONT = new rex_article_content_editor();
$CONT->getContentAsQuery();
$CONT->info = $info;
$CONT->warning = $warning;
$CONT->template_attributes = $template_attributes;
$CONT->setArticleId($article_id);
$CONT->setSliceId($slice_id);
$CONT->setMode('edit');
$CONT->setCLang($clang);
$CONT->setEval(true);
$CONT->setSliceRevision($slice_revision);
$CONT->setFunction($function);
$content .= $CONT->getArticle($ctype);

return $content != '' ? '<ul class="rex-slices">' . $content . '</ul>' : '';
