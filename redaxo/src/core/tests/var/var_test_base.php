<?php

use PHPUnit\Framework\TestCase;

abstract class rex_var_test_base extends TestCase
{
    protected function getParseOutput(string $content): string
    {
        return rex_file::getOutput(rex_stream::factory('rex-var-test', rex_var::parse($content)));
    }

    protected function assertParseOutputEquals(string $expected, string $content, string $msg = 'Parsed content has not expected output.'): void
    {
        static::assertEquals($expected, $this->getParseOutput($content), $msg);
    }
}
