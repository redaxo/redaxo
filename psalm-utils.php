<?php

use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\StatementsSource;
use Psalm\Type;

class RexTypeReturnProvider implements \Psalm\Plugin\Hook\MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return ['rex_type'];
    }

    /**
     * @param array<PhpParser\Node\Arg> $call_args
     *
     * @return ?Type\Union
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
    )
    {
        if ('cast' === $method_name_lowercase
            && isset($call_args[1]->value->inferredType)
            && $call_args[1]->value->inferredType->isSingleStringLiteral()
        ) {
            $vartype = (string) $call_args[1]->value->inferredType->getSingleStringLiteral()->value;

            switch ($vartype) {
                case 'bool':
                case 'boolean':
                case 'int':
                case 'integer':
                case 'double':
                case 'float':
                case 'real':
                case 'string':
                case 'object':
                case 'array':
                    return Type::parseString($vartype);
            }
            // dont know..
            return Type::getMixed();
        }
    }
}
