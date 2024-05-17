<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Util\Markdown;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\View;

$addon = Addon::get('install');

echo View::title($addon->i18n('title'));

if ('reload' === Request::request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

$markdown = static function (string $content): string {
    $fragment = new Fragment();
    $fragment->setVar('content', Markdown::factory()->parse($content), false);

    return $fragment->parse('core/page/readme.php');
};

Controller::includeCurrentPageSubPath(['markdown' => $markdown]);
