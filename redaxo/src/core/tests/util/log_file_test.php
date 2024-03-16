<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_log_file_test extends TestCase
{
    protected function tearDown(): void
    {
        rex_dir::delete($this->getPath());
    }

    private function getPath(string $file = ''): string
    {
        return rex_path::addonData('tests', 'rex_log_file_test/' . $file);
    }

    public function testConstruct(): void
    {
        $path = $this->getPath('test1.log');
        rex_log_file::factory($path);
        self::assertStringEqualsFile($path, '');
    }

    public function testConstructWithMaxFileSize(): void
    {
        $path = $this->getPath('test2.log');
        $path2 = $path . '.2';

        rex_log_file::factory($path, 20);
        self::assertStringEqualsFile($path, '');
        self::assertFileDoesNotExist($path2);

        $content = str_repeat('abc', 5);
        rex_file::put($path, $content);

        rex_log_file::factory($path, 20);
        self::assertFileDoesNotExist($path2);
        self::assertStringEqualsFile($path, $content);

        rex_log_file::factory($path, 10);
        self::assertStringEqualsFile($path2, $content);
        self::assertStringEqualsFile($path, '');
    }

    #[Depends('testConstruct')]
    public function testAdd(): void
    {
        $path = $this->getPath('test3.log');
        $log = rex_log_file::factory($path);
        $log->add(['test1a', 'test1b']);
        $log->add(['test2a', 'test2b', 'test2c']);

        $format = <<<'EOF'
            %i-%i-%iT%i:%i:%i%i:%i | test1a | test1b
            %i-%i-%iT%i:%i:%i%i:%i | test2a | test2b | test2c
            EOF;
        self::assertStringMatchesFormat($format, rex_file::require($path));
    }

    #[Depends('testConstruct')]
    public function testIterator(): void
    {
        $path = $this->getPath('test4.log');
        $log = rex_log_file::factory($path);
        self::assertSame([], iterator_to_array($log));

        unset($log); // free handles to the underlying file
        rex_file::put($path, <<<'EOF'
            2013-08-27 23:07:02 | test1a | test1b
            2013-08-27 23:09:43 | test2a | test2b
            EOF
        );
        $expected = [
            new rex_log_entry(mktime(23, 9, 43, 8, 27, 2013), ['test2a', 'test2b']),
            new rex_log_entry(mktime(23, 7, 2, 8, 27, 2013), ['test1a', 'test1b']),
        ];
        $log = rex_log_file::factory($path);
        self::assertEquals($expected, iterator_to_array($log));

        unset($log); // free handles to the underlying file
        rex_file::put($path . '.2', <<<'EOF'

            2013-08-27 22:19:02 | test3

            2013-08-27 22:22:43 | test4

            EOF
        );
        $expected[] = new rex_log_entry(mktime(22, 22, 43, 8, 27, 2013), ['test4']);
        $expected[] = new rex_log_entry(mktime(22, 19, 2, 8, 27, 2013), ['test3']);
        $log = rex_log_file::factory($path);
        self::assertEquals($expected, iterator_to_array($log));
    }

    public function testDelete(): void
    {
        $path = $this->getPath('delete.log');
        $path2 = $path . '.2';
        rex_file::put($path, '');
        rex_file::put($path2, '');

        rex_log_file::delete($path);

        self::assertFileDoesNotExist($path);
        self::assertFileDoesNotExist($path2);
    }
}
