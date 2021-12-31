<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use rex;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class RexClassDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['gettable', 'gettableprefix'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $name = strtolower($methodReflection->getName());

        if ($name === "gettableprefix") {
            return new ConstantStringType('rex_');
        }

        $args = $methodCall->getArgs();
        if (\count($args) < 1) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }

        $tableName = $scope->getType($args[0]->value);

        if ($tableName instanceof ConstantStringType) {
            return new ConstantStringType('rex_'. $tableName->getValue());
        }

        return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
    }
}
