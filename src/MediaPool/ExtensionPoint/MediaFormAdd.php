<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Extra fields in the media add form.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaFormAdd extends ExtensionPoint
{
    public function __construct()
    {
        parent::__construct(self::class, '');
    }
}
