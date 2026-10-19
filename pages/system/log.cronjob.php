<?php

use Redaxo\Core\Cronjob\CronjobManager;
use Redaxo\Core\Cronjob\CronjobRegistry;
use Redaxo\Core\Exception\InvalidArgumentException;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Log\LogEntry;
use Redaxo\Core\Log\LogFile;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Editor;
use Redaxo\Core\Util\Formatter;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;

use function Redaxo\Core\View\escape;

$func = Request::request('func', 'string');
$error = '';
$success = '';
$message = '';
$logFile = Path::log('cronjob.log');

$csrfToken = CsrfToken::factory('cronjob_log');

if ('cronjob_delLog' == $func && !$csrfToken->isValid()) {
    $error = I18n::msg('csrf_token_invalid');
} elseif ('cronjob_delLog' == $func) {
    if (LogFile::delete($logFile)) {
        $success = I18n::msg('syslog_deleted');
    } else {
        $error = I18n::msg('syslog_delete_error');
    }
}
if ('' != $success) {
    $message .= Message::success($success);
}
if ('' != $error) {
    $message .= Message::error($error);
}
$content = '';

$content .= '
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . I18n::msg('cronjob_log_date') . '</th>
                        <th>' . I18n::msg('cronjob_name') . '</th>
                        <th>' . I18n::msg('cronjob_log_message') . '</th>
                        <th>' . I18n::msg('cronjob_trigger') . '</th>
                    </tr>
                </thead>
                <tbody>';

$formElements = [];

$file = LogFile::factory($logFile);

/** @var LogEntry $entry */
foreach (new LimitIterator($file, 0, 100) as $entry) {
    $data = $entry->getData();
    $class = 'ERROR' == trim($data[0]) ? 'rex-state-error' : 'rex-state-success';
    $trigger = $data[3] ?? '';
    $content .= '
        <tr class="' . $class . '">
            <td class="rex-table-icon"><i class="rex-icon rex-icon-cronjob"></i></td>
            <td data-title="' . I18n::msg('cronjob_log_date') . '" class="rex-table-tabular-nums">' . Formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) . '</td>
            <td data-title="' . I18n::msg('cronjob_name') . '">' . escape($data[1] ?? '') . '</td>
            <td data-title="' . I18n::msg('cronjob_log_message') . '">' . nl2br(escape($data[2] ?? '')) . '</td>
            <td data-title="' . I18n::msg('cronjob_trigger') . '">' . ('schedule' === $trigger || 'manual' === $trigger ? I18n::msg('cronjob_trigger_' . $trigger) : escape($trigger)) . '</td>
        </tr>';
}

// XXX calc last line and use it instead
if ($url = Editor::factory()->getUrl($logFile, 1)) {
    $n = [];
    $n['field'] = '<a class="btn btn-save" href="' . $url . '">' . I18n::msg('system_editor_open_file', Path::basename($logFile)) . '</a>';
    $formElements[] = $n;
}

$n = [];
$n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . I18n::msg('cronjob_delete_log_msg') . '?">' . I18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '
                </tbody>
            </table>';

$fragment = new Fragment();
$fragment->setVar('title', I18n::msg('cronjob_log'), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . Url::currentBackendPage() . '" method="post">
        ' . $csrfToken->getHiddenField() . '
        <input type="hidden" name="func" value="cronjob_delLog" />
        ' . $content . '
    </form>';

echo $message;

try {
    $setting = CronjobManager::getSetting();
} catch (InvalidArgumentException $exception) {
    echo Message::error(escape($exception->getMessage()));
    $setting = null;
}

if (['all' => false, 'names' => []] === $setting) {
    echo Message::warning(I18n::msg('cronjob_disabled_by_env'));
}
if (null !== $setting && $unknown = CronjobManager::getUnknownNames()) {
    echo Message::warning(I18n::msg('cronjob_unknown_names_in_env', escape(implode(', ', $unknown))));
}

$cronjobs = CronjobRegistry::all();
$states = CronjobManager::getStates();
$now = new DateTimeImmutable();

$overview = '';
$overviewBody = '';

if (!$cronjobs) {
    $overviewBody = I18n::msg('cronjob_no_cronjobs');
} else {
    $overview .= '
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="rex-table-icon"></th>
                    <th>' . I18n::msg('cronjob_name') . '</th>
                    <th>' . I18n::msg('cronjob_schedule') . '</th>
                    <th>' . I18n::msg('cronjob_lastrun') . '</th>
                    <th>' . I18n::msg('cronjob_nexttime') . '</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($cronjobs as $cronjob) {
        $state = $states[$cronjob->name] ?? null;
        $lastRun = $state['lastRun'] ?? null;

        if (null === $state || null === $lastRun) {
            $lastRunText = '–';
        } else {
            $lastRunText = Formatter::intlDateTime($lastRun);
            if ($state['running']) {
                $lastRunText .= ' <span class="text-muted">(' . I18n::msg('cronjob_running') . ')</span>';
            } elseif (true === $state['lastSuccess']) {
                $lastRunText = '<span class="rex-online"><i class="rex-icon rex-icon-active-true"></i> ' . $lastRunText . '</span>';
            } elseif (false === $state['lastSuccess']) {
                $lastRunText = '<span class="rex-offline"><i class="rex-icon rex-icon-active-false"></i> ' . $lastRunText . '</span>';
            }
        }

        if (null === $setting) {
            $nextRunText = '–';
        } elseif (!CronjobManager::isEnabled($cronjob->name)) {
            $nextRunText = '<span class="text-muted">' . I18n::msg('cronjob_not_enabled') . '</span>';
        } else {
            $nextRunText = Formatter::intlDateTime($cronjob->getNextRunDate($lastRun ?? $now));
        }

        $overview .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-cronjob"></i></td>
                <td data-title="' . I18n::msg('cronjob_name') . '">' . escape($cronjob->name) . '<br /><small class="text-muted">' . escape($cronjob->class . '::' . $cronjob->method . '()') . '</small></td>
                <td data-title="' . I18n::msg('cronjob_schedule') . '"><code class="text-nowrap">' . escape($cronjob->schedule) . '</code></td>
                <td data-title="' . I18n::msg('cronjob_lastrun') . '" class="rex-table-tabular-nums">' . $lastRunText . '</td>
                <td data-title="' . I18n::msg('cronjob_nexttime') . '" class="rex-table-tabular-nums">' . $nextRunText . '</td>
            </tr>';
    }

    $overview .= '
            </tbody>
        </table>';
}

$fragment = new Fragment();
$fragment->setVar('title', I18n::msg('cronjob_caption'), false);
$fragment->setVar('body', $overviewBody, false);
$fragment->setVar('content', $overview, false);
$fragment->setVar('footer', I18n::rawMsg('cronjob_notice', escape(Path::bin('console') . ' cronjob:run')), false);
echo $fragment->parse('core/page/section.php');

echo $content;
