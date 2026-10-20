<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Fieldset;

/** @internal */
final class Fieldsets
{
    #[Text('A')]
    public string $a = '';

    #[Text('B')]
    #[Fieldset('Group 1')]
    public string $b = '';

    #[Text('C')]
    public string $c = '';

    #[Text('D')]
    #[Fieldset('Group 2')]
    public string $d = '';
}
