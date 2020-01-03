<?php

/**
 * Verwaltung der Content Sprachen.
 *
 * @package redaxo5
 */

$content = '';
$message = '';

// -------------- Defaults
$clang_id = rex_request('clang_id', 'int');
$clang_code = rex_request('clang_code', 'string');
$clang_name = rex_request('clang_name', 'string');
$clang_prio = rex_request('clang_prio', 'int');
$clang_status = rex_request('clang_status', 'int');
$func = rex_request('func', 'string');

// -------------- Form Submits
$add_clang_save = rex_post('add_clang_save', 'boolean');
$edit_clang_save = rex_post('edit_clang_save', 'boolean');

$error = '';
$success = '';

$csrfToken = rex_csrf_token::factory('clang');

// ----- delete clang
if ('deleteclang' == $func && '' != $clang_id && rex_clang::exists($clang_id)) {
    try {
        if (!$csrfToken->isValid()) {
            throw new rex_functional_exception(rex_i18n::msg('csrf_token_invalid'));
        }
        rex_clang_service::deleteCLang($clang_id);
        $success = rex_i18n::msg('clang_deleted');
        $func = '';
        $clang_id = 0;
    } catch (rex_functional_exception $e) {
        echo rex_view::error($e->getMessage());
    }
}

if ('editstatus' === $func && rex_clang::exists($clang_id)) {
    try {
        if (!$csrfToken->isValid()) {
            throw new rex_functional_exception(rex_i18n::msg('csrf_token_invalid'));
        }
        $clang = rex_clang::get($clang_id);
        rex_clang_service::editCLang($clang_id, $clang->getCode(), $clang->getName(), $clang->getPriority(), $clang_status);
        $success = rex_i18n::msg('clang_edited');
        $func = '';
        $clang_id = 0;
    } catch (rex_functional_exception $e) {
        echo rex_view::error($e->getMessage());
    }
}

// ----- add clang
if ($add_clang_save || $edit_clang_save) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ('' == $clang_code) {
        $error = rex_i18n::msg('enter_code');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ('' == $clang_name) {
        $error = rex_i18n::msg('enter_name');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ($add_clang_save) {
        $success = rex_i18n::msg('clang_created');
        rex_clang_service::addCLang($clang_code, $clang_name, $clang_prio);
        $clang_id = 0;
        $func = '';
    } else {
        if (rex_clang::exists($clang_id)) {
            rex_clang_service::editCLang($clang_id, $clang_code, $clang_name, $clang_prio);
            $success = rex_i18n::msg('clang_edited');
            $func = '';
            $clang_id = 0;
        }
    }
}

if ('' != $success) {
    $message .= rex_view::success($success);
}

if ('' != $error) {
    $message .= rex_view::error($error);
}

$content .= '
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'addclang']) . '#clang"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'add') . '><i class="rex-icon rex-icon-add-language"></i></a></th>
                    <th class="rex-table-id">' . rex_i18n::msg('id') . '</th>
                    <th>' . rex_i18n::msg('clang_code') . '</th>
                    <th>' . rex_i18n::msg('clang_name') . '</th>
                    <th class="rex-table-priority">' . rex_i18n::msg('clang_priority') . '</th>
                    <th class="rex-table-action" colspan="3">' . rex_i18n::msg('clang_function') . '</th>
                </tr>
            </thead>
            <tbody>
    ';

// Add form
if ('addclang' == $func) {
    // ----- EXTENSION POINT
    $metaButtons = rex_extension::registerPoint(new rex_extension_point('CLANG_FORM_BUTTONS', ''));

    //ggf wiederanzeige des add forms, falls ungueltige id uebermittelt
    $content .= '
                <tr class="mark">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-language"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">–</td>
                    <td data-title="' . rex_i18n::msg('clang_code') . '"><input class="form-control" type="text" id="rex-form-clang-code" name="clang_code" value="' . rex_escape($clang_code) . '" autofocus /></td>
                    <td data-title="' . rex_i18n::msg('clang_name') . '"><input class="form-control" type="text" id="rex-form-clang-name" name="clang_name" value="' . rex_escape($clang_name) . '" /></td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('clang_priority') . '"><input class="form-control" type="text" id="rex-form-clang-prio" name="clang_prio" value="' . ($clang_prio ?: rex_clang::count() + 1) . '" /></td>
                    <td class="rex-table-action">' . $metaButtons . '</td>
                    <td class="rex-table-action" colspan="2"><button class="btn btn-save" type="submit" name="add_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'save') . ' value="1">' . rex_i18n::msg('clang_add') . '</button></td>
                </tr>
            ';

    // ----- EXTENSION POINT
    $content .= rex_extension::registerPoint(new rex_extension_point('CLANG_FORM_ADD', ''));
}

