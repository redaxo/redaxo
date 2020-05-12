<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_formatter_test extends TestCase
{
    public function testDate()
    {
        $format = 'd.m.Y H:i';

        static::assertEquals(
            '12.05.2012 10:24',
            rex_formatter::date(1336811080, $format)
        );
        static::assertEquals(
            '27.06.2016 21:40',
            rex_formatter::date('2016-06-27 21:40:00', $format)
        );
    }

    public function testStrftime()
    {
        $oldLocale = rex_i18n::getLocale();
        rex_i18n::setLocale('en_gb');

        $value = 1336811080;

        $format = '%d.%m.%Y %H:%M';
        static::assertEquals(
            '12.05.2012 10:24',
            rex_formatter::strftime($value, $format)
        );

        static::assertEquals(
            '27.06.2016 21:40',
            rex_formatter::strftime('2016-06-27 21:40:00', $format)
        );

        $format = 'date';
        static::assertEquals(
            '12 May 2012',
            rex_formatter::strftime($value, $format)
        );

        $format = 'datetime';
        static::assertEquals(
            '12 May 2012, 10:24',
            rex_formatter::strftime($value, $format)
        );

        rex_i18n::setLocale($oldLocale);
    }

    public function testNumber()
    {
        $value = 1336811080.23;

        $format = [];
        static::assertEquals(
            '1 336 811 080,23',
            rex_formatter::number($value, $format)
        );

        $format = [5, ':', '`'];
        static::assertEquals(
            '1`336`811`080:23000',
            rex_formatter::number($value, $format)
        );
    }

    public function testBytes()
    {
        $value = 1000;

        static::assertEquals(
            '1 000,00 B',
            rex_formatter::bytes($value)
        );

        static::assertEquals(
            '976,56 KiB',
            rex_formatter::bytes($value * 1000)
        );

        static::assertEquals(
            '953,67 MiB',
            rex_formatter::bytes($value * 1000 * 1000)
        );

        static::assertEquals(
            '931,32 GiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000)
        );

        static::assertEquals(
            '909,49 TiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000 * 1000)
        );

        static::assertEquals(
            '888,18 PiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000 * 1000 * 1000)
        );

        $format = [5]; // number of signs behind comma
        static::assertEquals(
            '953,67432 MiB',
            rex_formatter::bytes($value * 1000 * 1000, $format)
        );
    }

    public function testSprintf()
    {
        $value = 'hallo';
        $format = 'X%sX';

        static::assertEquals(
            'XhalloX',
            rex_formatter::sprintf($value, $format)
        );
    }

    public function testNl2br()
    {
        $value = "very\nloooooong\ntext lala";

        static::assertEquals(
            "very<br />\nloooooong<br />\ntext lala",
            rex_formatter::nl2br($value)
        );
    }

    public function testTruncate()
    {
        $value = 'very loooooong text lala';

        $format = [
            'length' => 10,
            'etc' => ' usw.',
            'break_words' => true,
        ];
        static::assertEquals(
            'very  usw.',
            rex_formatter::truncate($value, $format)
        );

        // XXX hmm seems not to be correct
        $format = [
            'length' => 10,
            'etc' => ' usw.',
            'break_words' => false,
        ];
        static::assertEquals(
            'very usw.',
            rex_formatter::truncate($value, $format)
        );
    }

    public function testVersion()
    {
        $value = '5.1.2-alpha1';

        static::assertEquals(
            '5_1',
            rex_formatter::version($value, '%s_%s')
        );

        static::assertEquals(
            '2-1-5',
            rex_formatter::version($value, '%3$s-%2$s-%1$s')
        );
    }

    public function testUrl()
    {
        $value = 'http://example.org';

        $format = [
            'attr' => ' data-haha="foo"',
            'params' => 'ilike=+1',
        ];
        static::assertEquals(
            '<a href="http://example.org?ilike=+1" data-haha="foo">http://example.org</a>',
            rex_formatter::url($value, $format)
        );
    }

    public function testEmail()
    {
        $value = 'dude@example.org';

        $format = [
            'attr' => ' data-haha="foo"',
            'params' => 'ilike=+1',
        ];
        static::assertEquals(
            '<a href="mailto:dude@example.org?ilike=+1" data-haha="foo">dude@example.org</a>',
            rex_formatter::email($value, $format)
        );
    }

    public function testCustom()
    {
        $value = 77;

        $format = 'octdec';
        static::assertEquals(
            63,
            rex_formatter::custom($value, $format)
        );

        $format = [
            static function ($params) {
                return $params['subject'] . ' ' . $params['some'];
            },
            ['some' => 'more params'],
        ];

        static::assertEquals(
            '77 more params',
            rex_formatter::custom($value, $format)
        );
    }
}
