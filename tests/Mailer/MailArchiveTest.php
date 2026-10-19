<?php

namespace Redaxo\Core\Tests\Mailer;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Mailer\MailArchive;

/** @internal */
final class MailArchiveTest extends TestCase
{
    private string $dir;

    #[Override]
    protected function setUp(): void
    {
        $this->dir = MailArchive::folder() . '/1999';
    }

    #[Override]
    protected function tearDown(): void
    {
        Dir::delete($this->dir);
    }

    public function testPurge(): void
    {
        // dates far in the past, so that real archived mails of the instance are not touched
        File::put($this->dir . '/01/old.eml', '');
        touch($this->dir . '/01/old.eml', new DateTimeImmutable('1999-01-01')->getTimestamp());
        File::put($this->dir . '/02/old.eml', '');
        touch($this->dir . '/02/old.eml', new DateTimeImmutable('1999-02-01')->getTimestamp());
        File::put($this->dir . '/02/new.eml', '');
        touch($this->dir . '/02/new.eml', new DateTimeImmutable('1999-03-01')->getTimestamp());

        self::assertSame(2, MailArchive::purge(new DateTimeImmutable('1999-02-15')));

        self::assertDirectoryDoesNotExist($this->dir . '/01');
        self::assertFileDoesNotExist($this->dir . '/02/old.eml');
        self::assertFileExists($this->dir . '/02/new.eml');
    }
}
