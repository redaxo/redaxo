<?php

$content = rex_file::getOutput(rex_path::plugin('be_style', 'redaxo', 'assets/css/styles.css'));

preg_match_all('@\.rex-icon-(\w+):before@im', $content, $matches, PREG_SET_ORDER);

$icons_used = '';
if (count($matches) > 0) {
    $list = [];
    foreach ($matches as $match) {
        $list[$match[1]] = '<li><i class="rex-icon rex-icon-' . $match[1] . '"></i> rex-icon-' . $match[1] . '</li>';
    }

    ksort($list);

    $icons_used = '<ul class="rex-list-inline">' . implode('', $list) . '</ul>';
}

preg_match_all('@\.fa-(\w+):before@im', $content, $matches, PREG_SET_ORDER);

$icons_complete = '';
if (count($matches) > 0) {
    $list = [];
    foreach ($matches as $match) {
        $list[$match[1]] = '<li><i class="fa fa-' . $match[1] . '"></i> fa-' . $match[1] . '</li>';
    }

    ksort($list);

    $icons_complete = '<ul class="rex-list-inline">' . implode('', $list) . '</ul>';
}

$fragment = new rex_fragment();
$fragment->setVar('content', '<h3>REDAXO Icons</h3>' . $icons_used, false);
echo $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('content', '<h3>Font-Awesome Icons</h3>' . $icons_complete, false);
echo $fragment->parse('core/page/section.php');
