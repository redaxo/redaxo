<?php

use Redaxo\Core\Addon\Addon;

/**
 * @extends rex_extension_point<Addon>
 */
final class rex_extension_point_package_cache_deleted extends rex_extension_point
{
    public const string NAME = 'PACKAGE_CACHE_DELETED';

    public function __construct(Addon $package)
    {
        parent::__construct(self::NAME, $package, [], true);
    }
}
