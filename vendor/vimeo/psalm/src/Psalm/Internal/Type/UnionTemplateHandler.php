<?php

namespace Psalm\Internal\Type;

use Psalm\Codebase;
use Psalm\Internal\Analyzer\TypeAnalyzer;
use Psalm\Type\Union;
use Psalm\Type\Atomic;
use function array_merge;
use function array_values;
use function count;
use function is_string;
use function reset;
use function strpos;
use function strval;
use function substr;

class UnionTemplateHandler
{
    public static function replaceTemplateTypesWithStandins(
        Union $union_type,
        TemplateResult $template_result,
        ?Codebase $codebase,
        ?Union $input_type,
        ?string $calling_class = null,
        ?string $calling_function = null,
        bool $replace = true,
        bool $add_upper_bound = false,
        int $depth = 0
    ) : Union {
        $atomic_types = [];

        $original_atomic_types = $union_type->getAtomicTypes();

        $had_template = false;

        foreach ($original_atomic_types as $key => $atomic_type) {
            $atomic_types = array_merge(
                $atomic_types,
                self::handleAtomicStandin(
                    $atomic_type,
                    $key,
                    $template_result,
                    $codebase,
                    $input_type,
                    $calling_class,
                    $calling_function,
                    $replace,
                    $add_upper_bound,
                    $depth,
                    count($original_atomic_types) === 1,
                    $union_type->isNullable(),
                    $had_template
                )
            );
        }

        if ($replace) {
            if (array_values($original_atomic_types) === $atomic_types) {
                return $union_type;
            }

            if (!$atomic_types) {
                throw new \UnexpectedValueException('Cannot remove all keys');
            }

            $new_union_type = new Union($atomic_types);
            $new_union_type->ignore_nullable_issues = $union_type->ignore_nullable_issues;
            $new_union_type->ignore_falsable_issues = $union_type->ignore_falsable_issues;
            $new_union_type->possibly_undefined = $union_type->possibly_undefined;

            if ($had_template) {
                $new_union_type->had_template = true;
            }

            return $new_union_type;
        }

        return $union_type;
    }

    /**
     * @return list<Atomic>
     */
    private static function handleAtomicStandin(
        Atomic $atomic_type,
        string $key,
        TemplateResult $template_result,
        ?Codebase $codebase,
        ?Union $input_type,
        ?string $calling_class,
        ?string $calling_function,
        bool $replace,
        bool $add_upper_bound,
        int $depth,
        bool $was_single,
        bool $was_nullable,
        bool &$had_template
    ) : array {
        if ($bracket_pos = strpos($key, '<')) {
            $key = substr($key, 0, $bracket_pos);
        }

        if ($atomic_type instanceof Atomic\TTemplateParam
            && ($param_name_key = strpos($key, '&') ? $key : $atomic_type->param_name)
            && isset($template_result->template_types[$param_name_key][$atomic_type->defining_class])
        ) {
            $a = self::handleTemplateParamStandin(
                $atomic_type,
                $key,
                $input_type,
                $calling_class,
                $calling_function,
                $template_result,
                $codebase,
                $replace,
                $add_upper_bound,
                $depth,
                $was_nullable,
                $had_template
            );

            return $a;
        }

        if ($atomic_type instanceof Atomic\TTemplateParamClass
            && isset($template_result->template_types[$atomic_type->param_name])
        ) {
            if ($replace) {
                return self::handleTemplateParamClassStandin(
                    $atomic_type,
                    $input_type,
                    $template_result,
                    $depth,
                    $was_single
                );
            }
        }

        if ($atomic_type instanceof Atomic\TTemplateIndexedAccess) {
            if ($replace) {
                $atomic_types = [];

                $include_first = true;

                if (isset($template_result->template_types[$atomic_type->array_param_name][$atomic_type->defining_class])
                    && !empty($template_result->generic_params[$atomic_type->offset_param_name])
                ) {
                    $array_template_type
                        = $template_result->template_types[$atomic_type->array_param_name][$atomic_type->defining_class][0];
                    $offset_template_type
                        = array_values(
                            $template_result->generic_params[$atomic_type->offset_param_name]
                        )[0][0];

                    if ($array_template_type->isSingle()
                        && $offset_template_type->isSingle()
                        && !$array_template_type->isMixed()
                        && !$offset_template_type->isMixed()
                    ) {
                        $array_template_type = array_values($array_template_type->getAtomicTypes())[0];
                        $offset_template_type = array_values($offset_template_type->getAtomicTypes())[0];

                        if ($array_template_type instanceof Atomic\ObjectLike
                            && ($offset_template_type instanceof Atomic\TLiteralString
                                || $offset_template_type instanceof Atomic\TLiteralInt)
                            && isset($array_template_type->properties[$offset_template_type->value])
                        ) {
                            $include_first = false;

                            $replacement_type
                                = clone $array_template_type->properties[$offset_template_type->value];

                            foreach ($replacement_type->getAtomicTypes() as $replacement_atomic_type) {
                                $atomic_types[] = $replacement_atomic_type;
                            }
                        }
                    }
                }

                if ($include_first) {
                    $atomic_types[] = $atomic_type;
                }

                return $atomic_types;
            }

            return [$atomic_type];
        }

        if ($atomic_type instanceof Atomic\TTemplateKeyOf) {
            if ($replace) {
                $atomic_types = [];

                $include_first = true;

                if (isset($template_result->template_types[$atomic_type->param_name][$atomic_type->defining_class])) {
                    $template_type
                        = $template_result->template_types[$atomic_type->param_name][$atomic_type->defining_class][0];

                    if ($template_type->isSingle()) {
                        $template_type = array_values($template_type->getAtomicTypes())[0];

                        if ($template_type instanceof Atomic\ObjectLike
                            || $template_type instanceof Atomic\TArray
                            || $template_type instanceof Atomic\TList
                        ) {
                            if ($template_type instanceof Atomic\ObjectLike) {
                                $key_type = $template_type->getGenericKeyType();
                            } elseif ($template_type instanceof Atomic\TList) {
                                $key_type = \Psalm\Type::getInt();
                            } else {
                                $key_type = clone $template_type->type_params[0];
                            }

                            $include_first = false;

                            foreach ($key_type->getAtomicTypes() as $key_atomic_type) {
                                $atomic_types[] = $key_atomic_type;
                            }
                        }
                    }
                }

                if ($include_first) {
                    $atomic_types[] = $atomic_type;
                }

                return $atomic_types;
            }

            return [$atomic_type];
        }

        $matching_atomic_type = null;

        if ($input_type && $codebase && !$input_type->hasMixed()) {
            $matching_atomic_type = self::findMatchingAtomicTypeForTemplate(
                $input_type,
                $atomic_type,
                $key,
                $codebase
            );
        }

        $atomic_type = $atomic_type->replaceTemplateTypesWithStandins(
            $template_result,
            $codebase,
            $matching_atomic_type,
            $calling_class,
            $calling_function,
            $replace,
            $add_upper_bound,
            $depth + 1
        );

        return [$atomic_type];
    }

