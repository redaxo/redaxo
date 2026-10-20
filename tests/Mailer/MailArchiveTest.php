<?php

namespace Redaxo\Core\Tests\Mailer;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('provideParseHeadersRecipient')]
    public function testParseHeadersRecipient(string $expected, string $headers): void
    {
        File::put($this->dir . '/01/mail.eml', $headers . "\r\n\r\nBody");

        self::assertSame($expected, MailArchive::parseHeaders($this->dir . '/01/mail.eml')['recipient']);
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideParseHeadersRecipient(): iterable
    {
        yield 'to' => ['to@example.org', "X-Envelope-To: to@example.org, bcc@example.org\r\nTo: to@example.org"];
        yield 'bcc only' => ['bcc@example.org', "X-Envelope-To: bcc@example.org\r\nSubject: Test"];
        yield 'none' => ['No recipient', 'Subject: Test'];
    }
}
