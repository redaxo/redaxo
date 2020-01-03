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
    rex_extension::register('PAGE_CHECKED', static function () {
        if ('content' == rex_be_controller::getCurrentPagePart(1)) {
            rex_be_controller::getPageObject('structure')->setIsActive(true);
        }
    });

    if ('system' == rex_be_controller::getCurrentPagePart(1)) {
        rex_system_setting::register(new rex_system_setting_default_template_id());
    }

    if ('content' == rex_be_controller::getCurrentPagePart(1)) {
        rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', static function (rex_extension_point $ep) {
            $params = $ep->getParams();
            $subject = $ep->getSubject();

            $article = rex_article::get($params['article_id'], $params['clang']);
            $articleStatusTypes = rex_article_service::statusTypes();

            $panel = '';
            $panel .= '<dl class="dl-horizontal text-left">';

            $panel .= '<dt>'.rex_i18n::msg('created_by').'</dt>';
            $panel .= '<dd>'.rex_escape($article->getValue('createuser')).'</dd>';

            $panel .= '<dt>'.rex_i18n::msg('created_on').'</dt>';
            $panel .= '<dd>'.rex_formatter::strftime($article->getValue('createdate'), 'date').'</dd>';

            $panel .= '<dt>'.rex_i18n::msg('updated_by').'</dt>';
            $panel .= '<dd>'.rex_escape($article->getValue('updateuser')).'</dd>';

            $panel .= '<dt>'.rex_i18n::msg('updated_on').'</dt>';
            $panel .= '<dd>'.rex_formatter::strftime($article->getValue('updatedate'), 'date').'</dd>';

            $panel .= '<dt>'.rex_i18n::msg('status').'</dt>';
            $panel .= '<dd class="'.$articleStatusTypes[$article->getValue('status')][1].'">'.$articleStatusTypes[$article->getValue('status')][0].'</dd>';

            $panel .= '</dl>';
            $fragment = new rex_fragment();
            $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.rex_i18n::msg('metadata'), false);
            $fragment->setVar('body', $panel, false);
            $fragment->setVar('collapse', true);
            $fragment->setVar('collapsed', true);
            $content = $fragment->parse('core/page/section.php');

            return $content.$subject;
        });
    }

    rex_extension::register('CLANG_DELETED', static function (rex_extension_point $ep) {
        $del = rex_sql::factory();
        $del->setQuery('delete from ' . rex::getTablePrefix() . "article_slice where clang_id='" . $ep->getParam('clang')->getId() . "'");
    });
} else {
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
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
            $fragment = new rex_fragment([
                'content' => '<p><b>Kein Startartikel selektiert - No starting Article selected.</b><br />Please click here to enter <a href="' . rex_url::backendController() . '">redaxo</a>.</p>',
            ]);
            $content .= $fragment->parse('core/fe_ooops.php');
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

rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
    static $urls = [
        'template' => ['templates', 'template_id'],
        'module' => ['modules/modules', 'module_id'],
        'action' => ['modules/actions', 'action_id'],
    ];

    if (preg_match('@^rex:///(template|module|action)/(\d+)@', $ep->getParam('file'), $match)) {
        return rex_url::backendPage($urls[$match[1]][0], ['function' => 'edit', $urls[$match[1]][1] => $match[2]]);
    }
});
