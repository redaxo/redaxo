<?php

/**
 * Verwaltung der Content Sprachen
 * @package redaxo5
 */

$content = '';
$message = '';

// -------------- Defaults
$clang_id   = rex_request('clang_id', 'int');
$clang_code = rex_request('clang_code', 'string');
$clang_name = rex_request('clang_name', 'string');
$clang_prio = rex_request('clang_prio', 'int');
$func       = rex_request('func', 'string');

// -------------- Form Submits
$add_clang_save  = rex_post('add_clang_save', 'boolean');
$edit_clang_save = rex_post('edit_clang_save', 'boolean');


$error = '';
$success = '';

// ----- delete clang
if ($func == 'deleteclang' && $clang_id != '') {
    try {
        if (rex_clang::exists($clang_id)) {
            rex_clang_service::deleteCLang($clang_id);
            $success = rex_i18n::msg('clang_deleted');
            $func = '';
            unset ($clang_id);
        }

    } catch (rex_functional_exception $e) {
        echo rex_view::error($e->getMessage());

    }

}

// ----- add clang
if ($add_clang_save || $edit_clang_save) {
    if ($clang_code == '') {
        $error = rex_i18n::msg('enter_code');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ($clang_name == '') {
        $error = rex_i18n::msg('enter_name');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ($add_clang_save) {
        $success = rex_i18n::msg('clang_created');
        rex_clang_service::addCLang($clang_code, $clang_name, $clang_prio);
        unset($clang_id);
        $func = '';
    } else {
        if (rex_clang::exists($clang_id)) {
            rex_clang_service::editCLang($clang_id, $clang_code, $clang_name, $clang_prio);
            $success = rex_i18n::msg('clang_edited');
            $func = '';
            unset ($clang_id);
        }
    }
}

if ($success != '') {
    $message .= rex_view::success($success);
}

if ($error != '') {
    $message .= rex_view::error($error);
}



$content .= '
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th><a href="' . rex_url::currentBackendPage(['func' => 'addclang']) . '#clang"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'add') . '><i class="rex-icon rex-icon-add-language"></i></a></th>
                    <th>' . rex_i18n::msg('id') . '</th>
                    <th>' . rex_i18n::msg('clang_code') . '</th>
                    <th>' . rex_i18n::msg('clang_name') . '</th>
                    <th>' . rex_i18n::msg('clang_priority') . '</th>
                    <th colspan="2">' . rex_i18n::msg('clang_function') . '</th>
                </tr>
            </thead>
            <tbody>
    ';

// Add form
if ($func == 'addclang') {
    //ggf wiederanzeige des add forms, falls ungueltige id uebermittelt
    $content .= '
                <tr class="mark">
                    <td><i class="rex-icon rex-icon-language"></i></td>
                    <td data-title="' . rex_i18n::msg('id') . '">–</td>
                    <td data-title="' . rex_i18n::msg('clang_code') . '"><input class="form-control" type="text" id="rex-form-clang-code" name="clang_code" value="' . htmlspecialchars($clang_code) . '" autofocus /></td>
                    <td data-title="' . rex_i18n::msg('clang_name') . '"><input class="form-control" type="text" id="rex-form-clang-name" name="clang_name" value="' . htmlspecialchars($clang_name) . '" /></td>
                    <td data-title="' . rex_i18n::msg('clang_priority') . '"><input class="form-control" type="text" id="rex-form-clang-prio" name="clang_prio" value="' . ($clang_prio ?: rex_clang::count() + 1) . '" /></td>
                    <td colspan="2"><button class="btn btn-primary" type="submit" name="add_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'save') . ' value="1">' . rex_i18n::msg('clang_add') . '</button></td>
                </tr>
            ';
}
foreach (rex_clang::getAll() as $lang_id => $lang) {

    $add_td = '';
    $add_td = '<td data-title="' . rex_i18n::msg('id') . '">' . $lang_id . '</td>';

    $delLink = rex_i18n::msg('delete');
    if ($lang_id == rex_clang::getStartId()) {
     $delLink = '<span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . $delLink . '</span>';
    } else {
        $delLink = '<a href="' . rex_url::currentBackendPage(['func' => 'deleteclang', 'clang_id' => $lang_id]) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . $delLink . '</a>';
    }

    // Edit form
    if ($func == 'editclang' && $clang_id == $lang_id) {
        $content .= '
                    <tr class="mark">
                        <td><i class="rex-icon rex-icon-language"></i></td>
                        ' . $add_td . '
                        <td data-title="' . rex_i18n::msg('clang_code') . '"><input class="form-control" type="text" id="rex-form-clang-code" name="clang_code" value="' . htmlspecialchars($lang->getCode()) . '" autofocus /></td>
                        <td data-title="' . rex_i18n::msg('clang_name') . '"><input class="form-control" type="text" id="rex-form-clang-name" name="clang_name" value="' . htmlspecialchars($lang->getName()) . '" /></td>
                        <td data-title="' . rex_i18n::msg('clang_priority') . '"><input class="form-control" type="text" id="rex-form-clang-prio" name="clang_prio" value="' . htmlspecialchars($lang->getPriority()) . '" /></td>
                        <td colspan="2"><button class="btn btn-primary" type="submit" name="edit_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_update'), 'save') . ' value="1">' . rex_i18n::msg('clang_update') . '</button></td>
                    </tr>';

    } else {
        $editLink = rex_url::currentBackendPage(['func' => 'editclang', 'clang_id' => $lang_id]) . '#clang';

        $content .= '
                    <tr>
                        <td><a href="' . $editLink . '" title="' . htmlspecialchars($clang_name) . '"><i class="rex-icon rex-icon-language"></i></a></td>
                        ' . $add_td . '
                        <td data-title="' . rex_i18n::msg('clang_code') . '">' . htmlspecialchars($lang->getCode()) . '</td>
                        <td data-title="' . rex_i18n::msg('clang_name') . '">' . htmlspecialchars($lang->getName()) . '</td>
                        <td data-title="' . rex_i18n::msg('clang_priority') . '">' . htmlspecialchars($lang->getPriority()) . '</td>
                        <td><a href="' . $editLink . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit') . '</a></td>
                        <td>' . $delLink . '</td>
                    </tr>';
    }
}

$content .= '
        </tbody>
    </table>';


echo $message;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('clang_caption'), false);
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');



if ($func == 'addclang' || $func == 'editclang') {
    $content = '
        <form id="rex-form-system-language" action="' . rex_url::currentBackendPage() . '" method="post">
            <fieldset>
                <input type="hidden" name="clang_id" value="' . $clang_id . '" />
                ' . $content . '
            </fieldset>
        </form>
        ';
}

echo $content;
