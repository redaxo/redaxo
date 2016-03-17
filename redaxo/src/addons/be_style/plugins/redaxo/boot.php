<?php

/**
 * REDAXO Default-Theme.
 *
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]org Thomas Blum
 *
 * @package redaxo5
 *
 * @var rex_plugin $this
 */

$mypage = 'redaxo';

if (rex::isBackend()) {
    rex_extension::register('BE_STYLE_SCSS_FILES', function (rex_extension_point $ep) use ($mypage) {
        $subject = $ep->getSubject();
        $file = rex_plugin::get('be_style', $mypage)->getPath('scss/default.scss');
        array_unshift($subject, $file);
        return $subject;
    }, rex_extension::EARLY);

    if (rex::getUser() && $this->getProperty('compile')) {
        rex_addon::get('be_style')->setProperty('compile', true);

        rex_extension::register('PACKAGES_INCLUDED', function () {
            $compiler = new rex_scss_compiler();
            $compiler->setRootDir($this->getPath('scss/'));
            $compiler->setScssFile($this->getPath('scss/master.scss'));

            // Compile in backend assets dir
            $compiler->setCssFile($this->getPath('assets/css/styles.css'));

            $compiler->compile();

            // Compiled file to copy in frontend assets dir
            rex_file::copy($this->getPath('assets/css/styles.css'), $this->getAssetsPath('css/styles.css'));
        });
    }

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/redaxo.js'));

    rex_extension::register('PAGE_HEADER', function (rex_extension_point $ep) {
        $ep->setSubject('
            <link rel="apple-touch-icon-precomposed" sizes="57x57" href="' . $this->getAssetsUrl('images/apple-touch-icon-57x57.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="60x60" href="' . $this->getAssetsUrl('images/apple-touch-icon-60x60.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="76x76" href="' . $this->getAssetsUrl('images/apple-touch-icon-76x76.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="72x72" href="' . $this->getAssetsUrl('images/apple-touch-icon-72x72.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="114x114" href="' . $this->getAssetsUrl('images/apple-touch-icon-114x114.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="120x120" href="' . $this->getAssetsUrl('images/apple-touch-icon-120x120.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="144x144" href="' . $this->getAssetsUrl('images/apple-touch-icon-144x144.png') . '" />
            <link rel="apple-touch-icon-precomposed" sizes="152x152" href="' . $this->getAssetsUrl('images/apple-touch-icon-152x152.png') . '" />
            <link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-16x16.png') . '" sizes="16x16" />
            <link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-32x32.png') . '" sizes="32x32" />
            <link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-96x96.png') . '" sizes="96x96" />
            <link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-128x128.png') . '" sizes="128x128" />
            <link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-196x196.png') . '" sizes="196x196" />
            <meta name="msapplication-TileColor" content="#FFFFFF" />
            <meta name="msapplication-TileImage" content="' . $this->getAssetsUrl('images/mstile-144x144.png') . '" />
            <meta name="msapplication-square70x70logo" content="' . $this->getAssetsUrl('images/mstile-70x70.png') . '" />
            <meta name="msapplication-square150x150logo" content="' . $this->getAssetsUrl('images/mstile-150x150.png') . '" />
            <meta name="msapplication-square310x310logo" content="' . $this->getAssetsUrl('images/mstile-310x310.png') . '" />
            <meta name="msapplication-wide310x150logo" content="' . $this->getAssetsUrl('images/mstile-310x150.png') . '" />
            ' . $ep->getSubject()
        );
    }, rex_extension::EARLY);
}
