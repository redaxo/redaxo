<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Util\Markdown;
use Redaxo\Core\View\View;

$addon = Addon::get('install');

echo View::title($addon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

$markdown = static function (string $content): string {
    $fragment = new rex_fragment();
    $fragment->setVar('content', Markdown::factory()->parse($content), false);

    return $fragment->parse('core/page/readme.php');
};

Controller::includeCurrentPageSubPath(['markdown' => $markdown]);
