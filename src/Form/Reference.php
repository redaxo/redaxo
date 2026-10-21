<?php

namespace Redaxo\Core\Form;

/**
 * An item a field value refers to, e.g. a medium or an article, see {@see Field\ReferencesItems}.
 *
 * Used to find where an item is in use, e.g. before it is deleted.
 */
final readonly class Reference
{
    public const string MEDIA = 'media';
    public const string ARTICLE = 'article';

    public function __construct(
        /** Kind of the item, e.g. {@see self::MEDIA}; addons may use own types. */
        public string $type,
        /** Identifier of the item, e.g. the file name of a medium. */
        public string $target,
    ) {}

    public static function media(string $fileName): self
    {
        return new self(self::MEDIA, $fileName);
    }

    public static function article(int $id): self
    {
        return new self(self::ARTICLE, (string) $id);
    }
}
