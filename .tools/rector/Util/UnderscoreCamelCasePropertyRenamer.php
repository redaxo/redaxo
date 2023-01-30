<?php

declare(strict_types=1);

namespace Redaxo\Rector\Util;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\PropertyRenamer\PropertyRenamer;
use Rector\Naming\ValueObject\PropertyRename;

final class UnderscoreCamelCasePropertyRenamer
{
    /** @var UnderscoreCamelCaseConflictingNameGuard */
    private $underscoreCamelCaseConflictingNameGuard;

    /** @var PropertyRenamer */
    private $propertyRenamer;

    public function __construct(
        UnderscoreCamelCaseConflictingNameGuard $underscoreCamelCaseConflictingNameGuard,
        PropertyRenamer $propertyRenamer,
    ) {
        $this->underscoreCamelCaseConflictingNameGuard = $underscoreCamelCaseConflictingNameGuard;
        $this->propertyRenamer = $propertyRenamer;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->underscoreCamelCaseConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return $this->propertyRenamer->rename($propertyRename);
    }
}
