<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Util\Str;

/**
 * URL of an article; extensions return their URL, `null` keeps the default URL of the frontend controller.
 *
 * @psalm-import-type TUrlParams from Str
 * @extends ExtensionPoint<string|null>
 */
final class ArticleUrl extends ExtensionPoint
{
    /** @param TUrlParams $params */
    public function __construct(
        public readonly int $articleId,
        public readonly int $languageId,
        public readonly array $params,
    ) {
        parent::__construct(self::class, null);
    }
}
