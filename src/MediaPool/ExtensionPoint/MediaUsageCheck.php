<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;
use Redaxo\Core\Util\UsageList;

/**
 * Dispatched to find the places using the medium, e.g. before it is deleted; extensions add theirs to `$usages`.
 *
 * @extends ExtensionPoint<null>
 */
final class MediaUsageCheck extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
        public readonly UsageList $usages,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
