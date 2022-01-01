<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use rex_sql;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class RexSqlClassDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['escapeidentifier', 'escapelikewildcards'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();
        if (\count($args) < 1) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }

        $arg1 = $scope->getType($args[0]->value);
        if (!$arg1 instanceof ConstantStringType) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }

        $value = $arg1->getValue();
        $sql = rex_sql::factory();
        $name = strtolower($methodReflection->getName());

        if ($name === "escapeidentifier") {
            return new ConstantStringType($sql->escapeIdentifier($value));
        }

        return new ConstantStringType($sql->escapeLikeWildcards($value));
    }
}
