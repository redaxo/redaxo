<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Override;
use Redaxo\Core\Form\ChoiceProvider;

/** @internal */
final readonly class SizeChoices implements ChoiceProvider
{
    /** @return array<int, string> */
    #[Override]
    public function choices(): array
    {
        return [1 => 'Small', 2 => 'Large'];
    }
}
