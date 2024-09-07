<?php

use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Version;
use Redaxo\Core\View\Fragment;

use function Redaxo\Core\View\escape;

$report = rex_system_report::factory()->get();

echo '<div class="row"><div class="col-sm-6">';

foreach ($report as $title => $group) {
    if (rex_system_report::TITLE_PACKAGES === $title) {
        echo '</div><div class="col-sm-6">';
    }

    $content = '';

    foreach ($group as $label => $value) {
        if (rex_system_report::TITLE_PACKAGES === $title || rex_system_report::TITLE_REDAXO === $title) {
            if (null === $value) {
                throw new rex_exception('Package ' . $label . ' does not define a proper version in its package.yml');
            }
            if (Version::isUnstable($value)) {
                $value = '<i class="rex-icon rex-icon-unstable-version" title="' . I18n::msg('unstable_version') . '"></i> ' . escape($value);
            }
        } elseif (is_bool($value)) {
            $value = $value ? 'yes' : 'no';
        } else {
            $value = escape($value);
        }

        $content .= '
            <tr>
                <th width="120">' . escape($label) . '</th>
                <td data-title="' . escape($label) . '">' . $value . '</td>
            </tr>
        ';
    }

    $content = '<table class="table table-hover table-bordered"><tbody>' . $content . '</tbody></table>';

    $fragment = new Fragment();
    $fragment->setVar('title', $title);

    if (rex_system_report::TITLE_PHP === $title) {
        $phpinfo = '<a href="' . Url::backendPage('system/phpinfo') . '" class="btn btn-primary btn-xs" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;">phpinfo</a>';
        $fragment->setVar('options', $phpinfo, false);
    }

    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo '</div></div>';
