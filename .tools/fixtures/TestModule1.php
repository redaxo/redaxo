<?php

namespace Redaxo\Core\Fixtures;

use Override;
use Redaxo\Core\Content\AsModule;
use Redaxo\Core\Content\Module;

#[AsModule('testmodule1', 'Test Module 1')]
final class TestModule1 extends Module
{
    #[Override]
    public function input(): string
    {
        return 'input';
    }

    #[Override]
    public function output(): string
    {
        return 'output';
    }
}
