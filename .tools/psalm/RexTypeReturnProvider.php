<?php

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Plugin\Hook\FunctionReturnTypeProviderInterface;
use Psalm\Plugin\Hook\MethodReturnTypeProviderInterface;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

class RexTypeReturnProvider implements MethodReturnTypeProviderInterface, FunctionReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [rex_type::class, rex_request::class];
    }

    public static function getFunctionIds(): array
    {
        return ['rex_get', 'rex_post', 'rex_request', 'rex_server', 'rex_session', 'rex_cookie', 'rex_files', 'rex_env'];
    }

    /**
     * @param array<PhpParser\Node\Arg> $call_args
     */
    public static function getMethodReturnType(
        StatementsSource $source,
        string $fq_classlike_name,
        string $method_name_lowercase,
        array $call_args,
        Context $context,
        CodeLocation $code_location,
        array $template_type_parameters = null,
        string $called_fq_classlike_name = null,
        string $called_method_name_lowercase = null
    ): ?Union {
        if (rex_type::class === $fq_classlike_name) {
            if ('cast' === $method_name_lowercase) {
                return self::resolveType($call_args[1]->value);
            }

            return null;
        }

        switch ($method_name_lowercase) {
            case 'get':
            case 'post':
            case 'request':
            case 'server':
            case 'session':
            case 'cookie':
            case 'files':
            case 'env':
                if (!isset($call_args[1])) {
                    return null;
                }
                $argType = $call_args[1];
                $argDefault = $call_args[2] ?? null;
                break;
            case 'arraykeycast':
                $argType = $call_args[2];
                $argDefault = $call_args[3] ?? null;
                break;
            default:
                return null;
        }

        return self::resolveType($argType->value, $argDefault->value ?? null);
    }

    /**
     * @param array<PhpParser\Node\Arg> $call_args
     */
    public static function getFunctionReturnType(
        StatementsSource $statements_source,
        string $function_id,
        array $call_args,
        Context $context,
        CodeLocation $code_location
    ): ?Union {
        if (!isset($call_args[1])) {
            return null;
        }

        return self::resolveType($call_args[1]->value, $call_args[2]->value ?? null);
    }

    private static function resolveType(Expr $typeExpr, ?Expr $defaultExpr = null): Union
    {
        if ($typeExpr instanceof String_) {
            $type = self::resolveTypeFromString($typeExpr);
        } elseif ($typeExpr instanceof Array_) {
            $type = self::resolveTypeFromArray($typeExpr);
        } else {
            return Type::getMixed();
        }

        if ($defaultExpr instanceof ConstFetch && 'null' === $defaultExpr->name->parts[0]) {
            $type->addType(new TNull());
        }

        return $type;
    }

    private static function resolveTypeFromString(String_ $string): Union
    {
        $vartype = $string->value;

        if (in_array($vartype, [
            'bool',
            'boolean',
            'int',
            'integer',
            'double',
            'float',
            'real',
            'string',
            'object',
            'array',
        ], true)) {
            return Type::parseString($vartype);
        }

        if (preg_match('/^array\[(.+)\]$/', $vartype, $match)) {
            return new Union([new TArray([
                new Union([new TArrayKey()]),
                Type::parseString($match[1]),
            ])]);
        }

        return Type::getMixed();
    }

    private static function resolveTypeFromArray(Array_ $array): Union
    {
        $fallback = new Union([new TArray([
            new Union([new TString()]),
            new Union([new TMixed()]),
        ])]);

        $types = [];

        foreach ($array->items as $item) {
            if (!$item->value instanceof Array_) {
                return $fallback;
            }

            $subItems = $item->value->items;

            if (!isset($subItems[0])) {
                return $fallback;
            }

            $itemKey = $subItems[0]->value;

            if (!$itemKey instanceof String_) {
                return $fallback;
            }

            $key = $itemKey->value;

            if (!isset($subItems[1])) {
                $type = Type::getMixed();
            } else {
                $type = self::resolveType($subItems[1]->value, $subItems[2]->value ?? null);
            }

            $types[$key] = $type;
        }

        if (!$types) {
            return $fallback;
        }

        return new Union([new TKeyedArray($types)]);
    }
}