$sql = rex_sql::factory()->setQuery('SELECT * FROM '.rex::getTable('clang').' ORDER BY priority');
foreach ($sql as $row) {
    $lang_id = $sql->getValue('id');
    $add_td = '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">' . $lang_id . '</td>';

    $delLink = rex_i18n::msg('delete');
    if ($lang_id == rex_clang::getStartId()) {
        $delLink = '<span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . $delLink . '</span>';
    } else {
        $delLink = '<a href="' . rex_url::currentBackendPage(['func' => 'deleteclang', 'clang_id' => $lang_id] + $csrfToken->getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . $delLink . '</a>';
    }

    // Edit form
    if ('editclang' == $func && $clang_id == $lang_id) {
        // ----- EXTENSION POINT
        $metaButtons = rex_extension::registerPoint(new rex_extension_point('CLANG_FORM_BUTTONS', '', ['id' => $clang_id, 'sql' => $sql]));

        $content .= '
                    <tr class="mark">
                        <td class="rex-table-icon"><i class="rex-icon rex-icon-language"></i></td>
                        ' . $add_td . '
                        <td data-title="' . rex_i18n::msg('clang_code') . '"><input class="form-control" type="text" id="rex-form-clang-code" name="clang_code" value="' . rex_escape($sql->getValue('code')) . '" autofocus /></td>
                        <td data-title="' . rex_i18n::msg('clang_name') . '"><input class="form-control" type="text" id="rex-form-clang-name" name="clang_name" value="' . rex_escape($sql->getValue('name')) . '" /></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('clang_priority') . '"><input class="form-control" type="text" id="rex-form-clang-prio" name="clang_prio" value="' . rex_escape($sql->getValue('priority')) . '" /></td>
                        <td class="rex-table-action">' . $metaButtons . '</td>
                        <td class="rex-table-action" colspan="2"><button class="btn btn-save" type="submit" name="edit_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_update'), 'save') . ' value="1">' . rex_i18n::msg('clang_update') . '</button></td>
                    </tr>';

        // ----- EXTENSION POINT
        $content .= rex_extension::registerPoint(new rex_extension_point('CLANG_FORM_EDIT', '', ['id' => $clang_id, 'sql' => $sql]));
    } else {
        $editLink = rex_url::currentBackendPage(['func' => 'editclang', 'clang_id' => $lang_id]) . '#clang';

        $status = $sql->getValue('status') ? 'online' : 'offline';

        $content .= '
                    <tr>
                        <td class="rex-table-icon"><a href="' . $editLink . '" title="' . rex_escape($clang_name) . '"><i class="rex-icon rex-icon-language"></i></a></td>
                        ' . $add_td . '
                        <td data-title="' . rex_i18n::msg('clang_code') . '">' . rex_escape($sql->getValue('code')) . '</td>
                        <td data-title="' . rex_i18n::msg('clang_name') . '">' . rex_escape($sql->getValue('name')) . '</td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('clang_priority') . '">' . rex_escape($sql->getValue('priority')) . '</td>
                        <td class="rex-table-action"><a href="' . $editLink . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit') . '</a></td>
                        <td class="rex-table-action">' . $delLink . '</td>
                        <td class="rex-table-action"><a class="rex-' . $status . '" href="' . rex_url::currentBackendPage(['clang_id' => $lang_id, 'func' => 'editstatus', 'clang_status' => $sql->getValue('status') ? 0 : 1] + $csrfToken->getUrlParams()) . '"><i class="rex-icon rex-icon-' . $status . '"></i> ' . rex_i18n::msg('clang_'.$status) . '</a></td>
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

if ('addclang' == $func || 'editclang' == $func) {
    $content = '
        <form id="rex-form-system-language" action="' . rex_url::currentBackendPage() . '" method="post">
            <fieldset>
                <input type="hidden" name="clang_id" value="' . $clang_id . '" />
                ' . $csrfToken->getHiddenField() . '
                ' . $content . '
            </fieldset>
        </form>
        ';
}

echo $content;
