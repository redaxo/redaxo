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
            $class = $value ? 'fa-check text-success' : 'fa-times text-danger';
            $value = '<i class="rex-icon '.$class.'"></i>';
        } else {
            $value = rex_escape($value);
        }

        $content .= '
            <tr>
                <th width="120">'.rex_escape($label).'</th>
                <td>'.$value.'</td>
            </tr>
        ';
    }

    $content = '<table class="table table-hover table-bordered">'.$content.'</table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo '</div></div>';
