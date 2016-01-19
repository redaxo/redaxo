<?php

/** @var rex_addon $this */

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
        $file = $new ? ['version' => '', 'description' => '', 'status' => 1, 'redaxo_versions' => ['5.0.x']] : $addon['files'][$file_id];

        $newVersion = rex_addon::get($addonkey)->getVersion();

        $redaxo_select = new rex_select();
        $redaxo_select->setName('upload[redaxo][]');
        $redaxo_select->setId('rex-install-packages-upload-redaxo');
        $redaxo_select->setAttribute('class', 'form-control');
        $redaxo_select->setSize(4);
        $redaxo_select->setMultiple(true);
        $redaxo_select->addOption('5.0.x', '5.0.x');
        $redaxo_select->setSelected($file['redaxo_versions']);

        $uploadCheckboxDisabled = '';
        $hiddenField = '';
        if ($new || !rex_addon::exists($addonkey)) {
            $uploadCheckboxDisabled = ' disabled="disabled"';
            $hiddenField = '<input type="hidden" name="upload[upload_file]" value="' . ((integer) $new) . '" />';
        }

        $panel = '<fieldset>';

        $formElements = [];

        $n = [];
        $n['label'] = '<label for="rex-js-install-packages-upload-version">' . $this->i18n('version') . '</label>';
        $n['field'] = '<p class="form-control-static" id="rex-js-install-packages-upload-version">' . ($new ? $newVersion : $file['version']) . '</p>
                           <input type="hidden" name="upload[oldversion]" value="' . $file['version'] . '" />';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="rex-install-packages-upload-redaxo">REDAXO</label>';
        $n['field'] = $redaxo_select->get();
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="rex-install-packages-upload-description">' . $this->i18n('description') . '</label>';
        $n['field'] = '<textarea class="form-control" id="rex-install-packages-upload-description" name="upload[description]" rows="15">' . $file['description'] . '</textarea>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-install-packages-upload-status">' . $this->i18n('online') . '</label>';
        $n['field'] = '<input id="rex-install-packages-upload-status" type="checkbox" name="upload[status]" value="1" ' . (!$new && $file['status'] ? 'checked="checked" ' : '') . '/>';
        $formElements[] = $n;

        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label for="rex-js-install-packages-upload-upload-file">' . $this->i18n('upload_file') . '</label>' . $hiddenField;
        $n['field'] = '<input id="rex-js-install-packages-upload-upload-file" type="checkbox" name="upload[upload_file]" value="1" ' . ($new ? 'checked="checked" ' : '') . $uploadCheckboxDisabled . '/>';
        $formElements[] = $n;

        if (rex_addon::get($addonkey)->isInstalled() && is_dir(rex_url::addonAssets($addonkey))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-replace-assets">' . $this->i18n('replace_assets') . '</label>';
            $n['field'] = '<input id="rex-js-install-packages-upload-replace-assets" type="checkbox" name="upload[replace_assets]" value="1" ' . ($new ? '' : 'disabled="disabled" ') . '/>';
            $formElements[] = $n;
        }

        if (is_dir(rex_path::addon($addonkey, 'tests'))) {
            $n = [];
            $n['reverse'] = true;
            $n['label'] = '<label for="rex-js-install-packages-upload-ignore-tests">' . $this->i18n('ignore_tests') . '</label>';
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
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="upload[send]" value="' . $this->i18n('send') . '">' . $this->i18n('send') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-delete" value="' . $this->i18n('delete') . '" onclick="if(confirm(\'' . $this->i18n('delete') . ' ?\')) location.href=\'' . rex_url::currentBackendPage(['rex-api-call' => 'install_package_delete', 'addonkey' => $addonkey, 'file' => $file_id]) . '\';">' . $this->i18n('delete') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $panel .= '</fieldset>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $addonkey . ' <small>' . $this->i18n($new ? 'file_add' : 'file_edit') . '</small>', false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content = $fragment->parse('core/page/section.php');

        $content = '
            <form action="' . rex_url::currentBackendPage(['rex-api-call' => 'install_package_upload', 'addonkey' => $addonkey, 'file' => $file_id]) . '" method="post">
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
                    ' . ($newVersion != $file['version'] ? '$("#rex-js-install-packages-upload-version").html("<del>' . $file['version'] . '</del> <ins>' . $newVersion . '</ins>");' : '') . '
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
            $icon = '<a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => 'new']) . '" title="' . $this->i18n('file_add') . '"><i class="rex-icon rex-icon-add-package"></i></a>';
        }

        $panel = '

        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $this->i18n('name') . '</th>
                <td data-title="' . $this->i18n('name') . '">' . $addon['name'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('author') . '</th>
                <td data-title="' . $this->i18n('author') . '">' . $addon['author'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('shortdescription') . '</th>
                <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('description') . '</th>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($addon['description']) . '</td>
            </tr>
            </tbody>
        </table>';

        $fragment = new rex_fragment();
        $fragment->setVar('title', $addonkey . ' <small>' . $this->i18n('information') . '</small>', false);
        $fragment->setVar('content', $panel, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;

        $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon">' . $icon . '</th>
                <th class="rex-table-width-4">' . $this->i18n('version') . '</th>
                <th>REDAXO</th>
                <th>' . $this->i18n('description') . '</th>
                <th class="rex-table-action" colspan="2">' . $this->i18n('status') . '</th>
            </tr>
            </thead>
            <tbody>';

        foreach ($addon['files'] as $fileId => $file) {
            $url = rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId]);
            $status = $file['status'] ? 'online' : 'offline';
            $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $this->i18n('version') . '">' . $file['version'] . '</td>
                <td data-title="REDAXO">' . implode(', ', $file['redaxo_versions']) . '</td>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($file['description']) . '</td>
                <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-edit"></i> ' . $this->i18n('file_edit') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '"><i class="rex-icon rex-icon-' . $status . '"></i> ' . $this->i18n($status) . '</span></td>
            </tr>';
        }

        $panel .= '</tbody></table>';

        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('files'), false);
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
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $this->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $this->i18n('key') . '</th>
                <th>' . $this->i18n('name') . '</th>
                <th class="rex-table-action" colspan="2">' . $this->i18n('status') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        $url = rex_url::currentBackendPage(['addonkey' => $key]);
        $status = $addon['status'] ? 'online' : 'offline';
        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $this->i18n('key') . '">' . $key . '</td>
                <td data-title="' . $this->i18n('name') . '">' . $addon['name'] . '</td>
                <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
                <td class="rex-table-action"><span class="rex-text-' . $status . '">' . $this->i18n($status) . '</span></td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('my_packages'), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