    public static function findMatchingAtomicTypeForTemplate(
        Union $input_type,
        Atomic $atomic_type,
        string $key,
        Codebase $codebase
    ) : ?Atomic {
        foreach ($input_type->getAtomicTypes() as $input_key => $atomic_input_type) {
            if ($bracket_pos = strpos($input_key, '<')) {
                $input_key = substr($input_key, 0, $bracket_pos);
            }

            if ($input_key === $key) {
                return $atomic_input_type;
            }

            if ($atomic_input_type instanceof Atomic\TFn && $atomic_type instanceof Atomic\TFn) {
                return $atomic_input_type;
            }

            if ($atomic_input_type instanceof Atomic\TCallable
                && $atomic_type instanceof Atomic\TCallable
            ) {
                return $atomic_input_type;
            }

            if ($atomic_input_type instanceof Atomic\TFn && $atomic_type instanceof Atomic\TCallable) {
                return $atomic_input_type;
            }

            if (($atomic_input_type instanceof Atomic\TArray
                    || $atomic_input_type instanceof Atomic\ObjectLike
                    || $atomic_input_type instanceof Atomic\TList)
                && $key === 'iterable'
            ) {
                return $atomic_input_type;
            }

            if (strpos($input_key, $key . '&') === 0) {
                return $atomic_input_type;
            }

            if ($atomic_type instanceof Atomic\TCallable) {
                $matching_atomic_type = TypeAnalyzer::getCallableFromAtomic(
                    $codebase,
                    $atomic_input_type
                );

                if ($matching_atomic_type) {
                    return $matching_atomic_type;
                }
            }

            if ($atomic_input_type instanceof Atomic\TNamedObject
                && ($atomic_type instanceof Atomic\TNamedObject
                    || $atomic_type instanceof Atomic\TIterable)
            ) {
                if ($atomic_type instanceof Atomic\TIterable) {
                    if ($atomic_input_type->value === 'Traversable') {
                        return $atomic_input_type;
                    }

                    $atomic_type = new Atomic\TGenericObject(
                        'Traversable',
                        $atomic_type->type_params
                    );
                }

                try {
                    $classlike_storage =
                        $codebase->classlike_storage_provider->get($atomic_input_type->value);

                    if ($atomic_input_type instanceof Atomic\TGenericObject
                        && isset($classlike_storage->template_type_extends[$atomic_type->value])
                    ) {
                        return $atomic_input_type;
                    }

                    if (isset($classlike_storage->template_type_extends[$atomic_type->value])) {
                        $extends_list = $classlike_storage->template_type_extends[$atomic_type->value];

                        $new_generic_params = [];

                        foreach ($extends_list as $extends_key => $value) {
                            if (is_string($extends_key)) {
                                $new_generic_params[] = $value;
                            }
                        }

                        if (!$new_generic_params) {
                            return new Atomic\TNamedObject($atomic_input_type->value);
                        }

                        return new Atomic\TGenericObject(
                            $atomic_input_type->value,
                            $new_generic_params
                        );
                    }
                } catch (\InvalidArgumentException $e) {
                    // do nothing
                }
            }
        }

        return null;
    }

