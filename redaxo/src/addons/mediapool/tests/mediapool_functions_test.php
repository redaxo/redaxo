<?php

class rex_mediapool_functions_test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideIsAllowedMediaType
     */
    public function testIsAllowedMediaType($expected, $filename, array $args = [])
    {
        require_once rex_path::addon('mediapool', 'functions/function_rex_mediapool.php');

        return $this->assertSame($expected, rex_mediapool_isAllowedMediaType($filename, $args));
    }

    public function provideIsAllowedMediaType()
    {
        return [
            [false, 'foo.bar.php'],
            [false, 'foo.bar.php5'],
            [false, 'foo.bar.php71'],
            [false, 'foo.bar.php_71'],
            [false, 'foo.bar.jsp'],
            [false, '.htaccess'],
            [false, '.htpasswd'],
            [true, 'php_logo.jpg'],
            [true, 'foo.bar.png', ['types' => 'jpg,png,gif']],
            [false, 'foo.bar.txt', ['types' => 'jpg,png,gif']],
            [false, 'foo.bar.php', ['types' => 'jpg,png,gif,php']],
        ];
    }
}
