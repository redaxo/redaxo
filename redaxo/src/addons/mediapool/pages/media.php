<?php

/**
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);

$media_method = rex_request('media_method', 'string');
$media_name = rex_request('media_name', 'string');
$csrf = rex_csrf_token::factory('mediapool');

// *************************************** CONFIG

$media_manager = rex_addon::get('media_manager')->isAvailable();

// *************************************** KATEGORIEN CHECK UND AUSWAHL

$sel_media = new rex_media_category_select($check_perm = false);
$sel_media->setId('rex_file_category');
$sel_media->setName('rex_file_category');
$sel_media->setSize(1);
$sel_media->setSelected($rex_file_category);
$sel_media->setAttribute('onchange', 'this.form.submit();');
$sel_media->setAttribute('class', 'selectpicker');
$sel_media->setAttribute('data-live-search', 'true');

if (rex::getUser()->getComplexPerm('media')->hasAll()) {
    $sel_media->addOption(rex_i18n::msg('pool_kats_no'), '0');
}

// ----- EXTENSION POINT
echo rex_extension::registerPoint(new rex_extension_point('PAGE_MEDIAPOOL_HEADER', '', [
    'subpage' => $subpage,
    'category_id' => $rex_file_category,
]));

$formElements = [];
$n = [];
$n['field'] = '<input class="form-control" type="text" name="media_name" id="be_search-media-name" value="' . rex_escape($media_name, 'html_attr') . '" />';
$n['before'] = $sel_media->get();
$n['right'] = '<button class="btn btn-search" type="submit"><i class="rex-icon rex-icon-search"></i></button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$toolbar = '<div class="rex-truncate-dropdown">' . $fragment->parse('core/form/input_group.php') . '</div>';

$toolbar = '
<div class="navbar-form navbar-right">
<form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $arg_fields . '
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
    'category_id' => $rex_file_category,
]));

// *************************************** Subpage: Media

if ($file_id) {
    require __DIR__ .'/media.detail.php';
}

// *************************************** SUBPAGE: "" -> MEDIEN ANZEIGEN

if (!$file_id) {
    require __DIR__.'/media.list.php';
}
