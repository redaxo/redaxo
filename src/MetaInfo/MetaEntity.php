<?php

namespace Redaxo\Core\MetaInfo;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Language\Language;
use Redaxo\Core\MediaPool\Media;

/**
 * The entity a set of meta fields belongs to.
 *
 * Carries the tables the fields of that entity are stored in.
 */
enum MetaEntity
{
    case Article;
    case Category;
    case Media;
    case Language;

    /** @return non-empty-string */
    public function table(): string
    {
        return match ($this) {
            self::Article => 'rex_article',
            self::Category => 'rex_category',
            self::Media => 'rex_media',
            self::Language => 'rex_language',
        };
    }

    /**
     * The table holding the per-language values, or `null` if the entity has no translation table.
     *
     * @return non-empty-string|null
     */
    public function translationTable(): ?string
    {
        return match ($this) {
            self::Article => 'rex_article_translation',
            self::Category => 'rex_category_translation',
            self::Media => 'rex_media_translation',
            self::Language => null,
        };
    }

    /** Whether the given item belongs to this entity, i.e. whether its meta values can be read. */
    public function accepts(object $subject): bool
    {
        return match ($this) {
            self::Article => $subject instanceof Article,
            self::Category => $subject instanceof Category,
            self::Media => $subject instanceof Media,
            self::Language => $subject instanceof Language,
        };
    }
}
