<?php

/**
 * PHPMailer Archive Management.
 *
 * @author Thomas Skerbis
 */

/** @var rex_addon $addon */
$addon = rex_addon::get('phpmailer');
$func = rex_request('func', 'string');
$message = '';

// Handle archive deletion
if ('delete_archive' == $func) {
    if (rex_csrf_token::factory('phpmailer-delete-archive')->isValid()) {
        if (rex_dir::delete(rex_mailer::logFolder(), true)) {
            $message = rex_view::success($addon->i18n('archive_deleted'));
        } else {
            $message = rex_view::error($addon->i18n('archive_delete_error'));
        }
    } else {
        $message = rex_view::error($addon->i18n('csrf_token_invalid'));
    }
}

$archiveFolder = rex_mailer::logFolder();
$archiveExists = is_dir($archiveFolder);

// Two-column layout for Archive Information and Archive Maintenance
echo '<div class="row">';

// Archive Information Panel (left column)
echo '<div class="col-md-6">';
$content = '';

// Archive Status Panel
$formElements = [];
$n = [];
$n['label'] = '<label>' . $addon->i18n('archive_path') . '</label>';
$n['field'] = '<span style="word-break: break-all; font-family: monospace; font-size: 0.9em;">' . rex_escape($archiveFolder) . '</span>';
$formElements[] = $n;

if ($archiveExists) {
    // Get archive statistics
    $archiveStats = rex_mailer::getArchiveStats();

    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_size') . '</label>';
    $n['field'] = rex_formatter::bytes($archiveStats['size']);
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_file_count') . '</label>';
    $n['field'] = $archiveStats['fileCount'] . ' ' . $addon->i18n('archive_files');
    $formElements[] = $n;
} else {
    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_status') . '</label>';
    $n['field'] = '<span class="text-muted">' . $addon->i18n('archive_empty') . '</span>';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$fragment->setVar('grouped', true);
$content .= $fragment->parse('core/form/form.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $addon->i18n('archive_info'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
echo '</div>';

// Archive Maintenance Panel (right column)
echo '<div class="col-md-6">';
$content = '';
$content .= '<p>' . $addon->i18n('archive_purge_info') . '</p>';

// Add archive management to maintenance panel if archive exists
if ($archiveExists) {
    $content .= '<hr>';
    $content .= '<div>';
    $content .= '<strong>' . $addon->i18n('archive_delete_warning') . '</strong><br>';
    $content .= $addon->i18n('archive_delete_warning_desc');
    $content .= '</div>';

    $content .= '<form method="post" action="' . rex_url::currentBackendPage() . '">';
    $content .= rex_csrf_token::factory('phpmailer-delete-archive')->getHiddenField();
    $content .= '<input type="hidden" name="func" value="delete_archive">';
    $content .= '<button type="submit" class="btn btn-danger btn-sm" data-confirm="' . $addon->i18n('archive_delete_confirm') . '">';
    $content .= '<i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('archive_delete');
    $content .= '</button>';
    $content .= '</form>';
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('archive_maintenance'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
echo '</div>';

// Close row
echo '</div>';

// Recent Archived Emails Panel
if ($archiveExists) {
    $content = '';
    $content .= '<table class="table table-hover">';
    $content .= '<thead>';
    $content .= '<tr>';
    $content .= '<th>' . $addon->i18n('archive_date') . '</th>';
    $content .= '<th>' . $addon->i18n('archive_subject') . '</th>';
    $content .= '<th>' . $addon->i18n('archive_to') . '</th>';
    $content .= '<th>' . $addon->i18n('archive_size') . '</th>';
    $content .= '</tr>';
    $content .= '</thead>';
    $content .= '<tbody>';

    // Get recent .eml files using rex_mailer method
    $recentFiles = rex_mailer::getRecentArchivedFiles(10);

    if ($recentFiles) {
        /** @var string $file */
        foreach ($recentFiles as $file) {
            // Parse email headers using rex_mailer method
            $emailData = rex_mailer::parseEmailHeaders($file);

            $content .= '<tr>';
            $content .= '<td data-title="' . $addon->i18n('archive_date') . '" class="rex-table-tabular-nums">' . rex_formatter::intlDateTime($emailData['mtime'], [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) . '</td>';
            $content .= '<td data-title="' . $addon->i18n('archive_subject') . '">' . rex_escape($emailData['subject']) . '</td>';
            $content .= '<td data-title="' . $addon->i18n('archive_to') . '">' . rex_escape($emailData['recipient']) . '</td>';
            $content .= '<td data-title="' . $addon->i18n('archive_size') . '" class="rex-table-tabular-nums">' . rex_formatter::bytes($emailData['size']) . '</td>';
            $content .= '</tr>';
        }
    } else {
        $content .= '<tr><td colspan="4" class="text-muted text-center">' . $addon->i18n('archive_no_files') . '</td></tr>';
    }

    $content .= '</tbody>';
    $content .= '</table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('archive_recent_mails'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo $message;
