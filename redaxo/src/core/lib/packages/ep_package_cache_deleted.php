<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * @extends ExtensionPoint<Addon>
 */
final class rex_extension_point_package_cache_deleted extends ExtensionPoint
{
    public const string NAME = 'PACKAGE_CACHE_DELETED';

    public function __construct(Addon $package)
    {
        parent::__construct(self::NAME, $package, [], true);
    }
}
