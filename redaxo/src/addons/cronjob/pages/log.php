<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$content = '';

$content .= '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th></th>
                        <th>' . rex_i18n::msg('cronjob_log_date') . '</th>
                        <th>' . rex_i18n::msg('cronjob_name') . '</th>
                        <th>' . rex_i18n::msg('cronjob_log_message') . '</th>
                    </tr>
                </thead>
                <tbody>';

if ($file = new rex_log_file($this->getDataPath('cronjob.log'))) {
    foreach (new LimitIterator($file, 0, 30) as $entry) {
        /* @var rex_log_entry $entry */
        $data = $entry->getData();
        $class = trim($data[0]) == 'ERROR' ? 'text-danger' : 'text-success';
        if ($data[1] == '--') {
            $icon = '<i class="rex-icon rex-icon-cronjob" title="' . rex_i18n::msg('cronjob_not_editable') . '"></i>';
        } else {
            $icon = '<a href="' . rex_url::backendPage('cronjob', ['list' => 'cronjobs', 'func' => 'edit', 'oid' => $data[1]]) . '" title="' . rex_i18n::msg('cronjob_edit') . '"><i class="rex-icon rex-icon-cronjob"></i></a>';
        }
        $content .= '
                    <tr class="' . $class . '">
                        <td>' . $icon . '</td>
                        <td>' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                        <td>' . htmlspecialchars($data[2]) . '</td>
                        <td>' . $data[3] . '</td>
                    </tr>';
    }
}

$content .= '
                </tbody>
            </table>';

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
