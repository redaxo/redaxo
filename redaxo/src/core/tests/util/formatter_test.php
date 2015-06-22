<?php

class rex_formatter_test extends PHPUnit_Framework_TestCase
{
    public function testDate()
    {
        $value = 1336811080;
        $format = 'd.m.Y H:i';

        $this->assertEquals(
            '12.05.2012 10:24',
            rex_formatter::date($value, $format)
        );
    }

    public function testStrftime()
    {
        $oldLocale = rex_i18n::getLocale();
        rex_i18n::setLocale('en_gb');

        $value = 1336811080;

        $format = '%d.%m.%Y %H:%M';
        $this->assertEquals(
            '12.05.2012 10:24',
            rex_formatter::strftime($value, $format)
        );

        $format = 'date';
        $this->assertEquals(
            '2012-May-12',
            rex_formatter::strftime($value, $format)
        );

        $format = 'datetime';
        $this->assertEquals(
            '2012-May-12 10:24',
            rex_formatter::strftime($value, $format)
        );

        rex_i18n::setLocale($oldLocale);
    }

    public function testNumber()
    {
        $value = 1336811080.23;

        $format = [];
        $this->assertEquals(
            '1 336 811 080,23',
            rex_formatter::number($value, $format)
        );

        $format = [5, ':', '`'];
        $this->assertEquals(
            '1`336`811`080:23000',
            rex_formatter::number($value, $format)
        );
    }

    public function testBytes()
    {
        $value = 1000;

        $this->assertEquals(
            '1 000,00 B',
            rex_formatter::bytes($value)
        );

        $this->assertEquals(
            '976,56 KiB',
            rex_formatter::bytes($value * 1000)
        );

        $this->assertEquals(
            '953,67 MiB',
            rex_formatter::bytes($value * 1000 * 1000)
        );

        $this->assertEquals(
            '931,32 GiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000)
        );

        $this->assertEquals(
            '909,49 TiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000 * 1000)
        );

        $this->assertEquals(
            '888,18 PiB',
            rex_formatter::bytes($value * 1000 * 1000 * 1000 * 1000 * 1000)
        );

        $format = [5]; // number of signs behind comma
        $this->assertEquals(
            '953,67432 MiB',
            rex_formatter::bytes($value * 1000 * 1000, $format)
        );
    }

    public function testSprintf()
    {
        $value = 'hallo';
        $format = 'X%sX';

        $this->assertEquals(
            'XhalloX',
            rex_formatter::sprintf($value, $format)
        );
    }

    public function testNl2br()
    {
        $value = "very\nloooooong\ntext lala";

        $this->assertEquals(
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
        $this->assertEquals(
            'very  usw.',
            rex_formatter::truncate($value, $format)
        );

        // XXX hmm seems not to be correct
        $format = [
            'length' => 10,
            'etc' => ' usw.',
            'break_words' => false,
        ];
        $this->assertEquals(
            'very usw.',
            rex_formatter::truncate($value, $format)
        );
    }

    public function testVersion()
    {
        $value = '5.1.2-alpha1';

        $this->assertEquals(
            '5_1',
            rex_formatter::version($value, '%s_%s')
        );

        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
            '<a href="mailto:dude@example.org?ilike=+1" data-haha="foo">dude@example.org</a>',
            rex_formatter::email($value, $format)
        );
    }

    public function testCustom()
    {
        $value = 77;

        $format = 'octdec';
        $this->assertEquals(
            63,
            rex_formatter::custom($value, $format)
        );

        $format = [
            function ($params) {
                return $params['subject'] . ' ' . $params['some'];
            },
            ['some' => 'more params'],
        ];

        $this->assertEquals(
            '77 more params',
            rex_formatter::custom($value, $format)
        );
    }
}
