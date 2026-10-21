<?php

namespace Redaxo\Core\Mailer;

use DateTimeInterface;
use Redaxo\Core\Cronjob\AsCronjob;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxListHeader;

use function array_slice;
use function is_array;

use const ICONV_MIME_DECODE_CONTINUE_ON_ERROR;
use const PATHINFO_EXTENSION;

/**
 * Archive of the sent mails as `.eml` files, enabled by the env var `REX_MAILER_ARCHIVE`.
 */
final class MailArchive
{
    private function __construct() {}

    /** @internal */
    public static function folder(): string
    {
        return Path::coreData('mailer/archive');
    }

    /**
     * The envelope recipients are prepended as `X-Envelope-To` header, because the `Bcc` header is not part of the mail.
     *
     * @param array<Address> $recipients
     * @internal
     */
    public static function add(string $content, array $recipients, bool $sent = true): void
    {
        $recipients = array_map(static fn (Address $address) => new Address($address->getAddress()), $recipients);
        $content = new MailboxListHeader('X-Envelope-To', $recipients)->toString() . "\r\n" . $content;

        $dir = self::folder() . '/' . date('Y') . '/' . date('m');
        $prefix = $sent ? '' : 'not_sent_';
        $count = 1;
        $file = $dir . '/' . $prefix . date('Y-m-d_H_i_s') . '.eml';
        while (is_file($file)) {
            $file = $dir . '/' . $prefix . date('Y-m-d_H_i_s') . '_' . (++$count) . '.eml';
        }

        File::put($file, $content);
    }

    /**
     * Deletes the archived mails older than the given date, e.g. in a cronjob (see {@see AsCronjob}).
     *
     * @return int Number of deleted mails
     */
    public static function purge(DateTimeInterface $olderThan): int
    {
        $folder = self::folder();

        if (!is_dir($folder)) {
            return 0;
        }

        $count = 0;
        foreach (Finder::factory($folder)->recursive()->childFirst() as $path => $file) {
            if ($file->isDir()) {
                // only succeeds for directories that have become empty
                @rmdir($path);
            } elseif ($file->getMTime() < $olderThan->getTimestamp() && File::delete($path)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Returns the size and the number of mails of the archive.
     *
     * @internal
     *
     * @return array{size: int, fileCount: int}
     */
    public static function getStats(): array
    {
        $folder = self::folder();

        if (!is_dir($folder)) {
            return ['size' => 0, 'fileCount' => 0];
        }

        $size = 0;
        $fileCount = 0;

        foreach (Finder::factory($folder)->recursive()->filesOnly() as $file) {
            $size += $file->getSize();
            if ('eml' === pathinfo($file->getFilename(), PATHINFO_EXTENSION)) {
                ++$fileCount;
            }
        }

        return ['size' => $size, 'fileCount' => $fileCount];
    }

    /**
     * Returns the paths of the most recently archived mails, newest first.
     *
     * @internal
     *
     * @return list<string>
     */
    public static function getRecentFiles(int $limit = 10): array
    {
        $folder = self::folder();

        if (!is_dir($folder)) {
            return [];
        }

        $files = [];
        foreach (Finder::factory($folder)->recursive()->filesOnly() as $path => $file) {
            if ('eml' === pathinfo($file->getFilename(), PATHINFO_EXTENSION)) {
                $files[] = $path;
            }
        }

        usort($files, static fn (string $a, string $b): int => (int) filemtime($b) <=> (int) filemtime($a));

        return array_slice($files, 0, $limit);
    }

    /**
     * Parses the subject and the recipient from the headers of the archived mail.
     *
     * @internal
     *
     * @return array{subject: string, recipient: string, size: int, mtime: int}
     */
    public static function parseHeaders(string $file): array
    {
        $head = explode("\n\n", str_replace("\r\n", "\n", File::get($file, '')), 2)[0];
        /** @var array<string, string|list<string>> $headers headers occurring several times are returned as list */
        $headers = iconv_mime_decode_headers($head, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') ?: [];
        $headers = array_change_key_case($headers);

        $header = static function (string $name) use ($headers): ?string {
            $value = $headers[$name] ?? '';
            $value = trim(is_array($value) ? $value[0] : $value);

            return '' === $value ? null : $value;
        };

        $subject = $header('subject') ?? I18n::msg('mailer_archive_no_subject');
        $subject = mb_strlen($subject) > 50 ? mb_substr($subject, 0, 50) . '...' : $subject;

        $recipient = $header('to') ?? $header('x-envelope-to') ?? I18n::msg('mailer_archive_no_recipient');
        $recipient = mb_strlen($recipient) > 30 ? mb_substr($recipient, 0, 30) . '...' : $recipient;

        return [
            'subject' => $subject,
            'recipient' => $recipient,
            'size' => (int) filesize($file),
            'mtime' => (int) filemtime($file),
        ];
    }
}
