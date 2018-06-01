<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('credits'), '');

if (rex_get('license')) {
    $license = rex_markdown::factory()->parse(rex_file::get(rex_path::base('LICENSE.md')));

    $fragment = new rex_fragment();
    $fragment->setVar('title', 'REDAXO '. rex_i18n::msg('credits_license'));
    $fragment->setVar('body', $license, false);
    echo '<div id="license"></div>'; // scroll anchor
    echo $fragment->parse('core/page/section.php');

    echo '<a class="btn btn-back" href="javascript:history.back();">' . rex_i18n::msg('package_back') . '</a>';

    return;
}

$content = [];

$content[] = '
    <h3>Jan Kristinus <small>jan.kristinus@redaxo.org</small></h3>
    <p>
        ' . rex_i18n::msg('credits_inventor') . ' &amp; ' . rex_i18n::msg('credits_developer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="https://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Markus Staab <small>markus.staab@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        REDAXO, <a href="https://www.redaxo.org" onclick="window.open(this.href); return false;">www.redaxo.org</a>
    </p>

    <h3>Gregor Harlan <small>gregor.harlan@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_developer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="https://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>';

$content[] = '
    <h3>Ralph Zumkeller <small>info@redaxo.org</small></h3>
    <p>' . rex_i18n::msg('credits_designer') . '<br />
        Yakamara Media GmbH &amp; Co. KG, <a href="https://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
    </p>

    <h3>Thomas Blum <small>thomas.blum@redaxo.org</small></h3>
    <p>Yakamara Media GmbH &amp; Co. KG, <a href="https://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/grid.php');

$fragment = new rex_fragment();
$fragment->setVar('title', 'REDAXO <small>' . rex::getVersion() . ' &ndash; <a href="'. rex_url::backendPage('credits', ['license' => 'core']) .'">'. rex_i18n::msg('credits_license') .'</a></small>', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

$content = '';

$content .= '

    <table class="table table-hover">
        <thead>
        <tr>
            <th class="rex-table-icon">&nbsp;</th>
            <th>' . rex_i18n::msg('credits_name') . '</th>
            <th>' . rex_i18n::msg('credits_version') . '</th>
            <th class="rex-table-slim">' . rex_i18n::msg('credits_help') . '</th>
            <th>' . rex_i18n::msg('credits_author') . '</th>
            <th>' . rex_i18n::msg('credits_supportpage') . '</th>
        </tr>
        </thead>

        <tbody>';

        foreach (rex_package::getAvailablePackages() as $package) {
            $helpUrl = rex_url::backendPage('packages', ['subpage' => 'help', 'package' => $package->getPackageId()]);

            $license = '';
            if (is_readable($licenseFile = $package->getPath('LICENSE.md')) || is_readable($licenseFile = $package->getPath('LICENSE'))) {
                $f = fopen($licenseFile, 'r');
                $firstLine = fgets($f);
                fclose($f);

                $license = '<a href="'. $helpUrl .'#license">'. rex_escape($firstLine) .'</a> / ';
            }

            $content .= '
            <tr class="rex-package-is-' . $package->getType() . '">
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package-' . $package->getType() . '"></i></td>
                <td data-title="' . rex_i18n::msg('credits_name') . '">' . $package->getName() . ' </td>
                <td data-title="' . rex_i18n::msg('credits_version') . '">' . $package->getVersion() . '</td>
                <td class="rex-table-slim" data-title="' . rex_i18n::msg('credits_help') . '">
                    '. $license .'
                    <a href="' . $helpUrl . '" title="' . rex_i18n::msg('credits_open_help_file') . ' ' . $package->getName() . '"><i class="rex-icon rex-icon-help"></i> <span class="sr-only">' . rex_i18n::msg('package_help') . ' ' . htmlspecialchars($package->getName()) . '</span></a>
                </td>
                <td data-title="' . rex_i18n::msg('credits_author') . '">' . $package->getAuthor() . '</td>
                <td data-title="' . rex_i18n::msg('credits_supportpage') . '">';

            if ($supportpage = $package->getSupportPage()) {
                $content .= '<a href="' . $supportpage . '" onclick="window.open(this.href); return false;"><i class="rex-icon rex-icon-external-link"></i> ' . $supportpage . '</a>';
            }

            $content .= '
                </td>
            </tr>';
        }

        $content .= '
        </tbody>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('credits_caption'), false);
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
