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
            </tr>';

    if ($addon['website']) {
        $content .= '
            <tr>
                <th>' . $package->i18n('website') . '</th>
                <td data-title="' . $package->i18n('website') . '"><a class="rex-link-expanded" href="' . rex_escape($addon['website']) . '">' . rex_escape($addon['website']) . '</a></td>
            </tr>';
    }

    $content .= '
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . rex_escape($addonkey) . '</b> ' . $package->i18n('information'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

    $content = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-4">' . $package->i18n('version') . '</th>
                <th class="rex-table-width-4"><span class="text-nowrap">' . $package->i18n('published_on') . '</span></th>
                <th>' . $package->i18n('description') . '</th>
                <th class="rex-table-action">' . $package->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    $latestRelease = false;
    foreach ($addon['files'] as $fileId => $file) {
        $confirm = $releaseLabel = '';
        $packageIcon = '<i class="rex-icon rex-icon-package"></i>';
        $version = rex_escape($file['version']);
        $description = $markdown($file['description']);

        if (rex_version::isUnstable($version)) {
            $releaseLabel = '<br><span class="label label-warning" title="'. rex_i18n::msg('unstable_version') .'">'.rex_i18n::msg('unstable_version').'</span> ';
            $confirm = ' data-confirm="'.rex_i18n::msg('install_download_unstable').'"';
            $packageIcon = '<i class="rex-icon rex-icon-unstable-version"></i>';
        } elseif (!$latestRelease) {
            $releaseLabel = '<br><span class="label label-success">'.rex_i18n::msg('install_latest_release').'</span>';
            $latestRelease = true;
        }

        $content .= '
            <tr>
                <td class="rex-table-icon">'.$packageIcon.'</td>
                <td data-title="' . $package->i18n('version') .'">' . $version . $releaseLabel .'</td>
                <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime($file['created'])) . '</td>
                <td class="rex-word-break" data-title="' . $package->i18n('description') . '">' . $description . '</td>
                <td class="rex-table-action"><a class="rex-link-expanded"'.$confirm.' href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_add::getUrlParams()) . '" data-pjax="false"><i class="rex-icon rex-icon-download"></i> ' . $package->i18n('download') . '</a></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $package->i18n('files'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
} else {
    $fragment = new rex_fragment();
    $fragment->setVar('id', 'rex-js-install-addon-search');
    $fragment->setVar('autofocus', true);
    $toolbar = $fragment->parse('core/form/search.php');

    $sort = rex_request('sort', 'string', '');
    if ('up' === $sort) {
        $sortClass = '-up';
        $sortNext = 'down';
        uasort($addons, static function ($addon1, $addon2) {
            return reset($addon1['files'])['created'] <=> reset($addon2['files'])['created'];
        });
    } elseif ('down' === $sort) {
        $sortClass = '-down';
        $sortNext = '';
        uasort($addons, static function ($addon1, $addon2) {
            return reset($addon2['files'])['created'] <=> reset($addon1['files'])['created'];
        });
    } else {
        $sortClass = '';
        $sortNext = 'up';
    }

    $content = '
        <table class="table table-striped table-hover" id="rex-js-table-install-packages-addons">
         <thead>
            <tr>
                <th class="rex-table-icon"><a class="rex-link-expanded" href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $package->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th class="rex-table-min-width-4 rex-table-sort"><a class="rex-link-expanded" href="'.rex_url::currentBackendPage().'" title="'.$package->i18n('sort_default').'">' . $package->i18n('key') . '</a></th>
                <th class="rex-table-min-width-4">' . $package->i18n('name') . ' / ' . $package->i18n('author') . '</th>
                <th class="rex-table-min-width-4 rex-table-sort"><a class="rex-link-expanded" href="'.rex_url::currentBackendPage(['sort' => $sortNext]).'" title="' . $package->i18n('sort') . '"><span class="text-nowrap">' . $package->i18n('published_on') . '</span>&nbsp;<span><i class="rex-icon rex-icon-sort fa-sort'.$sortClass.'"></i></span></a></th>
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
                    <td class="rex-word-break" data-title="' . $package->i18n('key') . '">' . rex_escape($key) . '</td>
                    <td class="rex-word-break" data-title="' . $package->i18n('name') . '"><b>' . rex_escape($addon['name']) . '</b><br /><span class="text-muted">' . rex_escape($addon['author']) . '</span></td>
                    <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td class="rex-word-break" data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
                    <td class="rex-table-action"><span class="text-nowrap"><i class="rex-icon rex-icon-package-exists"></i> ' . $package->i18n('addon_already_exists') . '</span></td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr data-pjax-scroll-to="0">
                    <td class="rex-table-icon"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                    <td class="rex-word-break" data-title="' . $package->i18n('key') . '"><a class="rex-link-expanded" href="' . $url . '">' . rex_escape($key) . '</a></td>
                    <td class="rex-word-break" data-title="' . $package->i18n('name') . '"><b>' . rex_escape($addon['name']) . '</b><br /><span class="text-muted">' . rex_escape($addon['author']) . '</span></td>
                    <td data-title="' . $package->i18n('published_on') . '">' . rex_escape(rex_formatter::strftime(reset($addon['files'])['created'])) . '</td>
                    <td class="rex-word-break" data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
                    <td class="rex-table-action"><a class="rex-link-expanded" href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
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
            var rows = tablebody.find("tr");
            var replaceNumber = function replaceNumber() {
                table.prev().find(".panel-title").text(
                function(i,txt) {
                    return txt.replace(/\d+/, rows.filter(":visible").length);
                });
            };
            /** @see https://www.npmjs.com/package/okapibm25 */
            var BM25 = function BM25(documents, keywords, constants) {
                /** Gets word count. */
                var getWordCount = function (corpus) {
                    return ((corpus || "").match(/\w+/g) || []).length;
                };
                /** Number of occurences of a word in a string. */
                var getTermFrequency = function (term, corpus) {
                    return ((corpus || "").match(new RegExp(term,"g")) || []).length;
                };
                /** Inverse document frequency. */
                var getIDF = function (term, documents) {
                    // Number of relevant documents.
                    var relevantDocuments = documents.filter(function (document) {
                        return document.includes(term);
                    }).length;
                    return Math.log((documents.length - relevantDocuments + 0.5) / (relevantDocuments + 0.5) + 1);
                };
                /** Implementation of Okapi BM25 algorithm.
                 *  @param documents: string[]. Collection of documents.
                 *  @param keywords: keywords within query.
                 *  @param constants: Contains free parameters k1 and b, which are free parameters,
                 *  where k1 is within [1.2, 2.0] and b = 0.75, in absence of advanced optimization.
                 *  In this implementation, k1 = 1.2.
                 */
                var b = constants && constants.b ? constants.b : 0.75;
                var k1 = constants && constants.k1 ? constants.k1 : 1.2;
                var documentLengths = documents.map(getWordCount);
                var averageDocumentLength = documentLengths.reduce(function (a, b) { return a + b; }, 0) / documents.length;
                var scores = documents.map(function (document) {
                    var score = keywords
                        .map(function (keyword, index) {
                            var inverseDocumentFrequency = getIDF(keyword, documents);
                            var termFrequency = getTermFrequency(keyword, document);
                            var documentLength = documentLengths[index];
                            return ((inverseDocumentFrequency * (termFrequency * (k1 + 1))) /
                                (termFrequency +
                                    k1 * (1 - b + (b * documentLength) / averageDocumentLength)));
                        })
                        .reduce(function (a, b) { return a + b; }, 0);
                    return score;
                });
                return scores;
            };
            rows.each(function(i, elem) {
                $(this).data("id", i);
            });
            var addonTexts = rows.map(function (i, content) {
                return $(content).text().toLowerCase();
            }).toArray();
            var calculateScore = function calculateScore(search) {
                var scores = BM25(addonTexts, search.split(/\W+/));
                rows.each(function(i, elem) {
                    var score = Math.round(scores[i] * 1000) * 10000 + parseInt($(this).data("id"));
                    if ($(this).find("[data-title=Key] a").text().toLowerCase() == search) {
                        score += 100000000;
                    }
                    $(this).data("order", score);
                    $(this).data("score", scores[i]);
                });
            };
            var sortRows = function sortRows(search) {
                if (location.href.match(/&sort=[^&]/)) {
                    return;
                }
                var orderKey;
                // set scores
                if (search) {
                    calculateScore(search);
                    orderKey = "order";
                }
                else {
                    orderKey = "id";
                }
                // sort
                var visibleRows = rows.filter(":visible");
                visibleRows.detach().sort(function(a, b) {
                    var va = parseInt($(a).data(orderKey));
                    var vb = parseInt($(b).data(orderKey));
                    if (va > vb) return orderKey === "order" ? -1 : 1;
                    if (va < vb) return orderKey === "order" ? 1 : -1;
                    return 0;
                });
                tablebody.prepend(visibleRows);
            };
            var searchTimeoutHandle = null;
            $("#rex-js-install-addon-search .form-control").keyup(function () {
                clearTimeout(searchTimeoutHandle);
                var search = $(this).val().toLowerCase();
                searchTimeoutHandle = setTimeout(function() {
                    rows.show();
                    if (search) {
                        rows.each(function () {
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
                    sortRows(search);
                }, 500);
            });
        });

        rex_searchfield_init("#rex-js-install-addon-search");
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
