<?php

namespace Redaxo\Core\Language;

use Override;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Security\PermissionChoices;
use Redaxo\Core\Translation\Translatable;

use function count;
use function in_array;

/** @extends ComplexPermission<int> */
final class LanguagePermission extends ComplexPermission
{
    public function hasPerm(int $languageId): bool
    {
        return $this->hasAll() || in_array($languageId, $this->perms);
    }

    public function count(): int
    {
        return $this->hasAll() ? Language::count() : count($this->perms);
    }

    /** @return list<int> */
    public function getLanguageIds(): array
    {
        return $this->hasAll() ? Language::getAllIds() : $this->perms;
    }

    #[Override]
    public static function getChoices(): PermissionChoices
    {
        $choices = array_map(static function (Language $language) {
            return $language->name;
        }, Language::getAll());

        return new PermissionChoices(new Translatable('languages'), new Translatable('all_languages'), $choices);
    }
}
