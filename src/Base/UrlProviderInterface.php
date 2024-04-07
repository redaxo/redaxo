<?php

namespace Redaxo\Core\Base;

use Redaxo\Core\Util\Str;

/**
 * Generic interface for classes which provide urls.
 *
 * @psalm-import-type TUrlParams from Str
 */
interface UrlProviderInterface
{
    /**
     * Returns a Url which contains the given parameters.
     *
     * @param TUrlParams $params
     */
    public function getUrl(array $params = []): string;
}
