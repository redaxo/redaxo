<?php

namespace mheap\GithubActionsReporter;

use PHPUnit\Util\Filter;
use PHPUnit\Framework\Test;
use PHPUnit\Runner\Version;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Framework\TestFailure;

class Printer extends ResultPrinter
{
    protected $currentType = null;

    protected function printHeader(): void
    {
    }

    protected function writeProgress(string $progress): void
    {
    }

    protected function printFooter(TestResult $result): void
    {
    }

    protected function printDefects(array $defects, string $type): void
    {
        $this->currentType = $type;

        $i=0;
        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
    }

    protected function printDefectHeader(TestFailure $defect, int $count): void
    {
    }

    protected function printDefectTrace(TestFailure $defect): void
    {
        $e = $defect->thrownException();

        $firstError = explode(PHP_EOL, (string)$e)[2];
        list($path, $line) = explode(":", $firstError);

        if (!$path) {
            list($path, $line) = $this->getReflectionFromTest($defect->getTestName());
        }

        $message = explode(PHP_EOL, $e->getMessage())[0];

        debug_print_backtrace();
        $this->write("::{$this->getCurrentType()} file={$this->relativePath($path)},line={$line}::{$message}\n");
    }

    protected function getCurrentType() {
        if (in_array($this->currentType, ['error', 'failure'])) {
            return 'error';
        }

        return 'warning';
    }

    protected function relativePath(string $path) {
        return str_replace(getcwd().'/', "", $path);
    }

    protected function getReflectionFromTest(string $name) {
        list($klass, $method) = explode("::", $name);
        $c = new \ReflectionClass($klass);
        $m = $c->getMethod($method);

        return [$m->getFileName(), $m->getStartLine()];
    }
}
