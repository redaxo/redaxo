<?php
namespace Psalm\Internal\Provider\ReturnTypeProvider;

use function array_merge;
use function array_values;
use PhpParser;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Internal\Type\TypeCombination;
use Psalm\StatementsSource;
use Psalm\Type;

class ArrayMergeReturnTypeProvider implements \Psalm\Plugin\Hook\FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds() : array
    {
        return ['array_merge', 'array_replace'];
    }

    /**
     * @param  array<PhpParser\Node\Arg>    $call_args
     */
    public static function getFunctionReturnType(
        StatementsSource $statements_source,
        string $function_id,
        array $call_args,
        Context $context,
        CodeLocation $code_location
    ) : Type\Union {
        if (!$statements_source instanceof \Psalm\Internal\Analyzer\StatementsAnalyzer) {
            return Type::getMixed();
        }

        $inner_value_types = [];
        $inner_key_types = [];

        $codebase = $statements_source->getCodebase();

        $generic_properties = [];
        $all_int_offsets = true;
        $all_nonempty_lists = true;
        $any_nonempty = false;

        foreach ($call_args as $call_arg) {
            if (!($call_arg_type = $statements_source->node_data->getType($call_arg->value))) {
                return Type::getArray();
            }

            foreach ($call_arg_type->getAtomicTypes() as $type_part) {
                if ($call_arg->unpack) {
                    if (!$type_part instanceof Type\Atomic\TArray) {
                        if ($type_part instanceof Type\Atomic\ObjectLike) {
                            $type_part_value_type = $type_part->getGenericValueType();
                        } elseif ($type_part instanceof Type\Atomic\TList) {
                            $type_part_value_type = $type_part->type_param;
                        } else {
                            return Type::getArray();
                        }
                    } else {
                        $type_part_value_type = $type_part->type_params[1];
                    }

                    $unpacked_type_parts = [];

                    foreach ($type_part_value_type->getAtomicTypes() as $value_type_part) {
                        $unpacked_type_parts[] = $value_type_part;
                    }
                } else {
                    $unpacked_type_parts = [$type_part];
                }

                foreach ($unpacked_type_parts as $unpacked_type_part) {
                    if (!$unpacked_type_part instanceof Type\Atomic\TArray) {
                        if (($unpacked_type_part instanceof Type\Atomic\TFalse
                                && $call_arg_type->ignore_falsable_issues)
                            || ($unpacked_type_part instanceof Type\Atomic\TNull
                                && $call_arg_type->ignore_nullable_issues)
                        ) {
                            continue;
                        }

                        if ($unpacked_type_part instanceof Type\Atomic\ObjectLike) {
                            if ($generic_properties !== null) {
                                $generic_properties = array_merge(
                                    $generic_properties,
                                    $unpacked_type_part->properties
                                );
                            }

                            if (!$unpacked_type_part->is_list) {
                                $all_nonempty_lists = false;
                            }

                            if ($unpacked_type_part->sealed) {
                                $any_nonempty = true;
                            }

                            $unpacked_type_part = $unpacked_type_part->getGenericArrayType();
                        } elseif ($unpacked_type_part instanceof Type\Atomic\TList) {
                            $generic_properties = null;

                            if (!$unpacked_type_part instanceof Type\Atomic\TNonEmptyList) {
                                $all_nonempty_lists = false;
                            } else {
                                $any_nonempty = true;
                            }
                        } else {
                            if ($unpacked_type_part instanceof Type\Atomic\TMixed
                                && $unpacked_type_part->from_loop_isset
                            ) {
                                $unpacked_type_part = new Type\Atomic\TArray([
                                    Type::getArrayKey(),
                                    Type::getMixed(true),
                                ]);
                            } else {
                                return Type::getArray();
                            }
                        }
                    } elseif (!$unpacked_type_part->type_params[0]->isEmpty()) {
                        $generic_properties = null;
                        $all_nonempty_lists = false;
                    }

                    if ($unpacked_type_part instanceof Type\Atomic\TArray) {
                        if ($unpacked_type_part->type_params[1]->isEmpty()) {
                            continue;
                        }

                        if (!$unpacked_type_part->type_params[0]->isInt()) {
                            $all_int_offsets = false;
                        }

                        if ($unpacked_type_part instanceof Type\Atomic\TNonEmptyArray) {
                            $any_nonempty = true;
                        }
                    }

                    $inner_key_types = array_merge(
                        $inner_key_types,
                        $unpacked_type_part instanceof Type\Atomic\TList
                            ? [new Type\Atomic\TInt()]
                            : array_values($unpacked_type_part->type_params[0]->getAtomicTypes())
                    );
                    $inner_value_types = array_merge(
                        $inner_value_types,
                        $unpacked_type_part instanceof Type\Atomic\TList
                            ? array_values($unpacked_type_part->type_param->getAtomicTypes())
                            : array_values($unpacked_type_part->type_params[1]->getAtomicTypes())
                    );
                }
            }
        }

        if ($generic_properties) {
            $objectlike = new Type\Atomic\ObjectLike($generic_properties);

            if ($all_nonempty_lists) {
                $objectlike->is_list = true;
            }

            return new Type\Union([$objectlike]);
        }

        if ($inner_value_types) {
            $inner_value_type = TypeCombination::combineTypes($inner_value_types, $codebase, true);

            if ($all_int_offsets) {
                if ($any_nonempty) {
                    return new Type\Union([
                        new Type\Atomic\TNonEmptyList($inner_value_type),
                    ]);
                }

                return new Type\Union([
                    new Type\Atomic\TList($inner_value_type),
                ]);
            }

            $inner_key_type = $inner_key_types
                ? TypeCombination::combineTypes($inner_key_types, $codebase, true)
                : Type::getArrayKey();

            if ($any_nonempty) {
                return new Type\Union([
                    new Type\Atomic\TNonEmptyArray([
                        $inner_key_type,
                        $inner_value_type,
                    ]),
                ]);
            }

            return new Type\Union([
                new Type\Atomic\TArray([
                    $inner_key_type,
                    $inner_value_type,
                ]),
            ]);
        }

        return Type::getArray();
    }
}
