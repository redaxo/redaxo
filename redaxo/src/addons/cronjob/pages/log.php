<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$addon = rex_addon::get('cronjob');

$content = '';

$content .= '
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . rex_i18n::msg('cronjob_log_date') . '</th>
                        <th>' . rex_i18n::msg('cronjob_name') . '</th>
                        <th>' . rex_i18n::msg('cronjob_log_message') . '</th>
                    </tr>
                </thead>
                <tbody>';

$buttons = '';
$logFile = $addon->getDataPath('cronjob.log');
if ($file = new rex_log_file($logFile)) {
    foreach (new LimitIterator($file, 0, 30) as $entry) {
        /** @var rex_log_entry $entry */
        $data = $entry->getData();
        $class = 'ERROR' == trim($data[0]) ? 'rex-state-error' : 'rex-state-success';
        if ('--' == $data[1]) {
            $icon = '<i class="rex-icon rex-icon-cronjob" title="' . rex_i18n::msg('cronjob_not_editable') . '"></i>';
        } else {
            $icon = '<a href="' . rex_url::backendPage('cronjob', ['list' => 'cronjobs', 'func' => 'edit', 'oid' => $data[1]]) . '" title="' . rex_i18n::msg('cronjob_edit') . '"><i class="rex-icon rex-icon-cronjob"></i></a>';
        }
        $content .= '
                    <tr class="' . $class . '">
                        <td class="rex-table-icon">' . $icon . '</td>
                        <td data-title="' . rex_i18n::msg('cronjob_log_date') . '">' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                        <td data-title="' . rex_i18n::msg('cronjob_name') . '">' . rex_escape($data[2]) . '</td>
                        <td data-title="' . rex_i18n::msg('cronjob_log_message') . '">' . nl2br($data[3]) . '</td>
                    </tr>';
    }

    // XXX calc last line and use it instead
    if ($url = rex_editor::factory()->getUrl($logFile, 1)) {
        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-save" href="'. $url .'">' . rex_i18n::msg('system_editor_open_file', basename($logFile)) . '</a>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
    }
}

$content .= '
                </tbody>
            </table>';

$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
