<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('credits'), '');

$content_1 = '';
$content_2 = '';

$content_1 .= '
    <h2>REDAXO</h2>

    <h3>Jan Kristinus <span>jan.kristinus@redaxo.org</span></h3>
    <p>
        ' . rex_i18n::msg('credits_inventor') . ' &amp ' . rex_i18n::msg('credits_developer') . '<br />
        Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Markus Staab <span>markus.staab@redaxo.org</span></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        REDAXO, <a href="http://www.redaxo.org" onclick="window.open(this.href); return false;">www.redaxo.org</a>
    </p>

    <h3>Gregor Harlan <span>gregor.harlan@redaxo.org</span></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        meyerharlan, <a href="http://meyerharlan.de" onclick="window.open(this.href); return false;">www.meyerharlan.de</a>
    </p>';

$content_2 .= '
    <h2>' . rex::getVersion() . '</h2>

    <h3>Ralph Zumkeller <span>info@redaxo.org</span></h3>
    <p>' . rex_i18n::msg('credits_designer') . '<br />
        Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Thomas Blum <span>thomas.blum@redaxo.org</span></h3>
    <p>HTML/CSS<br />
        blumbeet - web.studio, <a href="http://www.blumbeet.com" onclick="window.open(this.href); return false;">www.blumbeet.com</a>
    </p>';


echo rex_view::contentBlock($content_1, $content_2);


$content = '';

$content .= '

    <table id="rex-table-credits-addons" class="rex-table">
        <caption>' . rex_i18n::msg('credits_caption') . '</caption>
        <thead>
        <tr>
            <th class="rex-name">' . rex_i18n::msg('credits_name') . '</th>
            <th class="rex-version">' . rex_i18n::msg('credits_version') . '</th>
            <th class="rex-author">' . rex_i18n::msg('credits_author') . '</th>
            <th class="rex-support">' . rex_i18n::msg('credits_supportpage') . '</th>
        </tr>
        </thead>

        <tbody>';

        foreach (rex_package::getRegisteredPackages() as $package) {
            $content .= '
            <tr class="rex-' . $package->getType() . ' rex-' . ($package->isAvailable() ? 'active' : 'inactive') . '">
                <td class="rex-name">' . $package->getName() . ' <a href="' . rex_url::backendPage('packages', ['subpage' => 'help', 'package' => $package->getPackageId()]) . '" title="' . rex_i18n::msg('credits_open_help_file') . ' ' . $package->getName() . '">?</a></td>
                <td class="rex-version">' . $package->getVersion() . '</td>
                <td class="rex-author">' . $package->getAuthor() . '</td>
                <td class="rex-support">';

            if ($supportpage = $package->getSupportPage()) {
                $content .= '<a href="http://' . $supportpage . '" onclick="window.open(this.href); return false;">' . $supportpage . '</a>';
            }

            $content .= '
                </td>
            </tr>';
        }

        $content .= '
        </tbody>
    </table>';



echo rex_view::contentBlock($content, '', 'block');
