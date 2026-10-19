<?php

namespace Redaxo\Core\Cronjob;

use Attribute;

/**
 * Registers the method as a cronjob, which `console cronjob:run` executes according to the schedule.
 *
 * The method must not require any parameters. It may return a message that is written to the cronjob log;
 * failures are signaled by throwing an exception.
 *
 *     final class Cronjobs
 *     {
 *         #[AsCronjob('mail_archive_cleanup', '@daily')]
 *         public static function purgeMailArchive(): string
 *         {
 *             return MailArchive::purge(new DateTimeImmutable('-30 days')) . ' mails deleted';
 *         }
 *     }
 *
 * Non-static methods are only supported on the project class and on addon classes, they are called on the
 * existing project or addon instance.
 *
 * Which cronjobs run on an instance is controlled by the env var `REX_CRONJOBS`.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class AsCronjob
{
    /**
     * @param non-empty-string $name Unique name, used in the env var `REX_CRONJOBS`, in the log and in
     *     `console cronjob:run <name>`. It may contain letters, digits, `_`, `.`, `:` and `-` (not at the beginning).
     * @param non-empty-string $schedule Cron expression (e.g. `0 3 * * *`) or one of the macros `@yearly`,
     *     `@monthly`, `@weekly`, `@daily` and `@hourly`
     */
    public function __construct(
        public string $name,
        public string $schedule,
    ) {}
}
