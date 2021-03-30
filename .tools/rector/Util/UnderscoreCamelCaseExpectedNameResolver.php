<?php

declare(strict_types=1);

namespace Redaxo\Rector\Util;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;

final class UnderscoreCamelCaseExpectedNameResolver
{
    /** @var NodeNameResolver */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Param|Property $node
     */
    public function resolve(Node $node): ?string
    {
        $currentName = $this->nodeNameResolver->getName($node);
        if (null === $currentName) {
            return null;
        }

        $parts = explode('_', $currentName);
        $uppercasedParts = array_map('ucfirst', $parts);
        return lcfirst(implode('', $uppercasedParts));
    }
}
