<?php

$CONT = new rex_article_content_editor;
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

echo rex_view::contentBlock($content);
