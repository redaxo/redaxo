<?php

assert(isset($rexFileCategory) && is_int($rexFileCategory));
assert(isset($argFields) && is_string($argFields));
assert(isset($fileId) && is_int($fileId));

$subpage = rex_be_controller::getCurrentPagePart(2);

$mediaName = rex_request('media_name', 'string');
$csrf = rex_csrf_token::factory('mediapool');

// *************************************** KATEGORIEN CHECK UND AUSWAHL

$selMedia = new rex_media_category_select($checkPerm = false);
$selMedia->setId('rex_file_category');
$selMedia->setName('rex_file_category');
$selMedia->setSize(1);
$selMedia->setSelected($rexFileCategory);
$selMedia->setAttribute('onchange', 'this.form.submit();');
$selMedia->setAttribute('class', 'selectpicker');
$selMedia->setAttribute('data-live-search', 'true');

if (rex::requireUser()->getComplexPerm('media')->hasAll()) {
    $selMedia->addOption(rex_i18n::msg('pool_kats_no'), '0');
}

// ----- EXTENSION POINT
echo rex_extension::registerPoint(new rex_extension_point('PAGE_MEDIAPOOL_HEADER', '', [
    'subpage' => $subpage,
    'category_id' => $rexFileCategory,
]));

$formElements = [];
$n = [];
$n['field'] = '<input class="form-control" type="text" name="media_name" id="be_search-media-name" value="' . rex_escape($mediaName) . '" />';
$n['before'] = $selMedia->get();
$n['right'] = '<button class="btn btn-search" type="submit"><i class="rex-icon rex-icon-search"></i></button>';
$formElements[] = $n;

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
    'category_id' => $rexFileCategory,
]));

// *************************************** Subpage: Media

if ($fileId) {
    require __DIR__ .'/media.detail.php';
}

// *************************************** SUBPAGE: "" -> MEDIEN ANZEIGEN

if (!$fileId) {
    require __DIR__.'/media.list.php';
}
