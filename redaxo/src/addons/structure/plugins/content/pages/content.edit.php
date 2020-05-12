<?php

assert(isset($article_id) && is_int($article_id));
assert(isset($clang) && is_int($clang));
assert(isset($ctype) && is_int($ctype));
assert(isset($slice_id) && is_int($slice_id));
assert(isset($template_attributes) && is_array($template_attributes));
assert(isset($slice_revision) && is_int($slice_revision));
assert(isset($function) && is_string($function));
assert(isset($info) && is_string($info));
assert(isset($warning) && is_string($warning));

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
$content = $CONT->getArticle($ctype);

return '' != $content ? '<ul class="rex-slices">' . $content . '</ul>' : '';
