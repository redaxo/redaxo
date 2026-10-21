<?php

namespace Redaxo\Core\Tests\Form\Fixtures;

use Redaxo\Core\Form\Field\Collection;
use Redaxo\Core\Form\Field\Number;
use Redaxo\Core\Form\Field\Text;

final class CollectionItem
{
    #[Text('Title', required: true)]
    public string $title = '';

    #[Number('Item count')]
    public ?int $itemCount = null;

    /** @var list<CollectionTag> */
    #[Collection(
        'Tags',
        item: CollectionTag::class,
    )]
    public array $tags = [];
}
