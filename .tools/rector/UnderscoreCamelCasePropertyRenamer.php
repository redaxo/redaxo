<?php

declare(strict_types=1);

namespace Redaxo\Rector;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\PropertyRenamer\PropertyRenamer;
use Rector\Naming\ValueObject\PropertyRename;

final class UnderscoreCamelCasePropertyRenamer
{
    /** @var UnderscoreCamelCaseConflictingNameGuard */
    private $conflictingPropertyNameGuard;

    /** @var PropertyRenamer */
    private $propertyRenamer;

    public function __construct(UnderscoreCamelCaseConflictingNameGuard $underscoreCamelCaseConflictingNameGuard, PropertyRenamer $propertyRenamer)
    {
        $this->conflictingPropertyNameGuard = $underscoreCamelCaseConflictingNameGuard;
        $this->propertyRenamer = $propertyRenamer;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->conflictingPropertyNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return $this->propertyRenamer->rename($propertyRename);
    }
}
