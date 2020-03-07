<?php

/**
 * @package redaxo5
 */

global $subpage, $ftitle, $error, $success;

// -------------- Defaults
$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');
$success = rex_escape(rex_request('info', 'string'));
$error = rex_escape(rex_request('warning', 'string'));
$args = rex_request('args', 'array');

$regex = '@&lt;(/?(?:b|i|code)|br ?/?)&gt;@i';
$success = preg_replace($regex, '<$1>', $success);
$error = preg_replace($regex, '<$1>', $error);

// -------------- Additional Args
$arg_url = ['args' => $args];
$arg_fields = '';
foreach ($args as $arg_name => $arg_value) {
    $arg_fields .= '<input type="hidden" name="args[' . rex_escape($arg_name) . ']" value="' . rex_escape($arg_value) . '" />' . "\n";
}

// ----- opener_input_field setzen
$opener_link = rex_request('opener_link', 'string');
$opener_input_field = rex_request('opener_input_field', 'string', '');

if ('' != $opener_input_field) {
    if (!preg_match('{^[A-Za-z]+[\w\-\:\.]*$}', $opener_input_field)) {
        throw new Exception('invalid opener_input_field given: '. $opener_input_field);
    }

    $opener_id = null;
    if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
        $opener_id = (int) substr($opener_input_field, 14, strlen($opener_input_field));
    }

    $arg_url['opener_input_field'] = $opener_input_field;
    $arg_fields .= '<input type="hidden" name="opener_input_field" value="' . rex_escape($opener_input_field) . '"/>' . "\n";
}

// -------------- CatId in Session speichern
$file_id = rex_request('file_id', 'int');
$file_name = rex_request('file_name', 'string');
$rex_file_category = rex_request('rex_file_category', 'int', -1);

if ('' != $file_name) {
    $sql = rex_sql::factory();
    $sql->setQuery('select * from ' . rex::getTablePrefix() . 'media where filename=?', [$file_name]);
    if (1 == $sql->getRows()) {
        $file_id = (int) $sql->getValue('id');
        $rex_file_category = (int) $sql->getValue('category_id');
    }
}

if (-1 == $rex_file_category) {
    $rex_file_category = rex_session('media[rex_file_category]', 'int');
}

$gc = rex_sql::factory();
$gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=?', [$rex_file_category]);
if (1 != $gc->getRows()) {
    $rex_file_category = 0;
    $rex_file_category_name = rex_i18n::msg('pool_kats_no');
} else {
    $rex_file_category_name = $gc->getValue('name');
}

rex_set_session('media[rex_file_category]', $rex_file_category);

// -------------- PERMS
$PERMALL = rex::getUser()->getComplexPerm('media')->hasCategoryPerm(0);

// -------------- Header
$subline = rex_be_controller::getPageObject('mediapool')->getSubpages();

foreach ($subline as $sp) {
    $sp->setHref(rex_url::backendPage($sp->getFullKey(), $arg_url, false));
}

echo rex_view::title(rex_i18n::msg('pool_media'), $subline);

// -------------- Messages
if ('' != $success) {
    echo rex_view::info($success);
    $success = '';
}
if ('' != $error) {
    echo rex_view::error($error);
    $error = '';
}

if (!rex_request::isXmlHttpRequest()) {
    ?>
    <script type="text/javascript">
        rex_retain_popup_event_handlers("rex:selectMedia");
        <?= $opener_input_field ? 'rex.mediapoolOpenerInputField = "'.rex_escape($opener_input_field, 'js').'";' : '' ?>
    </script>
    <?php
}

// -------------- Include Page
rex_be_controller::includeCurrentPageSubPath(compact('opener_input_field', 'opener_link', 'arg_url', 'args', 'arg_fields', 'rex_file_category', 'rex_file_category_name', 'PERMALL', 'file_id', 'error', 'success'));