    /**
     * @return list<Atomic>
     */
    private static function handleTemplateParamStandin(
        Atomic\TTemplateParam $atomic_type,
        string $key,
        ?Union $input_type,
        ?string $calling_class,
        ?string $calling_function,
        TemplateResult $template_result,
        ?Codebase $codebase,
        bool $replace,
        bool $add_upper_bound,
        int $depth,
        bool $was_nullable,
        bool &$had_template
    ) : array {
        $template_type = $template_result->template_types
            [$atomic_type->param_name]
            [$atomic_type->defining_class]
            [0];

        if ($template_type->getId() === $key) {
            return array_values($template_type->getAtomicTypes());
        }

        $replacement_type = $template_type;

        $param_name_key = $atomic_type->param_name;

        if (strpos($key, '&')) {
            $param_name_key = $key;
        }

        if ($replace) {
            $atomic_types = [];

            if ($replacement_type->hasMixed()
                && !$atomic_type->as->hasMixed()
            ) {
                foreach ($atomic_type->as->getAtomicTypes() as $as_atomic_type) {
                    $atomic_types[] = clone $as_atomic_type;
                }
            } else {
                foreach ($replacement_type->getAtomicTypes() as $replacement_atomic_type) {
                    $replacements_found = false;

                    // @codingStandardsIgnoreStart
                    if ($replacement_atomic_type instanceof Atomic\TTemplateKeyOf
                        && isset($template_result->template_types[$replacement_atomic_type->param_name][$replacement_atomic_type->defining_class][0])
                    ) {
                        $keyed_template = $template_result->template_types[$replacement_atomic_type->param_name][$replacement_atomic_type->defining_class][0];

                        if ($keyed_template->isSingle()) {
                            $keyed_template = array_values($keyed_template->getAtomicTypes())[0];
                        }

                        if ($keyed_template instanceof Atomic\ObjectLike
                            || $keyed_template instanceof Atomic\TArray
                            || $keyed_template instanceof Atomic\TList
                        ) {
                            if ($keyed_template instanceof Atomic\ObjectLike) {
                                $key_type = $keyed_template->getGenericKeyType();
                            } elseif ($keyed_template instanceof Atomic\TList) {
                                $key_type = \Psalm\Type::getInt();
                            } else {
                                $key_type = $keyed_template->type_params[0];
                            }

                            $replacements_found = true;

                            foreach ($key_type->getAtomicTypes() as $key_type_atomic) {
                                $atomic_types[] = clone $key_type_atomic;
                            }

                            $template_result->generic_params[$atomic_type->param_name][$atomic_type->defining_class][0]
                                = clone $key_type;
                        }
                    }

                    if ($replacement_atomic_type instanceof Atomic\TTemplateParam
                        && $replacement_atomic_type->defining_class !== $calling_class
                        && $replacement_atomic_type->defining_class !== 'fn-' . $calling_function
                    ) {
                        foreach ($replacement_atomic_type->as->getAtomicTypes() as $nested_type_atomic) {
                            $replacements_found = true;
                            $atomic_types[] = clone $nested_type_atomic;
                        }
                    }
                    // @codingStandardsIgnoreEnd

                    if (!$replacements_found) {
                        $atomic_types[] = clone $replacement_atomic_type;
                    }

                    $had_template = true;
                }
            }

            if ($input_type
                && (
                    $atomic_type->as->isMixed()
                    || !$codebase
                    || TypeAnalyzer::isContainedBy(
                        $codebase,
                        $input_type,
                        $atomic_type->as
                    )
                )
            ) {
                $generic_param = clone $input_type;

                if ($was_nullable && $generic_param->isNullable() && !$generic_param->isNull()) {
                    $generic_param->removeType('null');
                }

                $generic_param->setFromDocblock();

                if (isset(
                    $template_result->generic_params[$param_name_key][$atomic_type->defining_class][0]
                )) {
                    $existing_depth = $template_result->generic_params
                        [$param_name_key]
                        [$atomic_type->defining_class]
                        [1]
                        ?? -1;

                    if ($existing_depth > $depth) {
                        return $atomic_types ?: [$atomic_type];
                    }

                    if ($existing_depth === $depth) {
                        $generic_param = \Psalm\Type::combineUnionTypes(
                            $template_result->generic_params
                                [$param_name_key]
                                [$atomic_type->defining_class]
                                [0],
                            $generic_param,
                            $codebase
                        );
                    }
                }

                $template_result->generic_params[$param_name_key][$atomic_type->defining_class] = [
                    $generic_param,
                    $depth,
                ];
            }

            return $atomic_types;
        }

        if ($add_upper_bound && $input_type) {
            if ($codebase
                && TypeAnalyzer::isContainedBy(
                    $codebase,
                    $input_type,
                    $replacement_type
                )
            ) {
                $template_result->template_types[$param_name_key][$atomic_type->defining_class][0]
                    = clone $input_type;
            }
        }

        return [$atomic_type];
    }

