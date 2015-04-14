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
        <table id="rex-table-install-packages-information" class="table">
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



    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $this->i18n('information'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

    $content = '
        <table id="rex-table-install-packages-files" class="table">
            <thead>
            <tr>
                <th></th>
                <th>' . $this->i18n('version') . '</th>
                <th>' . $this->i18n('description') . '</th>
                <th>' . $this->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $content .= '
            <tr>
                <td><i class="rex-icon rex-icon-package"></i></td>
                <td>' . $file['version'] . '</td>
                <td>' . nl2br($file['description']) . '</td>
                <td><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'rex-api-call' => 'install_package_add', 'file' => $fileId]) . '"><i class="rex-icon rex-icon-download"></i> ' . $this->i18n('download') . '</a></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('files'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;


} else {

    $toolbar = '
    <div class="navbar-form">
        <div class="form-group">
            <div class="input-group" id="rex-js-install-addon-search">
                <span class="input-group-addon"><i class="rex-icon rex-icon-search"></i></span>
                <input class="form-control" type="text" placeholder="' . $this->i18n('search') . '" />
                <span class="input-group-btn"><button class="btn btn-default">' . $this->i18n('clear') . '</button></span>
            </div>
        </div>
    </div>
    ';
    echo rex_view::toolbar($toolbar, '', 'rex-navbar-flexible');


    $content = '
        <table id="rex-js-table-install-packages-addons" class="table table-striped table-hover">
         <thead>
            <tr>
                <th><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $this->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $this->i18n('key') . '</th>
                <th>' . $this->i18n('name') . ' / ' . $this->i18n('author') . '</th>
                <th>' . $this->i18n('shortdescription') . '</th>
                <th>' . $this->i18n('header_function') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        if (rex_addon::exists($key)) {
            $content .= '
                <tr>
                    <td><i class="rex-icon rex-icon-package"></i></td>
                    <td>' . $key . '</td>
                    <td><b>' . $addon['name'] . '</b><br /><span class="text-muted">' . $addon['author'] . '</span></td>
                    <td>' . nl2br($addon['shortdescription']) . '</td>
                    <td><span class="text-nowrap"><i class="rex-icon rex-icon-package-exists"></i> ' . $this->i18n('addon_already_exists') . '</span></td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr>
                    <td><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                    <td><a href="' . $url . '">' . $key . '</a></td>
                    <td><b>' . $addon['name'] . '</b><br /><span class="text-muted">' . $addon['author'] . '</span></td>
                    <td>' . nl2br($addon['shortdescription']) . '</td>
                    <td><a href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
                </tr>';
        }
    }

    $content .= '</tbody></table>';

    $content .= '
        <script type="text/javascript">
        <!--
        jQuery(function($) {
            var table = $("#rex-js-table-install-packages-addons");
            $("#rex-js-install-addon-search .form-control").keyup(function () {
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
            $("#rex-js-install-addon-search .btn").click(function () {
                $("#rex-js-install-addon-search .form-control").val("").trigger("keyup");
            });
        });
        //-->
        </script>
    ';



    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('addons_found', count($addons)), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

}
