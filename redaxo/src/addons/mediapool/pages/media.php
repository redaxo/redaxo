<?php

if (0 > rex_request('media_id', 'int', 0)) {
    require __DIR__ .'/edit.php';
} else {
    require __DIR__.'/media.list.php';
}

return;






/**
 * @package redaxo5
 */

assert(isset($argFields) && is_string($argFields));
assert(isset($mediaId) && is_int($mediaId));

$subpage = rex_be_controller::getCurrentPagePart(2);

$mediaName = rex_request('media_name', 'string');
$csrf = rex_csrf_token::factory('mediapool');

$formElements = [];

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$toolbar = '<div class="rex-truncate-dropdown">' . $fragment->parse('core/form/input_group.php') . '</div>';

$toolbar = '
<div class="navbar-form navbar-right">
<form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $argFields . '
    <div class="form-group">
    ' . $toolbar . '
    </div>
</form>
</div>';

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
]);

// ----- EXTENSION POINT
$toolbar = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_TOOLBAR', $toolbar, [
    'subpage' => $subpage,
//    'category_id' => $rex_file_category,
]));

// *************************************** Subpage: Media

if ($mediaId) {

    dump($mediaId);

    require __DIR__ .'/media.detail.php';
}

// *************************************** SUBPAGE: "" -> MEDIEN ANZEIGEN

if (!$mediaId) {
    require __DIR__.'/media.list.php';
}
