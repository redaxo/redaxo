<?php

$report = rex_system_report::factory()->get();

echo '<div class="row"><div class="col-sm-6">';

foreach ($report as $title => $group) {
    if ('Packages' === $title) {
        echo '</div><div class="col-sm-6">';
    }

    $content = '';

    foreach ($group as $label => $value) {
        if (is_bool($value)) {
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

    if ('PHP' === $title) {
        $phpinfo = '<a href="'.rex_url::backendPage('system/phpinfo').'" class="btn btn-primary btn-xs" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;">phpinfo</a>';
        $fragment->setVar('options', $phpinfo, false);
    }

    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo '</div></div>';
