<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function in_array;

final class RexFunctionsDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), ['rex_get', 'rex_post', 'rex_request', 'rex_server', 'rex_session', 'rex_cookie', 'rex_files', 'rex_env'], true);
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        $args = $functionCall->getArgs();

        if (count($args) < 2) {
            return null;
        }

        $defaultArgType = null;
        if (count($args) >= 3) {
            $defaultArgType = $scope->getType($args[2]->value);
        }

        $typeString = $scope->getType($args[1]->value);
        if ($typeString instanceof ConstantStringType) {
            $resolvedType = $this->resolveTypeFromString($typeString->getValue());

            if (null !== $resolvedType) {
                if ($defaultArgType) {
                    return TypeCombinator::union($resolvedType, $defaultArgType);
                }
                return $resolvedType;
            }
        }

        return null;
    }

    private function resolveTypeFromString(string $vartype): ?Type
    {
        if (in_array($vartype, [
            'bool',
            'boolean',
        ], true)) {
            return new BooleanType();
        }

        if (in_array($vartype, [
            'int',
            'integer',
        ], true)) {
            return new IntegerType();
        }

        if (in_array($vartype, [
            'double',
            'float',
            'real',
        ], true)) {
            return new FloatType();
        }

        if (in_array($vartype, [
            'string',
        ], true)) {
            return new StringType();
        }

        if (in_array($vartype, [
            'object',
        ], true)) {
            return new ObjectWithoutClassType();
        }

        if (in_array($vartype, [
            'array',
        ], true)) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if (preg_match('/^array\[(.+)\]$/', $vartype, $match)) {
            $valueType = $this->resolveTypeFromString($match[1]);

            if (null === $valueType) {
                throw new ShouldNotHappenException();
            }

            return new ArrayType(
                new MixedType(),
                $valueType
            );
        }

        return null;
    }
}
