<?php

namespace Redaxo\Core\MetaInfo;

use Attribute;

/**
 * Marks a meta field whose value is maintained per language.
 *
 * Translated fields are stored in the translation table of the entity (see {@see MetaEntity::translationTable()}),
 * the other fields in the entity table itself.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Translated {}
