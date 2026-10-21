<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * SQL query of the media list, dispatched for the count query of a paginated list as well; extensions can add
 * parameters for their changes to `$params`.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaListQuery extends ExtensionPoint
{
    /** @param array<string, scalar|null> $params */
    public function __construct(
        string $query,
        public array $params,
        /** Whether it is the query counting the media of a paginated list */
        public readonly bool $count = false,
    ) {
        parent::__construct(self::class, $query);
    }
}
