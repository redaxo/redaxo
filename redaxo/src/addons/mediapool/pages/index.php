<?php

/**
 * @package redaxo5
 */

echo rex_view::title('jjj');

rex_be_controller::includeCurrentPageSubPath();

return;

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
$argUrl = ['args' => $args];
$argFields = '';
foreach ($args as $argName => $argValue) {
    $argFields .= '<input type="hidden" name="args[' . rex_escape($argName) . ']" value="' . rex_escape($argValue) . '" />' . "\n";
}

// ----- opener_input_field setzen
$openerLink = rex_request('opener_link', 'string');
$openerInputField = rex_request('opener_input_field', 'string', '');

if ('' != $openerInputField) {
    if (!preg_match('{^[A-Za-z]+[\w\-\:\.]*$}', $openerInputField)) {
        throw new Exception('invalid opener_input_field given: '. $openerInputField);
    }

    $openerId = null;
    if ('REX_MEDIALIST_' == substr($openerInputField, 0, 14)) {
        $openerId = (int) substr($openerInputField, 14, strlen($openerInputField));
    }

    $argUrl['opener_input_field'] = $openerInputField;
    $argFields .= '<input type="hidden" name="opener_input_field" value="' . rex_escape($openerInputField) . '"/>' . "\n";
}

// -------------- CatId in Session speichern
$mediaId = rex_request('file_id', 'int');
$mediaName = rex_request('file_name', 'string');

if ('' != $mediaName) {
    $media = rex_media::get($mediaName);
    if ($media) {
        $fileId = (int) $media->getId();
    }
} elseif ($mediaId > 0) {
    $media = rex_media::getById($mediaId);
    if ($media) {
        $mediaId = (int) $media->getId();
    }
}

/*if (-1 == $rex_file_category) {
    $rex_file_category = rex_session('media[rex_file_category]', 'int');
}*/

/*$gc = rex_sql::factory();
$gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=?', [$rex_file_category]);
if (1 != $gc->getRows()) {
    $rex_file_category = 0;
    $rex_file_category_name = rex_i18n::msg('pool_kats_no');
} else {
    $rex_file_category_name = $gc->getValue('name');
}

rex_set_session('media[rex_file_category]', $rex_file_category);

// -------------- PERMS
$PERMALL = rex::getUser()->getComplexPerm('media')->hasCategoryPerm(0);*/

// -------------- Header
$subline = rex_be_controller::getPageObject('mediapool')->getSubpages();

foreach ($subline as $sp) {
    $sp->setHref(rex_url::backendPage($sp->getFullKey(), $argUrl, false));
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
        <?= $openerInputField ? 'rex.mediapoolOpenerInputField = "'.rex_escape($openerInputField, 'js').'";' : '' ?>
    </script>
    <?php
}

// -------------- Include Page
rex_be_controller::includeCurrentPageSubPath(compact('opener_input_field', 'opener_link', 'arg_url', 'args', 'arg_fields', 'media_id', 'error', 'success')); // , 'PERMALL' 'rex_file_category', 'rex_file_category_name',
