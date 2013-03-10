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
$func       = rex_request('func', 'string');

// -------------- Form Submits
$add_clang_save  = rex_post('add_clang_save', 'boolean');
$edit_clang_save = rex_post('edit_clang_save', 'boolean');


$warning = '';
$info = '';

// ----- delete clang
if ($func == 'deleteclang' && $clang_id != '') {
    if (rex_clang::exists($clang_id)) {
        rex_clang_service::deleteCLang($clang_id);
        $info = rex_i18n::msg('clang_deleted');
        $func = '';
        unset ($clang_id);
    }
}

// ----- add clang
if ($add_clang_save || $edit_clang_save) {
    if ($clang_code == '') {
        $warning = rex_i18n::msg('enter_code');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ($clang_name == '') {
        $warning = rex_i18n::msg('enter_name');
        $func = $add_clang_save ? 'addclang' : 'editclang';
    } elseif ($add_clang_save) {
        if (!rex_clang::exists($clang_id)) {
            $info = rex_i18n::msg('clang_created');
            rex_clang_service::addCLang($clang_id, $clang_code, $clang_name);
            unset ($clang_id);
            $func = '';
        } else {
            $warning = rex_i18n::msg('id_exists');
            $func = 'addclang';
        }
    } else {
        if (rex_clang::exists($clang_id)) {
            rex_clang_service::editCLang($clang_id, $clang_code, $clang_name);
            $info = rex_i18n::msg('clang_edited');
            $func = '';
            unset ($clang_id);
        }
    }
}

// seltype
$sel = new rex_select;
$sel->setName('clang_id');
$sel->setStyle('class="rex-form-select"');
$sel->setId('rex-form-clang-id');
$sel->setSize(1);
$clangIds = rex_clang::getAllIds();
for ($i = 1, $count = 0; $count < 10; ++$i) {
    if (!in_array($i, $clangIds)) {
        ++$count;
        $sel->addOption($i, $i);
    }
}

if ($info != '')
    $message .= rex_view::info($info);

if ($warning != '')
    $message .= rex_view::warning($warning);


$content .= '
            <div class="rex-form" id="rex-form-system-language">
            <form action="' . rex_url::currentBackendPage() . '" method="post">
        ';

if ($func == 'addclang' || $func == 'editclang') {
    $legend = $func == 'addclang' ? rex_i18n::msg('clang_add') : rex_i18n::msg('clang_edit');
    $content .= '
                <fieldset>
                    <input type="hidden" name="clang_id" value="' . $clang_id . '" />
            ';
}


$content .= '
        <table class="rex-table rex-table-middle rex-table-striped" summary="' . rex_i18n::msg('clang_summary') . '">
            <caption>' . rex_i18n::msg('clang_caption') . '</caption>
            <thead>
                <tr>
                    <th class="rex-slim"><a href="' . rex_url::currentBackendPage(['func' => 'addclang']) . '#clang"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'add') . '><span class="rex-icon rex-icon-add-language"></span></a></th>
                    <th class="rex-id">ID</th>
                    <th class="rex-code">' . rex_i18n::msg('clang_code') . '</th>
                    <th class="rex-name">' . rex_i18n::msg('clang_name') . '</th>
                    <th class="rex-function">' . rex_i18n::msg('clang_function') . '</th>
                </tr>
            </thead>
            <tbody>
    ';

// Add form
if ($func == 'addclang') {
    //ggf wiederanzeige des add forms, falls ungueltige id uebermittelt
    $content .= '
                <tr class="rex-active">
                    <td class="rex-slim"><span class="rex-icon rex-icon-language"></span></td>
                    <td class="rex-id">' . $sel->get() . '</td>
                    <td class="rex-code"><input type="text" id="rex-form-clang-code" name="clang_code" value="' . htmlspecialchars($clang_code) . '" /></td>
                    <td class="rex-name"><input type="text" id="rex-form-clang-name" name="clang_name" value="' . htmlspecialchars($clang_name) . '" /></td>
                    <td class="rex-save"><button class="rex-button" type="submit" name="add_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_add'), 'save') . ' value="1">' . rex_i18n::msg('clang_add') . '</button></td>
                </tr>
            ';
}
foreach (rex_clang::getAll() as $lang_id => $lang) {

    $add_td = '';
    $add_td = '<td class="rex-id">' . $lang_id . '</td>';

    $delLink = rex_i18n::msg('clang_delete');
    if ($lang_id == 0)
     $delLink = '<span class="rex-delete rex-disabled">' . $delLink . '</span>';
    else
        $delLink = '<a class="rex-delete" href="' . rex_url::currentBackendPage(['func' => 'deleteclang', 'clang_id' => $lang_id]) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . $delLink . '</a>';

    // Edit form
    if ($func == 'editclang' && $clang_id == $lang_id) {
        $content .= '
                    <tr class="rex-active">
                        <td class="rex-slim"><span class="rex-icon rex-icon-language"></span></td>
                        ' . $add_td . '
                        <td class="rex-code"><input type="text" id="rex-form-clang-code" name="clang_code" value="' . htmlspecialchars($lang->getCode()) . '" /></td>
                        <td class="rex-name"><input type="text" id="rex-form-clang-name" name="clang_name" value="' . htmlspecialchars($lang->getName()) . '" /></td>
                        <td class="rex-save"><button class="rex-button" type="submit" name="edit_clang_save"' . rex::getAccesskey(rex_i18n::msg('clang_update'), 'save') . ' value="1">' . rex_i18n::msg('clang_update') . '</button></td>
                    </tr>';

    } else {
        $editLink = rex_url::currentBackendPage(['func' => 'editclang', 'clang_id' => $lang_id]) . '#clang';

        $content .= '
                    <tr>
                        <td class="rex-slim"><a href="' . $editLink . '" title="' . htmlspecialchars($clang_name) . '"><span class="rex-icon rex-icon-language"></span></a></td>
                        ' . $add_td . '
                        <td class="rex-code">' . htmlspecialchars($lang->getCode()) . '</td>
                        <td class="rex-name"><a href="' . $editLink . '">' . htmlspecialchars($lang->getName()) . '</a></td>
                        <td class="rex-delete">' . $delLink . '</td>
                    </tr>';
    }
}

$content .= '
        </tbody>
    </table>';

if ($func == 'addclang' || $func == 'editclang') {
    $content .= '
                    <script type="text/javascript">
                        <!--
                        jQuery(function($){
                            $("#rex-form-clang-name").focus();
                        });
                        //-->
                    </script>
                </fieldset>';
}

$content .= '
            </form>
            </div>';

echo $message;
echo rex_view::contentBlock($content);
