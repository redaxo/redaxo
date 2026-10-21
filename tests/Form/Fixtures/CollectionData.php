<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Redaxo\Core\Form\Field\Collection;

final class CollectionData
{
    /** @var list<CollectionItem> */
    #[Collection(
        'Items',
        item: CollectionItem::class,
        min: 2,
        max: 3,
    )]
    public array $items = [];
}
