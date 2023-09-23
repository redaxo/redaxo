<?php

namespace Redaxo\Core\Fragment\Page;

use LimitIterator;
use Redaxo\Core\Fragment\Page;
use rex_csrf_token;
use rex_editor;
use rex_i18n;
use rex_log_entry;
use rex_log_file;
use rex_logger;
use rex_response;

/**
 * @see redaxo/src/core/fragments/core/page/SystemLogRedaxo.php
 */
final class SystemLogRedaxo extends Page
{
    public readonly rex_csrf_token $csrfToken;

    public readonly rex_editor $editor;

    public readonly rex_log_file $logFile;

    public readonly string $logFilePath;

    public ?string $success = null;

    public ?string $error = null;

    public function __construct()
    {
        $this->csrfToken = rex_csrf_token::factory('system-log-redaxo');
        $this->editor = rex_editor::factory();
        $this->logFilePath = rex_logger::getPath();

        $func = rex_request('func', 'string');

        if ($func && !$this->csrfToken->isValid()) {
            $this->error = rex_i18n::msg('csrf_token_invalid');
        } elseif ('delLog' === $func) {
            // close logger, to free remaining file-handles to syslog
            // so we can safely delete the file
            rex_logger::close();

            if (rex_log_file::delete($this->logFilePath)) {
                $this->success = rex_i18n::msg('syslog_deleted');
            } else {
                $this->error = rex_i18n::msg('syslog_delete_error');
            }
        } elseif ('download' === $func && is_file($this->logFilePath)) {
            rex_response::sendFile($this->logFilePath, 'application/octet-stream', 'attachment');
            exit;
        }

        $this->logFile = new rex_log_file($this->logFilePath);
    }

    protected function getPath(): string
    {
        return 'core/page/SystemLogRedaxo.php';
    }

    /** @return iterable<int, rex_log_entry> */
    public function getEntries(): iterable
    {
        return new LimitIterator($this->logFile, 0, 100);
    }
}