    /**
     * @return list<Atomic>
     */
    public static function handleTemplateParamClassStandin(
        Atomic\TTemplateParamClass $atomic_type,
        ?Union $input_type,
        TemplateResult $template_result,
        int $depth,
        bool $was_single
    ) : array {
        $class_string = new Atomic\TClassString($atomic_type->as, $atomic_type->as_type);

        $atomic_types = [];

        $atomic_types[] = $class_string;

        if ($input_type) {
            $valid_input_atomic_types = [];

            foreach ($input_type->getAtomicTypes() as $input_atomic_type) {
                if ($input_atomic_type instanceof Atomic\TLiteralClassString) {
                    $valid_input_atomic_types[] = new Atomic\TNamedObject(
                        $input_atomic_type->value
                    );
                } elseif ($input_atomic_type instanceof Atomic\TTemplateParamClass) {
                    $valid_input_atomic_types[] = new Atomic\TTemplateParam(
                        $input_atomic_type->param_name,
                        $input_atomic_type->as_type
                            ? new Union([$input_atomic_type->as_type])
                            : ($input_atomic_type->as === 'object'
                                ? \Psalm\Type::getObject()
                                : \Psalm\Type::getMixed()),
                        $input_atomic_type->defining_class
                    );
                } elseif ($input_atomic_type instanceof Atomic\TClassString) {
                    if ($input_atomic_type->as_type) {
                        $valid_input_atomic_types[] = clone $input_atomic_type->as_type;
                    } elseif ($input_atomic_type->as !== 'object') {
                        $valid_input_atomic_types[] = new Atomic\TNamedObject(
                            $input_atomic_type->as
                        );
                    } else {
                        $valid_input_atomic_types[] = new Atomic\TObject();
                    }
                }
            }

            $generic_param = null;

            if ($valid_input_atomic_types) {
                $generic_param = new Union($valid_input_atomic_types);
                $generic_param->setFromDocblock();
            } elseif ($was_single) {
                $generic_param = \Psalm\Type::getMixed();
            }

            if ($generic_param) {
                if (isset($template_result->generic_params[$atomic_type->param_name][$atomic_type->defining_class])) {
                    $template_result->generic_params[$atomic_type->param_name][$atomic_type->defining_class] = [
                        \Psalm\Type::combineUnionTypes(
                            $generic_param,
                            $template_result->generic_params[$atomic_type->param_name][$atomic_type->defining_class][0]
                        ),
                        $depth
                    ];
                } else {
                    $template_result->generic_params[$atomic_type->param_name][$atomic_type->defining_class] = [
                        $generic_param,
                        $depth,
                    ];
                }
            }
        }

        return $atomic_types;
    }

    /**
     * @param  array<string, array<string, array{Union, 1?:int}>>  $template_types
     *
     * @return array{Union, 1?:int}|null
     */
    public static function getRootTemplateType(
        array $template_types,
        string $param_name,
        string $defining_class,
        array $visited_classes = []
    ) : ?array {
        if (isset($template_types[$param_name][$defining_class])) {
            $mapped_type = $template_types[$param_name][$defining_class][0];

            $mapped_type_atomic_types = array_values($mapped_type->getAtomicTypes());

            if (count($mapped_type_atomic_types) > 1
                || !$mapped_type_atomic_types[0] instanceof Atomic\TTemplateParam
                || isset($visited_classes[$defining_class])
            ) {
                return $template_types[$param_name][$defining_class];
            }

            $first_template = $mapped_type_atomic_types[0];

            return self::getRootTemplateType(
                $template_types,
                $first_template->param_name,
                $first_template->defining_class,
                $visited_classes + [$defining_class => true]
            ) ?? $template_types[$param_name][$defining_class];
        }

        return null;
    }
}
