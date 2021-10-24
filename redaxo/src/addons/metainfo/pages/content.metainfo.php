<?php

assert(isset($ep) && $ep instanceof rex_extension_point);

$params = $ep->getParams();

$articleId = $params['article_id'];
$clang = $params['clang'];

$content = [];

$article = rex_article::get($articleId, $clang);
$articleStatusTypes = rex_article_service::statusTypes();
$status = (int) $article->getValue('status');

// ------------------

$panels = [];
$panels[] = '<dt>'.rex_i18n::msg('created_by').'</dt><dd>'.rex_escape($article->getValue('createuser')).'</dd>';
$panels[] = '<dt>'.rex_i18n::msg('created_on').'</dt><dd>'.rex_formatter::intlDate($article->getValue('createdate')).'</dd>';
$panels[] = '<dt>'.rex_i18n::msg('updated_by').'</dt><dd>'.rex_escape($article->getValue('updateuser')).'</dd>';
$panels[] = '<dt>'.rex_i18n::msg('updated_on').'</dt><dd>'.rex_formatter::intlDate($article->getValue('updatedate')).'</dd>';
$panels[] = '<dt>'.rex_i18n::msg('status').'</dt><dd class="'.$articleStatusTypes[$status][1].'">'.$articleStatusTypes[$status][0].'</dd>';
$content[] = '<dl class="dl-horizontal text-left">' . implode('', $panels) . '</dl>';

// ------------------

$article = rex_sql::factory();
$article->setQuery('
            SELECT
                article.*, template.attributes as template_attributes
            FROM
                '.rex::getTablePrefix().'article as article
            LEFT JOIN '.rex::getTablePrefix()."template as template
                ON template.id=article.template_id
            WHERE
                article.id='$articleId'
                AND clang_id=$clang"
);

if (1 == $article->getRows()) {
    // ----- ctype holen
    $templateAttributes = $article->getArrayValue('template_attributes');

    // Für Artikel ohne Template
    if (!is_array($templateAttributes)) {
        $templateAttributes = [];
    }

    $ctypes = $templateAttributes['ctype'] ?? []; // ctypes - aus dem template

    $ctype = rex_request('ctype', 'int', 1);
    if (!array_key_exists($ctype, $ctypes)) {
        $ctype = 1;
    } // default = 1

    $context = new rex_context([
        'page' => rex_be_controller::getCurrentPage(),
        'article_id' => $articleId,
        'clang' => $clang,
        'ctype' => $ctype,
    ]);

    $metainfoHandler = new rex_metainfo_article_handler();
    $form = $metainfoHandler->getForm([
        'id' => $articleId,
        'clang' => $clang,
        'article' => $article,
    ]);

    $formElements = [];
    $formElements[] = [
        'label' => '<label for="rex-id-meta-article-name">'.rex_i18n::msg('header_article_name').'</label>',
        'field' => '<input class="form-control" type="text" id="rex-id-meta-article-name" name="meta_article_name" value="'.htmlspecialchars(rex_article::get($articleId, $clang)->getName()).'" />',
    ];
    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $form = $fragment->parse('core/form/form.php').$form;

    $content[] = '
              <div id="rex-page-sidebar-metainfo" data-pjax-container="#rex-page-sidebar-metainfo">
                <form class="metainfo-sidebar" action="'.$context->getUrl().'" method="post" enctype="multipart/form-data">
                    '.(rex_post('savemeta', 'boolean') ? rex_view::success(rex_i18n::msg('minfo_metadata_saved')) : '').'
                    <fieldset>
                        <input type="hidden" name="save" value="1" />
                        <input type="hidden" name="ctype" value="'.$ctype.'" />
                        '.$form.'
                        <button class="btn btn-primary pull-left" type="submit" name="savemeta"'.rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save').' value="1">'.rex_i18n::msg('update_metadata').'</button>
                    </fieldset>
                </form>
              </div>
                ';
}

// ------------------

$fragment = new rex_fragment();
$fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.rex_i18n::msg('metadata'), false);
$fragment->setVar('body', implode('', $content), false);
$fragment->setVar('article_id', $params['article_id'], false);
$fragment->setVar('clang', $params['clang'], false);
$fragment->setVar('ctype', $params['ctype'], false);
$fragment->setVar('collapse', true);
$fragment->setVar('collapsed', false);
return $fragment->parse('core/page/section.php');
