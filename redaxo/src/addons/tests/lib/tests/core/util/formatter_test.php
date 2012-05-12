<?php

class rex_formatter_test extends PHPUnit_Framework_TestCase
{
  public function testFormatSprintF()
  {
    $value = 'hallo';
    $format_type = 'sprintf';
    $format = 'X%sX';

    $this->assertEquals(
        'XhalloX',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatDate()
  {
    $value = 1336811080;
    $format_type = 'date';
    $format = 'd.m.Y H:i';

    $this->assertEquals(
        '12.05.2012 10:24',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatStrftime()
  {
    $value = 1336811080;
    $format_type = 'strftime';

    $format = '%d.%m.%Y %H:%M';
    $this->assertEquals(
        '12.05.2012 10:24',
        rex_formatter::format($value, $format_type, $format));

    $format = 'date';
    $this->assertEquals(
        '12. Mai. 2012', // DE Locale by default
        rex_formatter::format($value, $format_type, $format));

    $format = 'datetime';
    $this->assertEquals(
        '12. Mai. 2012 - 10:24h', // DE Locale by default
        rex_formatter::format($value, $format_type, $format));


  }

  public function testFormatNumber()
  {
    $value = 1336811080.23;
    $format_type = 'number';

    $format = array();
    $this->assertEquals(
        '1 336 811 080,23',
        rex_formatter::format($value, $format_type, $format));

    $format = array(
        5, ':', '`'
    );
    $this->assertEquals(
        '1`336`811`080:23000',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatEmail()
  {
    $value = 'dude@example.org';
    $format_type = 'email';

    $format = array(
        'attr' => ' data-haha="foo"',
        'params' => 'ilike=+1',
    );
    $this->assertEquals(
        '<a href="mailto:dude@example.org?ilike=+1" data-haha="foo">dude@example.org</a>',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatUrl()
  {
    $value = 'http://example.org';
    $format_type = 'url';

    $format = array(
        'attr' => ' data-haha="foo"',
        'params' => 'ilike=+1',
    );
    $this->assertEquals(
        '<a href="http://example.org?ilike=+1" data-haha="foo">http://example.org</a>',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatTruncate()
  {
    $value = 'very loooooong text lala';
    $format_type = 'truncate';

    $format = array(
        'length' => 10,
        'etc' => ' usw.',
        'break_words' => true,
    );
    $this->assertEquals(
        'very  usw.',
        rex_formatter::format($value, $format_type, $format));

    // XXX hmm seems not to be correct
    $format = array(
        'length' => 10,
        'etc' => ' usw.',
        'break_words' => false,
    );
    $this->assertEquals(
        'very usw.',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatNl2br()
  {
    $value = "very\nloooooong\ntext lala";
    $format_type = 'nl2br';

    $format = array();
    $this->assertEquals(
        "very<br />\nloooooong<br />\ntext lala",
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatCustom()
  {
    $value = 77;
    $format_type = 'custom';

    $format = 'octdec';
    $this->assertEquals(
        63,
        rex_formatter::format($value, $format_type, $format));

    $format = array(
        function($params){
          return $params['subject'] .' '. $params['some'];
        },
        array('some' => 'more params'),
    );

    $this->assertEquals(
        '77 more params',
        rex_formatter::format($value, $format_type, $format));
  }

  public function testFormatBytes()
  {
    $value = 1000;
    $format_type = 'bytes';

    $format = null;
    $this->assertEquals(
        '1 000,00 B',
        rex_formatter::format($value, $format_type, $format));

    $format = null;
    $this->assertEquals(
        '976,56 KiB',
        rex_formatter::format($value*1000, $format_type, $format));

    $format = null;
    $this->assertEquals(
        '953,67 MiB',
        rex_formatter::format($value*1000*1000, $format_type, $format));

    $format = null;
    $this->assertEquals(
        '931,32 GiB',
        rex_formatter::format($value*1000*1000*1000, $format_type, $format));

    $format = null;
    $this->assertEquals(
        '909,49 TiB',
        rex_formatter::format($value*1000*1000*1000*1000, $format_type, $format));

    $format = null;
    $this->assertEquals(
        '888,18 PiB',
        rex_formatter::format($value*1000*1000*1000*1000*1000, $format_type, $format));

    $format = array(5); // number of signs behind comma
    $this->assertEquals(
        '953,67432 MiB',
        rex_formatter::format($value*1000*1000, $format_type, $format));
  }
}
