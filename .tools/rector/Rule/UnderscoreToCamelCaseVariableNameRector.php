<?php

declare(strict_types=1);

namespace Redaxo\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\Rector\AbstractRector;
use Redaxo\Rector\Util\UnderscoreCamelCaseExpectedNameResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    public function __construct(
        private readonly ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
        private readonly ParamRenameFactory $paramRenameFactory,
        private readonly ParamRenamer $paramRenamer,
        private readonly UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run($a_b)
                        {
                            $some_value = $a_b;
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run($aB)
                        {
                            $someValue = $aB;
                        }
                    }
                    CODE_SAMPLE,
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if (null === $nodeName) {
            return null;
        }

        if (!str_contains(ltrim($nodeName, '_'), '_')) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($nodeName)) {
            return null;
        }

        $parts = explode('_', $nodeName);
        $uppercasedParts = array_map('ucfirst', $parts);
        $camelCaseName = lcfirst(implode('', $uppercasedParts));
        if ('this' === $camelCaseName) {
            return null;
        }

        if ('' === $camelCaseName) {
            return null;
        }

        if (is_numeric($camelCaseName[0])) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Param) {
            return $this->renameParam($parent);
        }

        if ($this->isName($node, $camelCaseName)) {
            return null;
        }

        $node->name = $camelCaseName;

        return $node;
    }

    private function renameParam(Param $param): ?Variable
    {
        $resolvedExpectedName = $this->underscoreCamelCaseExpectedNameResolver->resolve($param);
        if (null === $resolvedExpectedName) {
            return null;
        }

        $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($param, $resolvedExpectedName);
        if (!$paramRename instanceof ParamRename) {
            return null;
        }

        $renamedParam = $this->paramRenamer->rename($paramRename);
        if (!$renamedParam instanceof Param) {
            return null;
        }

        if (!$renamedParam->var instanceof Variable) {
            return null;
        }

        return $renamedParam->var;
    }
}
