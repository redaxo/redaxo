<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

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
    ): Type {
        $args = $functionCall->getArgs();

        if (count($args) < 2 ) {
            return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }

        $typeString = $scope->getType($args[1]->value);
        if ($typeString instanceof ConstantStringType) {
            $resolvedType = $this->resolveTypeFromString($typeString->getValue());
            if ($resolvedType) {
            }

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
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

        /*
        if (in_array($vartype, [
            'object',
        ], true)) {
            return new ObjectType();
        }
         */

        /*
        if (in_array($vartype, [
            'array',
        ], true)) {
            return new ArrayType();
        }
        */

        /*
        if (preg_match('/^array\[(.+)\]$/', $vartype, $match)) {
            return new Union([new TArray([
                new Union([new TArrayKey()]),
                Type::parseString($match[1]),
            ])]);
        }
        */

        return null;
    }
}
