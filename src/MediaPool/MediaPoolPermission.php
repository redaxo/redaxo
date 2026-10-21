<?php

namespace Redaxo\Core\MediaPool;

use Override;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Security\PermissionChoices;
use Redaxo\Core\Translation\Translatable;

use function count;
use function in_array;

/** @extends ComplexPermission<int> */
final class MediaPoolPermission extends ComplexPermission
{
    /** @param int|null $categoryId `null` for the root level */
    public function hasCategoryPerm(?int $categoryId): bool
    {
        return $this->hasAll() || in_array($categoryId ?? 0, $this->perms);
    }

    public function hasMediaPerm(): bool
    {
        return $this->hasAll() || count($this->perms) > 0;
    }

    #[Override]
    public static function getChoices(): PermissionChoices
    {
        return new PermissionChoices(
            new Translatable('mediafolder'),
            new Translatable('all_mediafolder'),
            new MediaCategoryChoices(checkPerms: false),
        );
    }
}
