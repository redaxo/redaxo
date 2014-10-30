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

        $redaxo_select = new rex_select;
        $redaxo_select->setName('upload[redaxo][]');
        $redaxo_select->setId('install-packages-upload-redaxo');
        $redaxo_select->setAttribute('class', 'rex-form-select');
        $redaxo_select->setSize(4);
        $redaxo_select->setMultiple(true);
        $redaxo_select->addOption('5.0.x', '5.0.x');
        $redaxo_select->addOption('4.3.x', '4.3.x');
        $redaxo_select->addOption('4.2.x', '4.2.x');
        $redaxo_select->addOption('4.1.x', '4.1.x');
        $redaxo_select->addOption('4.0.x', '4.0.x');
        $redaxo_select->addOption('3.2.x', '3.2.x');
        $redaxo_select->setSelected($file['redaxo_versions']);

        $uploadCheckboxDisabled = '';
        $hiddenField = '';
        if ($new || !rex_addon::exists($addonkey)) {
            $uploadCheckboxDisabled = ' disabled="disabled"';
            $hiddenField = '<input type="hidden" name="upload[upload_file]" value="' . ((integer) $new) . '" />';
        }

        $content = '
        <h2><b>' . $addonkey . '</b> ' . $this->i18n($new ? 'file_add' : 'file_edit') . '</h2>

        <div class="rex-form">
            <form action="' . rex_url::currentBackendPage(['rex-api-call' => 'install_package_upload', 'addonkey' => $addonkey, 'file' => $file_id]) . '" method="post">
                <fieldset>';


                    $formElements = [];

                        $n = [];
                        $n['label'] = '<label for="install-packages-upload-version">' . $this->i18n('version') . '</label>';
                        $n['field'] = '<span id="install-packages-upload-version" class="rex-form-read">' . ($new ? $newVersion : $file['version']) . '</span>
                                       <input type="hidden" name="upload[oldversion]" value="' . $file['version'] . '" />';
                        $formElements[] = $n;

                        $n = [];
                        $n['label'] = '<label for="install-packages-upload-redaxo">REDAXO</label>';
                        $n['field'] = $redaxo_select->get();
                        $formElements[] = $n;

                        $n = [];
                        $n['label'] = '<label for="install-packages-upload-description">' . $this->i18n('description') . '</label>';
                        $n['field'] = '<textarea id="install-packages-upload-description" name="upload[description]" cols="50" rows="15">' . $file['description'] . '</textarea>';
                        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');



        $formElements = [];

                        $n = [];
                        $n['reverse'] = true;
                        $n['label'] = '<label for="install-packages-upload-status">' . $this->i18n('online') . '</label>';
                        $n['field'] = '<input id="install-packages-upload-status" type="checkbox" name="upload[status]" value="1" ' . (!$new && $file['status'] ? 'checked="checked" ' : '') . '/>';
                        $formElements[] = $n;

                        $n = [];
                        $n['reverse'] = true;
                        $n['label'] = '<label for="install-packages-upload-upload-file">' . $this->i18n('upload_file') . '</label>' . $hiddenField;
                        $n['field'] = '<input id="install-packages-upload-upload-file" type="checkbox" name="upload[upload_file]" value="1" ' . ($new ? 'checked="checked" ' : '') . $uploadCheckboxDisabled . '/>';
                        $formElements[] = $n;

                        if (rex_addon::get($addonkey)->isInstalled() && is_dir(rex_url::addonAssets($addonkey))) {
                            $n = [];
                            $n['reverse'] = true;
                            $n['label'] = '<label for="install-packages-upload-replace-assets">' . $this->i18n('replace_assets') . '</label>';
                            $n['field'] = '<input id="install-packages-upload-replace-assets" type="checkbox" name="upload[replace_assets]" value="1" ' . ($new ? '' : 'disabled="disabled" ') . '/>';
                            $formElements[] = $n;
                        }

                        if (is_dir(rex_path::addon($addonkey, 'tests'))) {
                            $n = [];
                            $n['reverse'] = true;
                            $n['label'] = '<label for="install-packages-upload-ignore-tests">' . $this->i18n('ignore_tests') . '</label>';
                            $n['field'] = '<input id="install-packages-upload-ignore-tests" type="checkbox" name="upload[ignore_tests]" value="1" checked="checked"' . ($new ? '' : 'disabled="disabled" ') . '/>';
                            $formElements[] = $n;
                        }

                    $fragment = new rex_fragment();
                    $fragment->setVar('elements', $formElements, false);
                    $content .= $fragment->parse('core/form/checkbox.php');

        $content .= '</fieldset>';


        $formElements = [];

        $n = [];
        $n['field'] = '<a class="rex-back" href="' . rex_url::currentBackendPage() . '"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="rex-button rex-button-primary" id="install-packages-upload-send" type="submit" name="upload[send]" value="' . $this->i18n('send') . '">' . $this->i18n('send') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="rex-button rex-danger" id="install-packages-delete" value="' . $this->i18n('delete') . '" onclick="if(confirm(\'' . $this->i18n('delete') . ' ?\')) location.href=\'' . rex_url::currentBackendPage(['rex-api-call' => 'install_package_delete', 'addonkey' => $addonkey, 'file' => $file_id]) . '\';">' . $this->i18n('delete') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/submit.php');

        $content .= '
                </fieldset>
            </form>
        </div>';



        echo rex_view::content('block', $content, '', $params = ['flush' => true]);

        if (!$new) {
            echo '
    <script type="text/javascript"><!--

        jQuery(function($) {
            $("#install-packages-upload-upload-file").change(function(){
                if($(this).is(":checked"))
                {
                    ' . ($newVersion != $file['version'] ? '$("#install-packages-upload-version").html("<span class=\'rex-strike\'>' . $file['version'] . '</span> <strong>' . $newVersion . '</strong>");' : '') . '
                    $("#install-packages-upload-replace-assets, #install-packages-upload-ignore-tests").removeAttr("disabled");
                }
                else
                {
                    $("#install-packages-upload-version").html("' . $file['version'] . '");
                    $("#install-packages-upload-replace-assets, #install-packages-upload-ignore-tests").attr("disabled", "disabled");
                }
            });
        });

    //--></script>';
        }

    } else {
        $icon = '';
        if (rex_addon::exists($addonkey)) {
            $icon = '<a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => 'new']) . '" title="' . $this->i18n('file_add') . '"><span class="rex-icon rex-icon-add-package"></span></a>';
        }

        $content = '
        <h2><b>' . $addonkey . '</b> ' . $this->i18n('information') . '</h2>

        <table class="rex-table">
            <tbody>
            <tr>
                <th>' . $this->i18n('name') . '</th>
                <td>' . $addon['name'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('author') . '</th>
                <td>' . $addon['author'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('shortdescription') . '</th>
                <td>' . nl2br($addon['shortdescription']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('description') . '</th>
                <td>' . nl2br($addon['description']) . '</td>
            </tr>
            </tbody>
        </table>';

        echo rex_view::content('block', $content, '', $params = ['flush' => true]);

        $content = '
        <h2>' . $this->i18n('files') . '</h2>
        <table class="rex-table">
            <thead>
            <tr>
                <th class="rex-slim">' . $icon . '</th>
                <th class="rex-version">' . $this->i18n('version') . '</th>
                <th>REDAXO</th>
                <th class="rex-description">' . $this->i18n('description') . '</th>
                <th colspan="2" class="rex-function">' . $this->i18n('status') . '</th>
            </tr>
            </thead>
            <tbody>';

        foreach ($addon['files'] as $fileId => $file) {
            $url = rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId]);
            $status = $file['status'] ? 'online' : 'offline';
            $content .= '
            <tr>
                <td class="rex-slim"><a href="' . $url . '"><span class="rex-icon rex-icon-package"></span></a></td>
                <td class="rex-version">' . $file['version'] . '</td>
                <td class="rex-version">' . implode(', ', $file['redaxo_versions']) . '</td>
                <td class="rex-description">' . nl2br($file['description']) . '</td>
                <td class="rex-edit"><a class="rex-link rex-edit" href="' . $url . '">' . $this->i18n('file_edit') . '</a></td>
                <td class="rex-status"><span class="rex-status rex-' . $status . '">' . $this->i18n($status) . '</span></td>
            </tr>';
        }

        $content .= '</tbody></table>';

        echo rex_view::content('block', $content, '', $params = ['flush' => true]);

        echo '<a class="rex-back" href="' . rex_url::currentBackendPage() . '"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('back') . '</a>';

    }

} else {

    $content = '
        <h2>' . $this->i18n('my_packages') . '</h2>
        <table class="rex-table">
         <thead>
            <tr>
                <th class="rex-slim"></th>
                <th class="rex-key">' . $this->i18n('key') . '</th>
                <th class="rex-name">' . $this->i18n('name') . '</th>
                <th colspan="2" class="rex-function">' . $this->i18n('status') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        $url = rex_url::currentBackendPage(['addonkey' => $key]);
        $status = $addon['status'] ? 'online' : 'offline';
        $content .= '
            <tr>
                <td class="rex-slime"><a href="' . $url . '"><span class="rex-icon rex-icon-package"></span></a></td>
                <td class="rex-key">' . $key . '</td>
                <td class="rex-name">' . $addon['name'] . '</td>
                <td class="rex-view"><a href="' . $url . '" class="rex-link rex-view">' . rex_i18n::msg('view') . '</a></td>
                <td class="rex-status"><span class="rex-status rex-' . $status . '">' . $this->i18n($status) . '</span></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    echo rex_view::content('block', $content, '', $params = ['flush' => true]);

}
