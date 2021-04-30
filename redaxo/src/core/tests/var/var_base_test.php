<?php

use PHPUnit\Framework\TestCase;

abstract class rex_var_base_test extends TestCase
{
    protected function getParseOutput($content)
    {
        return rex_file::getOutput(rex_stream::factory('rex-var-test', rex_var::parse($content)));
    }

    protected function assertParseOutputEquals($expected, $content, $msg = 'Parsed content has not expected output.')
    {
        static::assertEquals($expected, $this->getParseOutput($content), $msg);
    }
}
