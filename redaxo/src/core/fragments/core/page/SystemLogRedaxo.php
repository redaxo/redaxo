<?php

use Redaxo\Core\Fragment\Component\Alert\Error;
use Redaxo\Core\Fragment\Component\Alert\Success;
use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonType;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Card;
use Redaxo\Core\Fragment\Html;
use Redaxo\Core\Fragment\HtmlAttributes;
use Redaxo\Core\Fragment\Page\SystemLogRedaxo;

/**
 * @var SystemLogRedaxo $this
 * @psalm-scope-this SystemLogRedaxo
 */
?>

<?php if ($this->error): ?>
    <?= (new Error(
        body: new Html($this->error),
    ))->render() ?>
<?php endif ?>

<?php if ($this->success): ?>
    <?= (new Success(
        body: new Html($this->success),
    ))->render() ?>
<?php endif ?>

<form action="<?= rex_url::currentBackendPage() ?>" method="post">
    <input type="hidden" name="func" value="delLog" />
    <?= $this->csrfToken->getHiddenField() ?>

    <?= (new Card(
        header: rex_i18n::rawMsg('syslog_title', $this->logFilePath),
        body: new Html(function () { ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= rex_i18n::msg('syslog_timestamp') ?></th>
                        <th><?= rex_i18n::msg('syslog_message') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($this->getEntries() as $entry) {
                    $data = $entry->getData();

                    $type = rex_type::string($data[0]);
                    $message = rex_type::string($data[1]);
                    $file = $data[2] ?? null;
                    $line = $data[3] ?? null;

                    $class = match (strtolower($type)) {
                        'success' => 'success',
                        'debug' => 'default',
                        'info', 'notice', 'deprecated' => 'info',
                        'warning' => 'warning',
                        default => 'danger',
                    };

                    $path = '';
                    if ($file) {
                        $path = rex_escape($file . (null === $line ? '' : ':' . $line));

                        $fullPath = str_starts_with($file, 'rex://') ? $file : rex_path::base($file);
                        if ($url = $this->editor->getUrl($fullPath, (int) ($line ?? 1))) {
                            $path = '<a href="' . $url . '">' . $path . '</a>';
                        }
                        $path = '<small class="rex-word-break"><span class="label label-default">' . rex_i18n::msg('syslog_file') . ':</span> ' . $path . '</small><br>';
                    }

                    $url = $data[4] ?? null;
                    if ($url) {
                        $url = rex_escape($url);
                        $url = '<small class="rex-word-break"><span class="label label-default">' . rex_i18n::msg('syslog_url') . ':</span> <a href="' . $url . '">' . $url . '</a></small>';
                    } else {
                        $url = '';
                    }
                    ?>
                    <tr>
                        <td class="rex-table-tabular-nums rex-table-date">
                            <small><?= rex_formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) ?></small><br>
                            <span class="label label-<?= $class ?>"><?= rex_escape($type) ?></span>
                        </td>
                        <td>
                            <div class="rex-word-break"><b style="font-weight: 500"><?= nl2br(rex_escape($message)) ?></b></div>
                            <?= $path ?>
                            <?= $url ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        <?php }),
        footer: new Html(function () { ?>
            <div>
                <?= (new Button(
                    label: rex_i18n::msg('syslog_delete'),
                    name: 'del_btn',
                    variant: ButtonVariant::Danger,
                    type: ButtonType::Submit,
                    attributes: new HtmlAttributes([
                        'data-confirm' => rex_i18n::msg('delete') . '?',
                    ]),
                ))->render() ?>
                <?php if ($url = $this->editor->getUrl($this->logFilePath, 0)) { ?>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('system_editor_open_file', rex_path::basename($this->logFilePath)),
                        variant: ButtonVariant::Success,
                        href: $url,
                    ))->render() ?>
                <?php } ?>
                <?php if (is_file($this->logFilePath)) { ?>
                    <?= (new Button(
                        label: rex_i18n::rawMsg('syslog_download', rex_path::basename($this->logFilePath)),
                        variant: ButtonVariant::Success,
                        href: rex_url::currentBackendPage(['func' => 'download'] + $this->csrfToken->getUrlParams()),
                        attributes: new HtmlAttributes([
                            'download' => true,
                        ]),
                    ))->render() ?>
                <?php } ?>
            </div>
        <?php }),
    ))->render() ?>
</form>
