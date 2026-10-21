<?php

namespace Redaxo\Core\Tests\Content\Fixtures;

use Override;
use Redaxo\Core\Content\AsModule;
use Redaxo\Core\Content\Module;

#[AsModule('test_no_fields', 'Test No Fields')]
final class TestNoFieldsModule extends Module
{
    #[Override]
    public function output(): string
    {
        return 'no fields';
    }
}
