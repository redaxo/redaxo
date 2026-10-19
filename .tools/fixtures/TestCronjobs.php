<?php

namespace Redaxo\Core\Fixtures;

use DateTimeImmutable;
use Redaxo\Core\Content\ArticleSliceHistory;
use Redaxo\Core\Cronjob\AsCronjob;
use Redaxo\Core\Mailer\MailArchive;

final class TestCronjobs
{
    #[AsCronjob('article_history_cleanup', '30 2 * * *')]
    public static function clearArticleHistory(): void
    {
        ArticleSliceHistory::clearHistoryByDate(new DateTimeImmutable('-6 months'));
    }

    #[AsCronjob('mail_archive_cleanup', '@daily')]
    public static function purgeMailArchive(): string
    {
        return MailArchive::purge(new DateTimeImmutable('-30 days')) . ' mails deleted';
    }
}
