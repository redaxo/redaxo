<?php

/**
 * Page Content Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

rex_perm::register('moveSlice[]', null, rex_perm::OPTIONS);
rex_complex_perm::register('modules', 'rex_module_perm');

if (rex::isBackend()) {
    /**
     * Content sidebar
     */
    rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function(rex_extension_point $ep) {
        $subject = $ep->getSubject();
        $params = $ep->getParams();

        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $params['article_id'],
            'clang' => $params['clang'],
            'ctype' => $params['ctype'],
        ]);

        $panel = rex_button_content_copy::init($params['article_id'], $context)->get();

        $fragment = new rex_fragment();
        $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.rex_i18n::msg('content_submitcopycontent'), false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('article_id', $params['article_id'], false);
        $fragment->setVar('clang', $params['clang'], false);
        $fragment->setVar('ctype', $params['ctype'], false);
        $fragment->setVar('collapse', true);
        $fragment->setVar('collapsed', false);
        $subject .= $fragment->parse('core/page/section.php');

        return $subject;
    });

    rex_extension::register('PAGE_CHECKED', function () {
        if (rex_be_controller::getCurrentPagePart(1) == 'content') {
            rex_be_controller::getPageObject('structure')->setIsActive(true);
        }
    });

    if (rex_be_controller::getCurrentPagePart(1) == 'system') {
        rex_system_setting::register(new rex_system_setting_default_template_id());
    }

    rex_extension::register('CLANG_DELETED', function (rex_extension_point $ep) {
        $del = rex_sql::factory();
        $del->setQuery('delete from ' . rex::getTablePrefix() . "article_slice where clang_id='" . $ep->getParam('clang')->getId() . "'");
    });
} else {
    rex_extension::register('FE_OUTPUT', function (rex_extension_point $ep) {
        $clangId = rex_get('clang', 'int');
        if ($clangId && !rex_clang::exists($clangId)) {
            rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getStartId());
        }

        $content = $ep->getSubject();

        $article = new rex_article_content();
        $article->setCLang(rex_clang::getCurrentId());

        if ($article->setArticleId(rex_article::getCurrentId())) {
            $content .= $article->getArticleTemplate();
        } else {
            $content .= 'Kein Startartikel selektiert / No starting Article selected. Please click here to enter <a href="' . rex_url::backendController() . '">redaxo</a>';
            rex_response::sendPage($content);
            exit;
        }

        $art_id = $article->getArticleId();
        if ($art_id == rex_article::getNotfoundArticleId() && $art_id != rex_article::getSiteStartArticleId()) {
            rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
        }

        // ----- inhalt ausgeben
        rex_response::sendPage($content, $article->getValue('updatedate'));
    });
}
