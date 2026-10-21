<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Redaxo\Core\Form\Field\Text;

final class CollectionTag
{
    #[Text('Name')]
    public string $name = '';
}
