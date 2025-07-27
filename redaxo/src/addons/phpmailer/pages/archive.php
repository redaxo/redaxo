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

// Two-column layout for Archive Information and Legal Notice
echo '<div class="row">';

// Archive Information Panel (left column)
echo '<div class="col-md-6">';
$content = '';

// Archive Status Panel
$formElements = [];
$n = [];
$n['label'] = '<label>' . $addon->i18n('archive_path') . '</label>';
$n['field'] = '<code>' . rex_escape($archiveFolder) . '</code>';
$formElements[] = $n;

if ($archiveExists) {
    // Calculate archive size manually
    $archiveSize = 0;
    $fileCount = 0;
    
    // Use rex_finder to count all .eml files in subdirectories
    $finder = rex_finder::factory($archiveFolder)->recursive()->filesOnly();
    
    foreach ($finder as $file) {
        $archiveSize += $file->getSize();
        if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'eml') {
            $fileCount++;
        }
    }

    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_size') . '</label>';
    $n['field'] = rex_formatter::bytes($archiveSize);
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_file_count') . '</label>';
    $n['field'] = $fileCount . ' ' . $addon->i18n('archive_files');
    $formElements[] = $n;
} else {
    $n = [];
    $n['label'] = '<label>' . $addon->i18n('archive_status') . '</label>';
    $n['field'] = '<span class="text-muted">' . $addon->i18n('archive_empty') . '</span>';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

// Add archive management to the same panel if archive exists
if ($archiveExists) {
    $content .= '<hr>';
    $content .= '<div style="margin-bottom: 15px;">';
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
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $addon->i18n('archive_info_page'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
echo '</div>';

// Legal Notice Panel (right column)
echo '<div class="col-md-6">';
$content = '';
$content .= '<p>' . $addon->i18n('archive_legal_notice_text') . '</p>';
$content .= '<ul>';
$content .= '<li>' . $addon->i18n('archive_legal_notice_technical') . '</li>';
$content .= '<li>' . $addon->i18n('archive_legal_notice_not_legal') . '</li>';
$content .= '<li>' . $addon->i18n('archive_legal_notice_gdpr') . '</li>';
$content .= '<li>' . $addon->i18n('archive_legal_notice_deletion') . '</li>';
$content .= '<li>' . $addon->i18n('archive_legal_notice_avv') . '</li>';
$content .= '</ul>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('archive_legal_notice_title'), false);
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
    
    // Get recent .eml files recursively using rex_finder
    $finder = rex_finder::factory($archiveFolder)->recursive()->filesOnly();
    $files = [];
    
    foreach ($finder as $path => $file) {
        if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'eml') {
            $files[] = $path;
        }
    }
    if ($files) {
        // Sort by modification time, newest first
        usort($files, function(string $a, string $b): int {
            $timeA = filemtime($a);
            $timeB = filemtime($b);
            if ($timeA === false || $timeB === false) {
                return 0;
            }
            return $timeB - $timeA;
        });

        // Show only last 10 files
        $recentFiles = array_slice($files, 0, 10);
        
        /** @var string $file */
        foreach ($recentFiles as $file) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $filesize = filesize($file);
            $filemtime = filemtime($file);

            // Read email headers from .eml file
            $subject = $addon->i18n('archive_no_subject');
            $recipient = $addon->i18n('archive_no_recipient');

            $handle = fopen($file, 'r');
            if ($handle) {
                $headerLines = 0;
                while (($line = fgets($handle)) !== false && $headerLines < 50) {
                    $line = trim($line);
                    if (empty($line)) {
                        break;
                    } // End of headers

                    if (0 === stripos($line, 'Subject:')) {
                        $subject = substr($line, 8);
                        // Decode MIME encoded subjects
                        if (function_exists('iconv_mime_decode')) {
                            $decodedSubject = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
                            if ($decodedSubject !== false) {
                                $subject = $decodedSubject;
                            }
                        }
                        $subject = rex_escape(trim($subject));
                        $subject = mb_strlen($subject) > 50 ? mb_substr($subject, 0, 50) . '...' : $subject;
                    }

                    if (0 === stripos($line, 'To:')) {
                        $recipient = rex_escape(trim(substr($line, 3)));
                        $recipient = mb_strlen($recipient) > 30 ? mb_substr($recipient, 0, 30) . '...' : $recipient;
                    }

                    ++$headerLines;
                }
                fclose($handle);
            }
            
            // Check for false values and provide defaults
            $filemtimeValue = $filemtime !== false ? $filemtime : 0;
            $filesizeValue = $filesize !== false ? $filesize : 0;
            
            $content .= '<tr>';
            $content .= '<td class="rex-table-tabular-nums">' . rex_formatter::intlDateTime($filemtimeValue, [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) . '</td>';
            $content .= '<td>' . $subject . '</td>';
            $content .= '<td>' . $recipient . '</td>';
            $content .= '<td class="rex-table-tabular-nums">' . rex_formatter::bytes($filesizeValue) . '</td>';
            $content .= '</tr>';
        }
    } else {
        $content .= '<tr><td colspan="4" class="text-muted text-center">' . $addon->i18n('archive_no_files') . '</td></tr>';
    }

    $content .= '</tbody>';
    $content .= '</table>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $addon->i18n('archive_recent_mails'), false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}

echo $message;
