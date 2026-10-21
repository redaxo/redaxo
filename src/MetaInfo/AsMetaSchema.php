<?php

namespace Redaxo\Core\MetaInfo;

use Attribute;

/**
 * Marks a {@see MetaSchema} subclass and binds it to an entity.
 *
 * An entity may have several schemas.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class AsMetaSchema
{
    public function __construct(
        public MetaEntity $entity,
    ) {}
}
