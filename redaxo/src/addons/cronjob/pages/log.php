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
            <table id="rex-table-log" class="table rex-table-middle table-striped">
                <thead>
                    <tr>
                        <th class="rex-icon"></th>
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
        $class = trim($data[0]) == 'ERROR' ? 'rex-warning' : 'rex-info';
        if ($data[1] == '--') {
            $icon = '<span class="rex-i-element rex-i-cronjob" title="' . rex_i18n::msg('cronjob_not_editable') . '"><span class="rex-i-element-text">' . rex_i18n::msg('cronjob_not_editable') . '</span></span>';
        } else {
            $icon = '<a href="' . rex_url::backendPage('cronjob', ['list' => 'cronjobs', 'func' => 'edit', 'oid' => $data[1]]) . '" title="' . rex_i18n::msg('cronjob_edit') . '"><span class="rex-i-element rex-i-cronjob"><span class="rex-i-element-text">' . rex_i18n::msg('cronjob_edit') . '</span></span></a>';
        }
        $content .= '
                    <tr class="' . $class . '">
                        <td class="rex-icon">' . $icon . '</td>
                        <td>' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                        <td>' . htmlspecialchars($data[2]) . '</td>
                        <td>' . $data[3] . '</td>
                    </tr>';
    }
}

$content .= '
                </tbody>
            </table>';

echo rex_view::content('block', $content, '', $params = ['flush' => true]);
