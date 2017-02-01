<?php

/**
 * REDAXO Default-Theme.
 *
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
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

    rex_extension::register('PACKAGES_INCLUDED', function () {
        rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    }, rex_extension::EARLY);

    rex_view::addJsFile($this->getAssetsUrl('javascripts/redaxo.js'));

    rex_extension::register('PAGE_HEADER', function (rex_extension_point $ep) {
        $icons = [];
        foreach (['57', '60', '72', '76', '114', '120', '144', '152'] as $size) {
            $size = $size . 'x' . $size;
            $icons[] = '<link rel="apple-touch-icon-precomposed" sizes="' . $size . '" href="' . $this->getAssetsUrl('images/apple-touch-icon-' . $size . '.png') . '" />';
        }
        foreach (['16', '32', '96', '128', '196'] as $size) {
            $size = $size . 'x' . $size;
            $icons[] = '<link rel="icon" type="image/png" href="' . $this->getAssetsUrl('images/favicon-' . $size . '.png') . '" sizes="' . $size . '" />';
        }

        $icons[] = '<meta name="msapplication-TileColor" content="#FFFFFF" />';
        $icons[] = '<meta name="msapplication-TileImage" content="' . $this->getAssetsUrl('images/mstile-144x144.png') . '" />';

        foreach (['70', '150', '310'] as $size) {
            $size = $size . 'x' . $size;
            $icons[] = '<meta name="msapplication-square' . $size . 'logo" content="' . $this->getAssetsUrl('images/mstile-' . $size . '.png') . '" />';
        }
        $icons[] = '<meta name="msapplication-wide310x150logo" content="' . $this->getAssetsUrl('images/mstile-310x150.png') . '" />';

        $icons = implode("\n    ", $icons);
        $ep->setSubject($icons . $ep->getSubject());
    });
}
