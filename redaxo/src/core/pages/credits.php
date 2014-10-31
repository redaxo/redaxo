<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('credits'), '');

$content = [];

$content[] = '
    <h2>REDAXO</h2>

    <h3>Jan Kristinus <small>jan.kristinus@redaxo.org</small></h3>
    <p>
        ' . rex_i18n::msg('credits_inventor') . ' &amp ' . rex_i18n::msg('credits_developer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Markus Staab <small>markus.staab@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        REDAXO, <a href="http://www.redaxo.org" onclick="window.open(this.href); return false;">www.redaxo.org</a>
    </p>

    <h3>Gregor Harlan <small>gregor.harlan@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>';

$content[] = '
    <h2>' . rex::getVersion() . '</h2>

    <h3>Ralph Zumkeller <small>info@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_designer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Thomas Blum <small>thomas.blum@redaxo.org</small></h3>
    <p>Yakamara Media GmbH &amp; Co. KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/grid.php');

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');



$content = '';

$content .= '

    <table id="rex-table-credits-addons" class="table table-responsive">
        <caption>' . rex_i18n::msg('credits_caption') . '</caption>
        <thead>
        <tr>
            <th>' . rex_i18n::msg('credits_name') . '</th>
            <th>' . rex_i18n::msg('credits_version') . '</th>
            <th>' . rex_i18n::msg('credits_author') . '</th>
            <th>' . rex_i18n::msg('credits_supportpage') . '</th>
        </tr>
        </thead>

        <tbody>';

        foreach (rex_package::getRegisteredPackages() as $package) {
            $content .= '
            <tr class="rex-' . $package->getType() . ' rex-' . $package->getType() . '-' . ($package->isAvailable() ? 'active' : 'inactive') . '">
                <th data-title="' . rex_i18n::msg('credits_name') . '">' . $package->getName() . ' <a href="' . rex_url::backendPage('packages', ['subpage' => 'help', 'package' => $package->getPackageId()]) . '" title="' . rex_i18n::msg('credits_open_help_file') . ' ' . $package->getName() . '"><i class="rex-icon rex-icon-help"></i></a></th>
                <td data-title="' . rex_i18n::msg('credits_version') . '">' . $package->getVersion() . '</td>
                <td data-title="' . rex_i18n::msg('credits_author') . '">' . $package->getAuthor() . '</td>
                <td data-title="' . rex_i18n::msg('credits_supportpage') . '">';

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



$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
