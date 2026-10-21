<?php

namespace Redaxo\Core\Tests\Mailer;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Mailer\MailArchive;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

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

    /** @param array{subject: string, recipient: string} $expected */
    #[DataProvider('provideParseHeaders')]
    public function testParseHeaders(array $expected, string $headers): void
    {
        File::put($this->dir . '/01/mail.eml', $headers . "\r\n\r\nBody\r\nTo: body@example.org");

        $result = MailArchive::parseHeaders($this->dir . '/01/mail.eml');

        self::assertSame($expected, ['subject' => $result['subject'], 'recipient' => $result['recipient']]);
    }

    /** @return iterable<string, array{array{subject: string, recipient: string}, string}> */
    public static function provideParseHeaders(): iterable
    {
        yield 'plain' => [
            ['subject' => 'Test', 'recipient' => 'to@example.org'],
            "X-Envelope-To: to@example.org, bcc@example.org\r\nTo: to@example.org\r\nSubject: Test",
        ];
        yield 'bcc only' => [
            ['subject' => 'Test', 'recipient' => 'bcc@example.org'],
            "X-Envelope-To: bcc@example.org\r\nSubject: Test",
        ];
        yield 'none' => [
            ['subject' => 'No subject', 'recipient' => 'No recipient'],
            'From: from@example.org',
        ];
        yield 'folded and encoded' => [
            ['subject' => 'Grüße aus Köln', 'recipient' => 'Änne Müller <anna@example.org>'],
            "Subject: =?utf-8?Q?Gr=C3=BC=C3=9Fe_aus?=\r\n =?utf-8?Q?_K=C3=B6ln?=\r\nTo: =?utf-8?Q?=C3=84nne_M=C3=BCller?= <anna@example.org>",
        ];
        $email = new Email()
            ->from('from@example.org')
            ->to(new Address('anna@example.org', 'Änne Müller'))
            ->subject('Grüße aus Köln – Prüfung äöüß € ✓')
            ->text('Body');
        yield 'symfony' => [
            ['subject' => 'Grüße aus Köln – Prüfung äöüß € ✓', 'recipient' => 'Änne Müller <anna@example.org>'],
            explode("\r\n\r\n", $email->toString(), 2)[0],
        ];
        yield 'repeated header' => [
            ['subject' => 'Test', 'recipient' => 'first@example.org'],
            "To: first@example.org\r\nTo: second@example.org\r\nSubject: Test",
        ];
        yield 'lf line endings' => [
            ['subject' => 'Test', 'recipient' => 'to@example.org'],
            "To: to@example.org\nSubject: Test",
        ];
    }
}
