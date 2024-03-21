<?php

/**
 * @extends rex_extension_point<rex_addon>
 */
final class rex_extension_point_package_cache_deleted extends rex_extension_point
{
    public const string NAME = 'PACKAGE_CACHE_DELETED';

    public function __construct(rex_addon $package)
    {
        parent::__construct(self::NAME, $package, [], true);
    }
}
