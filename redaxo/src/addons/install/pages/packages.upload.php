<?php

$addon = rex_addon::get('install');

$addonkey = rex_request('addonkey', 'string');
$addons = [];

echo rex_api_function::getMessage();

try {
    $addons = rex_install_packages::getMyPackages();
} catch (rex_functional_exception $e) {
    echo rex_view::error($e->getMessage());
    $addonkey = '';
}

if ($addonkey && isset($addons[$addonkey])) {
    $addon = $addons[$addonkey];
    $file_id = rex_request('file', 'string');

    if ($file_id) {
        $new = $file_id == 'new';
        $file = $new ? ['version' => '', 'description' => '', 'status' => 1] : $addon['files'][$file_id];

        $newVersion = rex_addon::get($addonkey)->getVersion();

        $uploadCheckboxDisabled = '';
        $hiddenField = '';
        if ($new || !rex_addon::exists($addonkey)) {
            $uploadCheckboxDisabled = ' disabled="disabled"';
            $hiddenField = '<input type="hidden" name="upload[upload_file]" value="' . ((int) $new) . '" />';
        }

        $panel = '<fieldset>';

        $formElements = [];

        $n = [];
        $n['label'] = '<label for="rex-js-install-packages-upload-version">' . $addon->i18n('version') . '</label>';
        $n['field'] = '<p class="form-control-static" id="rex-js-install-packages-upload-version">' . rex_escape($new ? $newVersion : $file['version']) . '</p>
                           <input type="hidden" name="upload[oldversion]" value="' . rex_escape($file['version']) . '" />';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="rex-install-packages-upload-description">' . $addon->i18n('description') . '</label>';
        $n['field'] = '<textarea class="form-control" id="rex-install-packages-upload-description" name="upload[description]" rows="15">' . rex_escape($file['description']) . '</textarea>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-install-packages-upload-status">' . $addon->i18n('online') . '</label>';
        $n['field'] = '<input id="rex-install-packages-upload-status" type="checkbox" name="upload[status]" value="1" ' . (!$new && $file['status'] ? 'checked="checked" ' : '') . '/>';
        $formElements[] = $n;

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-js-install-packages-upload-upload-file">' . $addon->i18n('upload_file') . '</label>' . $hiddenField;
        $n['field'] = '<input id="rex-js-install-packages-upload-upload-file" type="checkbox" name="upload[upload_file]" value="1" ' . ($new ? 'checked="checked" ' : '') . $uploadCheckboxDisabled . '/>';
        $formElements[] = $n;

        if (rex_addon::get($addonkey)->isInstalled() && is_dir(rex_url::addonAssets($addonkey))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-replace-assets">' . $addon->i18n('replace_assets') . '</label>';
            $n['field'] = '<input id="rex-js-install-packages-upload-replace-assets" type="checkbox" name="upload[replace_assets]" value="1" ' . ($new ? '' : 'disabled="disabled" ') . '/>';
            $formElements[] = $n;
        }

        if (is_dir(rex_path::addon($addonkey, 'tests'))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-ignore-tests">' . $addon->i18n('ignore_tests') . '</label>';
            $n['field'] = '<input id="rex-js-install-packages-upload-ignore-tests" type="checkbox" name="upload[ignore_tests]" value="1" checked="checked"' . ($new ? '' : 'disabled="disabled" ') . '/>';
            $formElements[] = $n;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/checkbox.php');

        $panel .= '</fieldset>';

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="upload[send]" value="' . $addon->i18n('send') . '">' . $addon->i18n('send') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-delete" value="' . $addon->i18n('delete') . '" onclick="if(confirm(\'' . $addon->i18n('delete') . ' ?\')) location.href=\'' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $file_id] + rex_api_install_package_delete::getUrlParams()) . '\';">' . $addon->i18n('delete') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $panel .= '</fieldset>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $addonkey . ' <small>' . $addon->i18n($new ? 'file_add' : 'file_edit') . '</small>', false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content = $fragment->parse('core/page/section.php');

        $content = '
            <form action="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $file_id] + rex_api_install_package_upload::getUrlParams()) . '" method="post">
                ' . $content . '
            </form>';
        echo $content;

        if (!$new) {
            echo '
    <script type="text/javascript"><!--

        jQuery(function($) {
            $("#rex-js-install-packages-upload-upload-file").change(function(){
                if($(this).is(":checked"))
                {
                    ' . ($newVersion != $file['version'] ? '$("#rex-js-install-packages-upload-version").html(\'<del class="rex-package-old-version">' . $file['version'] . '</del> <ins class="rex-package-new-version">' . $newVersion . '</ins>\');' : '') . '
                    $("#rex-js-install-packages-upload-replace-assets, #rex-js-install-packages-upload-ignore-tests").removeAttr("disabled");
                }
                else
                {
                    $("#rex-js-install-packages-upload-version").html("' . $file['version'] . '");
                    $("#rex-js-install-packages-upload-replace-assets, #rex-js-install-packages-upload-ignore-tests").attr("disabled", "disabled");
                }
            });
        });

    //--></script>';
        }
    } else {
        $icon = '';
        if (rex_addon::exists($addonkey)) {
            $icon = '<a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => 'new']) . '" title="' . $addon->i18n('file_add') . '"><i class="rex-icon rex-icon-add-package"></i></a>';
        }

        $panel = '

        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $addon->i18n('name') . '</th>
                <td data-title="' . $addon->i18n('name') . '">' . rex_escape($addon['name']) . '</td>
            </tr>
            <tr>
                <th>' . $addon->i18n('author') . '</th>
                <td data-title="' . $addon->i18n('author') . '">' . rex_escape($addon['author']) . '</td>
            </tr>
            <tr>
                <th>' . $addon->i18n('shortdescription') . '</th>
                <td data-title="' . $addon->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
            </tr>
            <tr>
                <th>' . $addon->i18n('description') . '</th>
                <td data-title="' . $addon->i18n('description') . '">' . nl2br(rex_escape($addon['description'])) . '</td>
            </tr>
            </tbody>
        </table>';

        $fragment = new rex_fragment();
        $fragment->setVar('title', $addonkey . ' <small>' . $addon->i18n('information') . '</small>', false);
        $fragment->setVar('content', $panel, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;

        $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon">' . $icon . '</th>
                <th class="rex-table-width-4">' . $addon->i18n('version') . '</th>
                <th>REDAXO</th>
                <th>' . $addon->i18n('description') . '</th>
                <th class="rex-table-action" colspan="2">' . $addon->i18n('status') . '</th>
            </tr>
            </thead>
            <tbody>';

        foreach ($addon['files'] as $fileId => $file) {
            $url = rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId]);
            $status = $file['status'] ? 'online' : 'offline';
            $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $addon->i18n('version') . '">' . rex_escape($file['version']) . '</td>
                <td data-title="REDAXO">' . rex_escape(implode(', ', $file['redaxo_versions'])) . '</td>
                <td data-title="' . $addon->i18n('description') . '">' . nl2br(rex_escape($file['description'])) . '</td>
                <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-edit"></i> ' . $addon->i18n('file_edit') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '"><i class="rex-icon rex-icon-' . $status . '"></i> ' . $addon->i18n($status) . '</span></td>
            </tr>';
        }

        $panel .= '</tbody></table>';

        $fragment = new rex_fragment();
        $fragment->setVar('title', $addon->i18n('files'), false);
        $fragment->setVar('content', $panel, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;

        echo '<a class="btn btn-back" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('back') . '</a>';
    }
} else {
    $panel = '
        <table class="table table-striped table-hover">
         <thead>
            <tr>
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $addon->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $addon->i18n('key') . '</th>
                <th>' . $addon->i18n('name') . '</th>
                <th class="rex-table-action" colspan="2">' . $addon->i18n('status') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        $url = rex_url::currentBackendPage(['addonkey' => $key]);
        $status = $addon['status'] ? 'online' : 'offline';
        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $addon->i18n('key') . '">' . rex_escape($key) . '</td>
                <td data-title="' . $addon->i18n('name') . '">' . rex_escape($addon['name']) . '</td>
                <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '">' . $addon->i18n($status) . '</span></td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('my_packages'), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
