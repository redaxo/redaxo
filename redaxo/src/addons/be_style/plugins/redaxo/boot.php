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

    rex_extension::register('BE_STYLE_SCSS_COMPILE', function (rex_extension_point $ep) {
        $subject = $ep->getSubject();
        $subject[] = [
            'root_dir' => $this->getPath('scss/'),
            'scss_files' => $this->getPath('scss/master.scss'),
            'css_file' => $this->getPath('assets/css/styles.css'),
            'copy_dest' => $this->getAssetsPath('css/styles.css'),
        ];
        return $subject;
    });

    if (rex::getUser() && $this->getProperty('compile')) {
        rex_addon::get('be_style')->setProperty('compile', true);
    }

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/redaxo.js'));

    rex_extension::register('PAGE_HEADER', function (rex_extension_point $ep) {
        $icons = [];

        $icons[] = '<link rel="apple-touch-icon" sizes="180x180" href="' . $this->getAssetsUrl('icons/apple-touch-icon.png') . '" />';
        $icons[] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->getAssetsUrl('icons/favicon-32x32.png') . '" />';
        $icons[] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $this->getAssetsUrl('icons/favicon-16x16.png') . '" />';
        $icons[] = '<link rel="manifest" href="' . $this->getAssetsUrl('icons/manifest.json') . '">';
        $icons[] = '<link rel="mask-icon" href="' . $this->getAssetsUrl('icons/safari-pinned-tab.svg') . '" color="#404040">';
        $icons[] = '<link rel="shortcut icon" href="' . $this->getAssetsUrl('icons/favicon.ico') . '">';
        $icons[] = '<meta name="msapplication-TileColor" content="#ffffff">';
        $icons[] = '<meta name="msapplication-TileImage" content="' . $this->getAssetsUrl('icons/mstile-144x144.png') . '">';
        $icons[] = '<meta name="msapplication-config" content="' . $this->getAssetsUrl('icons/browserconfig.xml') . '">';
        $icons[] = '<meta name="theme-color" content="#ffffff">';

        $icons = implode("\n    ", $icons);
        $ep->setSubject($icons . $ep->getSubject());
    });
}
