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
        //$subject[] = rex_plugin::get('be_style', $mypage)->getPath('scss/master.scss');
        return $subject;
    }, rex_extension::EARLY);

    //rex::getUser() &&
    if ($this->getProperty('compile')) {
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
}
