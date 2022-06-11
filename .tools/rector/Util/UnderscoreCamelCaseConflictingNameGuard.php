<?php

declare(strict_types=1);

namespace Redaxo\Rector\Util;

use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
use function in_array;

final class UnderscoreCamelCaseConflictingNameGuard
{
    /** @var UnderscoreCamelCaseExpectedNameResolver */
    private $underscoreCamelCaseExpectedNameResolver;

    /** @var NodeNameResolver */
    private $nodeNameResolver;

    /** @var ArrayFilter */
    private $arrayFilter;

    public function __construct(
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver,
        NodeNameResolver $nodeNameResolver,
        ArrayFilter $arrayFilter
    ) {
        $this->underscoreCamelCaseExpectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }

    public function isConflicting(PropertyRename $propertyRename): bool
    {
        $conflictingPropertyNames = $this->resolve($propertyRename->getClassLike());
        return in_array($propertyRename->getExpectedName(), $conflictingPropertyNames, true);
    }

    /**
     * @return string[]
     */
    public function resolve(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->underscoreCamelCaseExpectedNameResolver->resolve($property);
            if (null === $expectedName) {
                // fallback to existing name
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
