<?php

if (rex::isSetup()) {
    rex_response::sendRedirect(rex_url::backendController());
}

if (rex::isDebugMode()) {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

if (0 != rex::getConfig('phpmailer_errormail')) {
    rex_extension::register('RESPONSE_SHUTDOWN', static function () {
        rex_mailer::errorMail();
    });
}

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
    $clangId = rex_get('clang', 'int');
    if ($clangId && !rex_clang::exists($clangId)) {
        rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getStartId());
    }

    $content = $ep->getSubject();

    $article = new rex_article_content();
    $article->setClang(rex_clang::getCurrentId());

    if (!$article->setArticleId(rex_article::getCurrentId())) {
        if (!rex::isDebugMode() && !rex_backend_login::hasSession()) {
            throw new rex_exception('Article with id ' . rex_article::getCurrentId() . ' does not exist');
        }

        $fragment = new rex_fragment([
            'content' => '<p><b>Article with ID ' . rex_article::getCurrentId() . ' not found.</b><br />If this is a fresh setup, an article must be created first.<br />Enter <a href="' . rex_url::backendController() . '">REDAXO</a>.</p>',
        ]);
        $content .= $fragment->parse('core/fe_ooops.php');
        rex_response::sendPage($content);
        exit;
    }

    try {
        $content .= $article->getArticleTemplate();
    } catch (rex_article_not_found_exception) {
        $article = new rex_article_content();
        $article->setClang(rex_clang::getCurrentId());
        $article->setArticleId(rex_article::getNotfoundArticleId());

        $content .= $article->getArticleTemplate();
    }

    $artId = $article->getArticleId();
    if ($artId == rex_article::getNotfoundArticleId() && $artId != rex_article::getSiteStartArticleId()) {
        rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
    }

    // ----- inhalt ausgeben
    rex_response::sendPage($content, $article->getValue('updatedate'));
});

// ----- caching end für output filter
$CONTENT = ob_get_clean();

// trigger api functions. the api function is responsible for checking permissions.
rex_api_function::handleCall();

if (rex_extension::isRegistered('FE_OUTPUT')) {
    // ----- EXTENSION POINT
    rex_extension::registerPoint(new rex_extension_point('FE_OUTPUT', $CONTENT));
} else {
    // ----- inhalt ausgeben
    rex_response::sendPage($CONTENT);
}
