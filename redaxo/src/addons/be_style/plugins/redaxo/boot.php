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
 */

$plugin = rex_plugin::get('be_style', 'redaxo');

if (rex::isBackend()) {
    rex_extension::register('BE_STYLE_SCSS_FILES', function (rex_extension_point $ep) use ($plugin) {
        $subject = $ep->getSubject();
        $file = $plugin->getPath('scss/default.scss');
        array_unshift($subject, $file);
        return $subject;
    }, rex_extension::EARLY);

    rex_extension::register('BE_STYLE_SCSS_COMPILE', function (rex_extension_point $ep) use ($plugin) {
        $subject = $ep->getSubject();
        $subject[] = [
            'root_dir' => $plugin->getPath('scss/'),
            'scss_files' => $plugin->getPath('scss/master.scss'),
            'css_file' => $plugin->getPath('assets/css/styles.css'),
            'copy_dest' => $plugin->getAssetsPath('css/styles.css'),
        ];
        return $subject;
    });

    if (rex::getUser() && $plugin->getProperty('compile')) {
        rex_addon::get('be_style')->setProperty('compile', true);
    }

    rex_view::addCssFile($plugin->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($plugin->getAssetsUrl('javascripts/redaxo.js'));

    rex_extension::register('PAGE_HEADER', function (rex_extension_point $ep) use ($plugin) {
        $icons = [];

        $icons[] = '<link rel="apple-touch-icon" sizes="180x180" href="' . $plugin->getAssetsUrl('icons/apple-touch-icon.png') . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $plugin->getAssetsUrl('icons/favicon-32x32.png') . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $plugin->getAssetsUrl('icons/favicon-16x16.png') . '">';
        $icons[] = '<link rel="manifest" href="' . $plugin->getAssetsUrl('icons/site.webmanifest') . '">';
        $icons[] = '<link rel="mask-icon" href="' . $plugin->getAssetsUrl('icons/safari-pinned-tab.svg') . '" color="#4d99d3">';
        $icons[] = '<meta name="msapplication-TileColor" content="#2d89ef">';
        $icons[] = '<meta name="theme-color" content="#4d99d3">';

        $icons = implode("\n    ", $icons);
        $ep->setSubject($icons . $ep->getSubject());
    });
}
