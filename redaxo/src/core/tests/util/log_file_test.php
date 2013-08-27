<?php

class rex_log_file_test extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        rex_dir::delete($this->getPath());
    }

    private function getPath($file = '')
    {
        return rex_path::addonData('tests', 'rex_log_file_test/' . $file);
    }

    public function testConstruct()
    {
        new rex_log_file($this->getPath('test1.log'));
    }

    public function testConstructWithMaxFileSize()
    {
        $content = str_repeat('abc', 5);
        $path = $this->getPath('test2.log');
        $path2 = $path . '.2';
        rex_file::put($path, $content);

        new rex_log_file($path, 20);
        $this->assertFileNotExists($path2);
        $this->assertStringEqualsFile($path, $content);

        new rex_log_file($path, 10);
        $this->assertStringEqualsFile($path2, $content);
        $this->assertStringEqualsFile($path, '');
    }

    /**
     * @depends testConstruct
     */
    public function testAdd()
    {
        $path = $this->getPath('test3.log');
        $log = new rex_log_file($path);
        $log->add(['test1a', 'test1b']);
        $log->add(['test2a', 'test2b', 'test2c']);

        $format = <<<'EOF'
%i-%i-%i %i:%i:%i | test1a | test1b
%i-%i-%i %i:%i:%i | test2a | test2b | test2c
EOF;
        $this->assertStringMatchesFormat($format, rex_file::get($path));
    }

    /**
     * @depends testConstruct
     */
    public function testIterator()
    {
        $path = $this->getPath('test4.log');
        $log = new rex_log_file($path);
        $this->assertSame([], iterator_to_array($log));

        rex_file::put($path, <<<'EOF'
2013-08-27 23:07:02 | test1a | test1b
2013-08-27 23:09:43 | test2a | test2b
EOF
        );
        $expected = [
            new rex_log_entry(mktime(23, 9, 43, 8, 27, 2013), ['test2a', 'test2b']),
            new rex_log_entry(mktime(23, 7, 2, 8, 27, 2013), ['test1a', 'test1b'])
        ];
        $this->assertEquals($expected, iterator_to_array($log));

        rex_file::put($path . '.2', <<<'EOF'

2013-08-27 22:19:02 | test3

2013-08-27 22:22:43 | test4

EOF
        );
        $expected[] = new rex_log_entry(mktime(22, 22, 43, 8, 27, 2013), ['test4']);
        $expected[] = new rex_log_entry(mktime(22, 19, 2, 8, 27, 2013), ['test3']);
        $this->assertEquals($expected, iterator_to_array($log));
    }

    public function testDelete()
    {
        $path = $this->getPath('delete.log');
        $path2 = $path . '.2';
        rex_file::put($path, '');
        rex_file::put($path2, '');

        rex_log_file::delete($path);

        $this->assertFileNotExists($path);
        $this->assertFileNotExists($path2);
    }
}
