<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Content\Category;
use Redaxo\Core\MediaPool\MediaCategory;

/**
 * The item whose meta fields are edited, handed to {@see MetaSchema::isFieldAllowed()}.
 *
 * Built once per edited item by the handler.
 */
final readonly class MetaContext
{
    /**
     * @param object|null $subject the item being edited (article/category/media/language, or the database row of it),
     *     or `null` when adding
     * @param Category|null $category Surrounding content category (an article's category / a category's parent);
     *     `null` means root. Set for the Article and Category entities, also available when adding.
     * @param MediaCategory|null $mediaCategory Surrounding media category; `null` means root. Set for the Media entity.
     */
    public function __construct(
        public MetaEntity $entity,
        public ?object $subject = null,
        public ?Category $category = null,
        public ?MediaCategory $mediaCategory = null,
    ) {}
}
