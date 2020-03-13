<?php

assert(isset($markdown) && is_callable($markdown));

$package = rex_addon::get('install');

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
        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $package->i18n('name') . '</th>
                <td data-title="' . $package->i18n('name') . '">' . rex_escape($addon['name']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('author') . '</th>
                <td data-title="' . $package->i18n('author') . '">' . rex_escape($addon['author']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('shortdescription') . '</th>
                <td data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('description') . '</th>
                <td data-title="' . $package->i18n('description') . '">' . nl2br(rex_escape($addon['description'])) . '</td>
            </tr>
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $package->i18n('information'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

    $content = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-3">' . $package->i18n('version') . '</th>
                <th class="rex-table-width-3"><span class="text-nowrap">' . $package->i18n('published_on') . '</span></th>
                <th>' . $package->i18n('description') . '</th>
                <th class="rex-table-action">' . $package->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $content .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                <td data-title="' . $package->i18n('version') . '">' . rex_escape($file['version']) . '</td>
                <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime($file['created'])) . '</td>
                <td data-title="' . $package->i18n('description') . '">' . $markdown($file['description']) . '</td>
                <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_add::getUrlParams()) . '" data-pjax="false"><i class="rex-icon rex-icon-download"></i> ' . $package->i18n('download') . '</a></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $package->i18n('files'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
} else {
    $toolbar = '
        <div class="form-group form-group-xs">
            <div class="input-group input-group-xs" id="rex-js-install-addon-search">
                <input class="form-control" type="text" autofocus placeholder="' . $package->i18n('search') . '" />
                <span class="input-group-btn"><button class="btn btn-default">' . $package->i18n('clear') . '</button></span>
            </div>
        </div>
    ';

    $sort = rex_request('sort', 'string', '');
    if ('up' === $sort) {
        $sortClass = '-up';
        $sortNext = 'down';
        uasort($addons, static function ($addon1, $addon2) {
            return reset($addon1['files'])['created'] > reset($addon2['files'])['created'];
        });
    } elseif ('down' === $sort) {
        $sortClass = '-down';
        $sortNext = '';
        uasort($addons, static function ($addon1, $addon2) {
            return reset($addon1['files'])['created'] < reset($addon2['files'])['created'];
        });
    } else {
        $sortClass = '';
        $sortNext = 'up';
    }

    $content = '
        <table class="table table-striped table-hover" id="rex-js-table-install-packages-addons">
         <thead>
            <tr>
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $package->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th><a href="'.rex_url::currentBackendPage().'" title="'.$package->i18n('sort_default').'">' . $package->i18n('key') . '</a></th>
                <th>' . $package->i18n('name') . ' / ' . $package->i18n('author') . '</th>
                <th class="rex-table-min-width-3"><a href="'.rex_url::currentBackendPage(['sort' => $sortNext]).'" title="' . $package->i18n('sort') . '"><span class="text-nowrap">' . $package->i18n('published_on') . '</span>&nbsp;<span><i class="rex-icon rex-icon-sort fa-sort'.$sortClass.'"></i></span></a></th>
                <th>' . $package->i18n('shortdescription') . '</th>
                <th class="rex-table-action">' . $package->i18n('header_function') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        if (rex_addon::exists($key)) {
            $content .= '
                <tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                    <td data-title="' . $package->i18n('key') . '">' . rex_escape($key) . '</td>
                    <td data-title="' . $package->i18n('name') . '"><b>' . rex_escape($addon['name']) . '</b><br /><span class="text-muted">' . rex_escape($addon['author']) . '</span></td>
                    <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
                    <td class="rex-table-action"><span class="text-nowrap"><i class="rex-icon rex-icon-package-exists"></i> ' . $package->i18n('addon_already_exists') . '</span></td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr data-pjax-scroll-to="0">
                    <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                    <td data-title="' . $package->i18n('key') . '"><a href="' . $url . '">' . rex_escape($key) . '</a></td>
                    <td data-title="' . $package->i18n('name') . '"><b>' . rex_escape($addon['name']) . '</b><br /><span class="text-muted">' . rex_escape($addon['author']) . '</span></td>
                    <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
                    <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
                </tr>';
        }
    }

    $content .= '</tbody></table>';

    $content .= '
        <script type="text/javascript">
        <!--
        jQuery(function($) {
            var table = $("#rex-js-table-install-packages-addons");
            var tablebody = table.find("tbody");
            var replaceNumber = function replaceNumber() {
                table.prev().find(".panel-title").text(
                function(i,txt) {
                    return txt.replace(/\d+/, tablebody.find("tr").filter(":visible").length);
                });
            };
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
                    replaceNumber();
                }
                else
                {
                    replaceNumber();
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
    $fragment->setVar('title', $package->i18n('addons_found', count($addons)), false);
    $fragment->setVar('options', $toolbar, false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
