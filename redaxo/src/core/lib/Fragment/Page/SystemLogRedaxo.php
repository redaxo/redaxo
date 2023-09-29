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
use rex_path;
use rex_response;
use rex_type;
use stdClass;

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
    }

    protected function getPath(): string
    {
        return 'core/page/SystemLogRedaxo.php';
    }

    /** @return iterable<int, object{timestamp: int, type: string, message: string, file: ?string, line: ?int, editorUrl: ?string, url: ?string}> */
    public function getEntries(): iterable
    {
        /** @var rex_log_entry $entry */
        foreach (new LimitIterator(new rex_log_file($this->logFilePath), 0, 100) as $entry) {
            $data = $entry->getData();

            $element = new stdClass();
            $element->timestamp = $entry->getTimestamp();
            $element->type = rex_type::string($data[0]);
            $element->message = rex_type::string($data[1]);
            $element->file = $data[2] ?? null;
            $element->line = $data[3] ?? null;
            $element->editorUrl = null;
            $element->url = $data[4] ?? null;

            if ($element->file) {
                $fullPath = str_starts_with($element->file, 'rex://') ? $element->file : rex_path::base($element->file);
                $element->editorUrl = $this->editor->getUrl($fullPath, (int) ($element->line ?? 1));
            }

            yield $element;
        }
    }
}
