<?php

use PHPUnit\Framework\TestCase;

abstract class rex_var_test_base extends TestCase
{
    protected function getParseOutput($content): string
    {
        return rex_file::getOutput(rex_stream::factory('rex-var-test', rex_var::parse($content)));
    }

    protected function assertParseOutputEquals($expected, $content, $msg = 'Parsed content has not expected output.'): void
    {
        static::assertEquals($expected, $this->getParseOutput($content), $msg);
    }
}
