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
        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $this->i18n('name') . '</th>
                <td data-title="' . $this->i18n('name') . '">' . htmlspecialchars($addon['name']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('author') . '</th>
                <td data-title="' . $this->i18n('author') . '">' . htmlspecialchars($addon['author']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('shortdescription') . '</th>
                <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br(htmlspecialchars($addon['shortdescription'])) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('description') . '</th>
                <td data-title="' . $this->i18n('description') . '">' . nl2br(htmlspecialchars($addon['description'])) . '</td>
            </tr>
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $this->i18n('information'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

    $content = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-3">' . $this->i18n('version') . '</th>
                <th class="rex-table-width-3"><span class="text-nowrap">' . $this->i18n('published_on') . '</span></th>
                <th>' . $this->i18n('description') . '</th>
                <th class="rex-table-action">' . $this->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $file['description'] = trim($file['description']) == '' ? '&nbsp;' : htmlspecialchars($file['description']);

        $content .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                <td data-title="' . $this->i18n('version') . '">' . htmlspecialchars($file['version']) . '</td>
                <td data-title="' . $this->i18n('published_on') . '">' . htmlspecialchars(rex_formatter::strftime($file['created'])) . '</td>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($file['description']) . '</td>
                <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_add::getUrlParams()) . '" data-pjax="false"><i class="rex-icon rex-icon-download"></i> ' . $this->i18n('download') . '</a></td>
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
        <div class="form-group form-group-xs">
            <div class="input-group input-group-xs" id="rex-js-install-addon-search">
                <input class="form-control" type="text" autofocus placeholder="' . $this->i18n('search') . '" />
                <span class="input-group-btn"><button class="btn btn-default">' . $this->i18n('clear') . '</button></span>
            </div>
        </div>
    ';

    $sort = rex_request('sort', 'string', '');
    if ($sort === 'up') {
        $sortClass = '-up';
        $sortNext = 'down';
        uasort($addons, function ($addon1, $addon2) {
            return reset($addon1['files'])['created'] > reset($addon2['files'])['created'];
        });
    } elseif ($sort === 'down') {
        $sortClass = '-down';
        $sortNext = '';
        uasort($addons, function ($addon1, $addon2) {
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
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $this->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th><a href="'.rex_url::currentBackendPage().'" title="'.$this->i18n('sort_default').'">' . $this->i18n('key') . '</a></th>
                <th>' . $this->i18n('name') . ' / ' . $this->i18n('author') . '</th>
                <th class="rex-table-min-width-3"><a href="'.rex_url::currentBackendPage(['sort' => $sortNext]).'" title="' . $this->i18n('sort') . '"><span class="text-nowrap">' . $this->i18n('published_on') . '</span>&nbsp;<span><i class="rex-icon rex-icon-sort fa-sort'.$sortClass.'"></i></span></a></th>
                <th>' . $this->i18n('shortdescription') . '</th>
                <th class="rex-table-action">' . $this->i18n('header_function') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        if (rex_addon::exists($key)) {
            $content .= '
                <tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                    <td data-title="' . $this->i18n('key') . '">' . $key . '</td>
                    <td data-title="' . $this->i18n('name') . '"><b>' . $addon['name'] . '</b><br /><span class="text-muted">' . htmlspecialchars($addon['author']) . '</span></td>
                    <td data-title="' . $this->i18n('published_on') . '">' . htmlspecialchars(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
                    <td class="rex-table-action"><span class="text-nowrap"><i class="rex-icon rex-icon-package-exists"></i> ' . $this->i18n('addon_already_exists') . '</span></td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr>
                    <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                    <td data-title="' . $this->i18n('key') . '"><a href="' . $url . '">' . htmlspecialchars($key) . '</a></td>
                    <td data-title="' . $this->i18n('name') . '"><b>' . htmlspecialchars($addon['name']) . '</b><br /><span class="text-muted">' . $addon['author'] . '</span></td>
                    <td data-title="' . $this->i18n('published_on') . '">' . htmlspecialchars(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br(htmlspecialchars($addon['shortdescription'])) . '</td>
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
    $fragment->setVar('title', $this->i18n('addons_found', count($addons)), false);
    $fragment->setVar('options', $toolbar, false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
