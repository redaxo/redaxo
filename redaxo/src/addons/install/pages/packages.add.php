<?php

/** @var rex_addon $this */

$addonkey = rex_request('addonkey', 'string');
$addons = [];

echo rex_api_function::getMessage();

try {
    $addons = rex_install_packages::getAddPackages();
} catch (rex_functional_exception $e) {
    echo rex_view::warning($e->getMessage());
    $addonkey = '';
}

if ($addonkey && isset($addons[$addonkey]) && !rex_addon::exists($addonkey)) {
    $addon = $addons[$addonkey];

    $content = '
        <h2><b>' . $addonkey . '</b> ' . $this->i18n('information') . '</h2>

        <table id="rex-table-install-packages-information" class="table">
            <tbody>
            <tr>
                <th class="rex-term">' . $this->i18n('name') . '</th>
                <td class="rex-description">' . $addon['name'] . '</td>
            </tr>
            <tr>
                <th class="rex-term">' . $this->i18n('author') . '</th>
                <td class="rex-description">' . $addon['author'] . '</td>
            </tr>
            <tr>
                <th class="rex-term">' . $this->i18n('shortdescription') . '</th>
                <td class="rex-description">' . nl2br($addon['shortdescription']) . '</td>
            </tr>
            <tr>
                <th class="rex-term">' . $this->i18n('description') . '</th>
                <td class="rex-description">' . nl2br($addon['description']) . '</td>
            </tr>
            </tbody>
        </table>';


    echo rex_view::content('block', $content, '', $params = ['flush' => true]);

    $content = '
        <h2>' . $this->i18n('files') . '</h2>
        <table id="rex-table-install-packages-files" class="table">
            <thead>
            <tr>
                <th class="rex-slim"></th>
                <th class="rex-version">' . $this->i18n('version') . '</th>
                <th class="rex-description">' . $this->i18n('description') . '</th>
                <th class="rex-function">' . $this->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $content .= '
            <tr>
                <td class="rex-slim"><span class="rex-icon rex-icon-package"></span></td>
                <td class="rex-version">' . $file['version'] . '</td>
                <td class="rex-description">' . nl2br($file['description']) . '</td>
                <td class="rex-function"><a class="rex-link rex-download" href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'rex-api-call' => 'install_package_add', 'file' => $fileId]) . '">' . $this->i18n('download') . '</a></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    echo rex_view::content('block', $content, '', $params = ['flush' => true]);


} else {

    echo rex_view::content('block', '<input type="text" id="rex-install-addon-search" class="rex-form-text" placeholder="Suchen…" style="width: 300px"/>');

    $content = '
        <h2>' . $this->i18n('addons_found', count($addons)) . '</h2>
        <table id="rex-table-install-packages-addons" class="table table-striped">
         <thead>
            <tr>
                <th class="rex-slim"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '">' . $this->i18n('reload') . '</a></th>
                <th class="rex-key">' . $this->i18n('key') . '</th>
                <th class="rex-name rex-author">' . $this->i18n('name') . ' / ' . $this->i18n('author') . '</th>
                <th class="rex-shortdescription">' . $this->i18n('shortdescription') . '</th>
                <th class="rex-function">' . $this->i18n('header_function') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        if (rex_addon::exists($key)) {
            $content .= '
                <tr>
                    <td class="rex-slim"></td>
                    <td class="rex-key">' . $key . '</td>
                    <td class="rex-name rex-author"><span class="rex-name">' . $addon['name'] . '</span><span class="rex-author">' . $addon['author'] . '</span></td>
                    <td class="rex-shortdescription">' . nl2br($addon['shortdescription']) . '</td>
                    <td class="rex-view">' . $this->i18n('addon_already_exists') . '</td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr>
                    <td class="rex-slim"><a href="' . $url . '"><span class="rex-icon rex-icon-package"></span></a></td>
                    <td class="rex-key"><a href="' . $url . '">' . $key . '</a></td>
                    <td class="rex-name rex-author"><span class="rex-name">' . $addon['name'] . '</span><span class="rex-author">' . $addon['author'] . '</span></td>
                    <td class="rex-shortdescription">' . nl2br($addon['shortdescription']) . '</td>
                    <td class="rex-view"><a href="' . $url . '" class="rex-link rex-view">' . rex_i18n::msg('view') . '</a></td>
                </tr>';
        }
    }

    $content .= '</tbody></table>';

    $content .= '
        <script type="text/javascript">
        <!--
        jQuery(function($) {
            var table = $("#rex-table-install-packages-addons");
            $("#rex-install-addon-search").keyup(function () {
                table.find("tr").show();
                var search = $(this).val().toLowerCase();
                if (search) {
                    table.find("tbody tr").each(function () {
                        var tr = $(this);
                        if (tr.text().toLowerCase().indexOf(search) < 0) {
                            tr.hide();
                        }
                    });
                }
            });
        });
        //-->
        </script>
    ';


    echo rex_view::content('block', $content, '', $params = ['flush' => true]);

}
