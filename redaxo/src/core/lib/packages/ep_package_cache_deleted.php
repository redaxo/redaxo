<?php

/**
 * @package redaxo\core\packages
 *
 * @extends rex_extension_point<rex_package>
 */
final class rex_extension_point_package_cache_deleted extends rex_extension_point
{
    public const NAME = 'PACKAGE_CACHE_DELETED';

    public function __construct(rex_package $package)
    {
        parent::__construct(self::NAME, $package, [], true);
    }
}
