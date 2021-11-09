<?php

declare(strict_types=1);

namespace Redaxo\Rector\Util;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
use function in_array;

final class UnderscoreCamelCaseConflictingNameGuard implements ConflictingNameGuardInterface
{
    /** @var UnderscoreCamelCaseExpectedNameResolver */
    private $expectedNameResolver;

    /** @var NodeNameResolver */
    private $nodeNameResolver;

    /** @var ArrayFilter */
    private $arrayFilter;

    public function __construct(UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver, NodeNameResolver $nodeNameResolver, ArrayFilter $arrayFilter)
    {
        $this->expectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }

    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting($renameValueObject): bool
    {
        $conflictingPropertyNames = $this->resolve($renameValueObject->getClassLike());
        return in_array($renameValueObject->getExpectedName(), $conflictingPropertyNames, true);
    }

    /**
     * @param ClassLike $node
     * @return string[]
     */
    private function resolve(Node $node): array
    {
        $expectedNames = [];
        foreach ($node->getProperties() as $property) {
            $expectedName = $this->expectedNameResolver->resolve($property);
            if (null === $expectedName) {
                /** @var string $expectedName */
                $expectedName = $this->nodeNameResolver->getName($property);
            }

            $expectedNames[] = $expectedName;
        }

        return $this->arrayFilter->filterWithAtLeastTwoOccurences($expectedNames);
    }
}
