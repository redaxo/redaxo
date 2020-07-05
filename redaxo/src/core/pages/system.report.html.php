<?php

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
                throw new rex_exception('Package '. $label .' does not define a proper version in its package.yml');
            }
            if (rex_version::isUnstable($value)) {
                $value = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. rex_escape($value);
            }
        } elseif (is_bool($value)) {
            $value = $value ? 'yes' : 'no';
        } else {
            $value = rex_escape($value);
        }

        $content .= '
            <tr>
                <th width="120">'.rex_escape($label).'</th>
                <td data-title="'.rex_escape($label).'">'.$value.'</td>
            </tr>
        ';
    }

    $content = '<table class="table table-hover table-bordered"><tbody>'.$content.'</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);

    if (rex_system_report::TITLE_PHP === $title) {
        $phpinfo = '<a href="'.rex_url::backendPage('system/phpinfo').'" class="btn btn-primary btn-xs" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;">phpinfo</a>';
        $fragment->setVar('options', $phpinfo, false);
    }

    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo '</div></div>';
