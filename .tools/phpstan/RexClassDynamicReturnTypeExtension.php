<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use rex;
use function count;
use function in_array;

final class RexClassDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['gettable', 'gettableprefix'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $name = strtolower($methodReflection->getName());

        if ('gettableprefix' === $name) {
            return new ConstantStringType('rex_');
        }

        $args = $methodCall->getArgs();
        if (count($args) < 1) {
            return null;
        }

        $tableName = $scope->getType($args[0]->value);

        if ($tableName instanceof ConstantStringType) {
            return new ConstantStringType('rex_'. $tableName->getValue());
        }

        return null;
    }
}
