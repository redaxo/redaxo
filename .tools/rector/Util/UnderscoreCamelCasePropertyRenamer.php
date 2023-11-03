<?php

declare(strict_types=1);

namespace Redaxo\Rector\Util;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Naming\PropertyRenamer\PropertyFetchRenamer;
use Rector\Naming\RenameGuard\PropertyRenameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class UnderscoreCamelCasePropertyRenamer
{
    public function __construct(
        private readonly UnderscoreCamelCaseConflictingNameGuard $underscoreCamelCaseConflictingNameGuard,
        private readonly PropertyRenameGuard $propertyRenameGuard,
        private readonly PropertyFetchRenamer $propertyFetchRenamer,
    ) {}

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->underscoreCamelCaseConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        if ($propertyRename->isAlreadyExpectedName()) {
            return null;
        }

        if ($this->propertyRenameGuard->shouldSkip($propertyRename)) {
            return null;
        }

        $onlyPropertyProperty = $propertyRename->getPropertyProperty();
        $onlyPropertyProperty->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);

        return $propertyRename->getProperty();
    }

    private function renamePropertyFetchesInClass(PropertyRename $propertyRename): void
    {
        $this->propertyFetchRenamer->renamePropertyFetchesInClass(
            $propertyRename->getClassLike(),
            $propertyRename->getCurrentName(),
            $propertyRename->getExpectedName(),
        );
    }
}
