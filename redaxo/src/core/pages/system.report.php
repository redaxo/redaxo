<?php

foreach (rex_system_report::factory()->get() as $title => $group) {
    $content = '';

    foreach ($group as $label => $value) {
        $content .= '<dt>'.rex_escape($label).'</dt>';

        if (is_bool($value)) {
            $class = $value ? 'fa-check text-success' : 'fa-times text-danger';
            $value = '<i class="rex-icon '.$class.'"></i>';
        } else {
            $value = rex_escape($value);
        }

        $content .= '<dd>'.$value.'</dd>';
    }

    $content = '<dl class="dl-horizontal">'.$content.'</dl>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}

