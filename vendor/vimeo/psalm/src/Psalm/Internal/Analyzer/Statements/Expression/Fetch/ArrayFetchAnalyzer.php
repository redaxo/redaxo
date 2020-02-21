<?php
namespace Psalm\Internal\Analyzer\Statements\Expression\Fetch;

use PhpParser;
use Psalm\Internal\Analyzer\Statements\ExpressionAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Analyzer\TypeAnalyzer;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Issue\EmptyArrayAccess;
use Psalm\Issue\InvalidArrayAccess;
use Psalm\Issue\InvalidArrayAssignment;
use Psalm\Issue\InvalidArrayOffset;
use Psalm\Issue\MixedArrayAccess;
use Psalm\Issue\MixedArrayAssignment;
use Psalm\Issue\MixedArrayOffset;
use Psalm\Issue\MixedStringOffsetAssignment;
use Psalm\Issue\MixedArrayTypeCoercion;
use Psalm\Issue\NullArrayAccess;
use Psalm\Issue\NullArrayOffset;
use Psalm\Issue\PossiblyInvalidArrayAccess;
use Psalm\Issue\PossiblyInvalidArrayAssignment;
use Psalm\Issue\PossiblyInvalidArrayOffset;
use Psalm\Issue\PossiblyNullArrayAccess;
use Psalm\Issue\PossiblyNullArrayAssignment;
use Psalm\Issue\PossiblyNullArrayOffset;
use Psalm\Issue\PossiblyUndefinedArrayOffset;
use Psalm\Issue\PossiblyUndefinedIntArrayOffset;
use Psalm\Issue\PossiblyUndefinedStringArrayOffset;
use Psalm\IssueBuffer;
use Psalm\Type;
use Psalm\Type\Atomic\ObjectLike;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TClassStringMap;
use Psalm\Type\Atomic\TEmpty;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TTemplateParam;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TList;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyList;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TSingleLetter;
use Psalm\Type\Atomic\TString;
use function array_values;
use function array_keys;
use function count;
use function array_pop;
use function implode;
use function strlen;
use function strtolower;
use function in_array;
use function is_int;
use function preg_match;
use Psalm\Internal\Taint\Source;
use Psalm\Internal\Type\TemplateResult;

/**
 * @internal
 */
class ArrayFetchAnalyzer
{
    /**
     * @param   StatementsAnalyzer                   $statements_analyzer
     * @param   PhpParser\Node\Expr\ArrayDimFetch   $stmt
     * @param   Context                             $context
     *
     * @return  false|null
     */
    public static function analyze(
        StatementsAnalyzer $statements_analyzer,
        PhpParser\Node\Expr\ArrayDimFetch $stmt,
        Context $context
    ) {
        $array_var_id = ExpressionAnalyzer::getArrayVarId(
            $stmt->var,
            $statements_analyzer->getFQCLN(),
            $statements_analyzer
        );

        $keyed_array_var_id = ExpressionAnalyzer::getArrayVarId(
            $stmt,
            $statements_analyzer->getFQCLN(),
            $statements_analyzer
        );

        if ($stmt->dim && ExpressionAnalyzer::analyze($statements_analyzer, $stmt->dim, $context) === false) {
            return false;
        }

        $dim_var_id = null;
        $new_offset_type = null;

        if ($stmt->dim) {
            $used_key_type = $statements_analyzer->node_data->getType($stmt->dim) ?: Type::getMixed();

            $dim_var_id = ExpressionAnalyzer::getArrayVarId(
                $stmt->dim,
                $statements_analyzer->getFQCLN(),
                $statements_analyzer
            );
        } else {
            $used_key_type = Type::getInt();
        }

        if (ExpressionAnalyzer::analyze(
            $statements_analyzer,
            $stmt->var,
            $context
        ) === false) {
            return false;
        }

        if ($keyed_array_var_id
            && $context->hasVariable($keyed_array_var_id)
            && !$context->vars_in_scope[$keyed_array_var_id]->possibly_undefined
            && !$context->vars_in_scope[$keyed_array_var_id]->isVanillaMixed()
        ) {
            $statements_analyzer->node_data->setType(
                $stmt,
                clone $context->vars_in_scope[$keyed_array_var_id]
            );

            return;
        }

        $codebase = $statements_analyzer->getCodebase();

        if ($stmt_var_type = $statements_analyzer->node_data->getType($stmt->var)) {
            if ($stmt_var_type->isNull()) {
                if (!$context->inside_isset) {
                    if (IssueBuffer::accepts(
                        new NullArrayAccess(
                            'Cannot access array value on null variable ' . $array_var_id,
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )) {
                        // fall through
                    }
                }

                if ($stmt_type = $statements_analyzer->node_data->getType($stmt)) {
                    $statements_analyzer->node_data->setType(
                        $stmt,
                        Type::combineUnionTypes($stmt_type, Type::getNull())
                    );
                } else {
                    $statements_analyzer->node_data->setType($stmt, Type::getNull());
                }

                return;
            }

            $stmt_type = self::getArrayAccessTypeGivenOffset(
                $statements_analyzer,
                $stmt,
                $stmt_var_type,
                $used_key_type,
                false,
                $array_var_id,
                $context,
                null
            );

            $statements_analyzer->node_data->setType($stmt, $stmt_type);

            if ($array_var_id === '$_GET' || $array_var_id === '$_POST' || $array_var_id === '$_COOKIE') {
                $stmt_type->tainted = (int) Type\Union::TAINTED_INPUT;
                $stmt_type->sources = [
                    new Source(
                        $array_var_id,
                        $array_var_id,
                        new CodeLocation($statements_analyzer->getSource(), $stmt),
                        (int) Type\Union::TAINTED_INPUT
                    )
                ];
            }

            if ($context->inside_isset
                && $stmt->dim
                && ($stmt_dim_type = $statements_analyzer->node_data->getType($stmt->dim))
                && $stmt_var_type->hasArray()
                && ($stmt->var instanceof PhpParser\Node\Expr\ClassConstFetch
                    || $stmt->var instanceof PhpParser\Node\Expr\ConstFetch)
            ) {
                /**
                 * @psalm-suppress PossiblyUndefinedStringArrayOffset
                 * @var TArray|ObjectLike|TList
                 */
                $array_type = $stmt_var_type->getAtomicTypes()['array'];

                if ($array_type instanceof TArray) {
                    $const_array_key_type = $array_type->type_params[0];
                } elseif ($array_type instanceof TList) {
                    $const_array_key_type = $array_type->type_param;
                } else {
                    $const_array_key_type = $array_type->getGenericKeyType();
                }

                if ($dim_var_id
                    && !$const_array_key_type->hasMixed()
                    && !$stmt_dim_type->hasMixed()
                ) {
                    $new_offset_type = clone $stmt_dim_type;
                    $const_array_key_atomic_types = $const_array_key_type->getAtomicTypes();

                    foreach ($new_offset_type->getAtomicTypes() as $offset_key => $offset_atomic_type) {
                        if ($offset_atomic_type instanceof TString
                            || $offset_atomic_type instanceof TInt
                        ) {
                            if (!isset($const_array_key_atomic_types[$offset_key])
                                && !TypeAnalyzer::isContainedBy(
                                    $codebase,
                                    new Type\Union([$offset_atomic_type]),
                                    $const_array_key_type
                                )
                            ) {
                                $new_offset_type->removeType($offset_key);
                            }
                        } elseif (!TypeAnalyzer::isContainedBy(
                            $codebase,
                            $const_array_key_type,
                            new Type\Union([$offset_atomic_type])
                        )) {
                            $new_offset_type->removeType($offset_key);
                        }
                    }
                }
            }
        }

        if ($keyed_array_var_id
            && $context->hasVariable($keyed_array_var_id, $statements_analyzer)
            && (!($stmt_type = $statements_analyzer->node_data->getType($stmt)) || $stmt_type->isVanillaMixed())
        ) {
            $statements_analyzer->node_data->setType($stmt, $context->vars_in_scope[$keyed_array_var_id]);
        }

        if (!($stmt_type = $statements_analyzer->node_data->getType($stmt))) {
            $stmt_type = Type::getMixed();
            $statements_analyzer->node_data->setType($stmt, $stmt_type);
        } else {
            if ($stmt_type->possibly_undefined && !$context->inside_isset && !$context->inside_unset) {
                if (IssueBuffer::accepts(
                    new PossiblyUndefinedArrayOffset(
                        'Possibly undefined array key ' . $keyed_array_var_id,
                        new CodeLocation($statements_analyzer->getSource(), $stmt)
                    ),
                    $statements_analyzer->getSuppressedIssues()
                )) {
                    // fall through
                }
            }

            $stmt_type->possibly_undefined = false;
        }

        if ($context->inside_isset && $dim_var_id && $new_offset_type && $new_offset_type->getAtomicTypes()) {
            $context->vars_in_scope[$dim_var_id] = $new_offset_type;
        }

        if ($keyed_array_var_id && !$context->inside_isset) {
            $context->vars_in_scope[$keyed_array_var_id] = $stmt_type;
            $context->vars_possibly_in_scope[$keyed_array_var_id] = true;

            // reference the variable too
            $context->hasVariable($keyed_array_var_id, $statements_analyzer);
        }

        if ($codebase->taint && ($stmt_var_type = $statements_analyzer->node_data->getType($stmt->var))) {
            $sources = [];
            $either_tainted = 0;

            $sources = \array_merge($sources, $stmt_var_type->sources ?: []);
            $either_tainted = $either_tainted | $stmt_var_type->tainted;

            if ($sources) {
                $stmt_type->sources = $sources;
            }

            if ($either_tainted) {
                $stmt_type->tainted = $either_tainted;
            }
        }

        return null;
    }

    /**
     * @param  Type\Union $array_type
     * @param  Type\Union $offset_type
     * @param  bool       $in_assignment
     * @param  null|string    $array_var_id
     *
     * @return Type\Union
     */
    public static function getArrayAccessTypeGivenOffset(
        StatementsAnalyzer $statements_analyzer,
        PhpParser\Node\Expr\ArrayDimFetch $stmt,
        Type\Union $array_type,
        Type\Union $offset_type,
        bool $in_assignment,
        ?string $array_var_id,
        Context $context,
        PhpParser\Node\Expr $assign_value = null,
        Type\Union $replacement_type = null
    ) {
        $codebase = $statements_analyzer->getCodebase();

        $has_array_access = false;
        $non_array_types = [];

        $has_valid_offset = false;
        $expected_offset_types = [];

        $key_value = null;

        if ($stmt->dim instanceof PhpParser\Node\Scalar\String_
            || $stmt->dim instanceof PhpParser\Node\Scalar\LNumber
        ) {
            $key_value = $stmt->dim->value;
        } elseif ($stmt->dim && ($stmt_dim_type = $statements_analyzer->node_data->getType($stmt->dim))) {
            foreach ($stmt_dim_type->getAtomicTypes() as $possible_value_type) {
                if ($possible_value_type instanceof TLiteralString
                    || $possible_value_type instanceof TLiteralInt
                ) {
                    if ($key_value !== null) {
                        $key_value = null;
                        break;
                    }

                    $key_value = $possible_value_type->value;
                } elseif ($possible_value_type instanceof TString
                    || $possible_value_type instanceof TInt
                ) {
                    $key_value = null;
                    break;
                }
            }
        }

        $array_access_type = null;

        if ($offset_type->isNull()) {
            if (IssueBuffer::accepts(
                new NullArrayOffset(
                    'Cannot access value on variable ' . $array_var_id . ' using null offset',
                    new CodeLocation($statements_analyzer->getSource(), $stmt)
                ),
                $statements_analyzer->getSuppressedIssues()
            )) {
                // fall through
            }

            if ($in_assignment) {
                $offset_type->removeType('null');
                $offset_type->addType(new TLiteralInt(0));
            }
        }

        if ($offset_type->isNullable() && !$context->inside_isset) {
            if (!$offset_type->ignore_nullable_issues) {
                if (IssueBuffer::accepts(
                    new PossiblyNullArrayOffset(
                        'Cannot access value on variable ' . $array_var_id
                            . ' using possibly null offset ' . $offset_type,
                        new CodeLocation($statements_analyzer->getSource(), $stmt->var)
                    ),
                    $statements_analyzer->getSuppressedIssues()
                )) {
                    // fall through
                }
            }

            if ($in_assignment) {
                $offset_type->removeType('null');

                if (!$offset_type->ignore_nullable_issues) {
                    $offset_type->addType(new TLiteralInt(0));
                }
            }
        }

        foreach ($array_type->getAtomicTypes() as $type_string => $type) {
            $original_type = $type;

            if ($type instanceof TMixed || $type instanceof TTemplateParam || $type instanceof TEmpty) {
                if (!$type instanceof TTemplateParam || $type->as->isMixed() || !$type->as->isSingle()) {
                    if (!$context->collect_initializations
                        && !$context->collect_mutations
                        && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                        && (!(($parent_source = $statements_analyzer->getSource())
                                instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                            || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
                    ) {
                        $codebase->analyzer->incrementMixedCount($statements_analyzer->getFilePath());
                    }

                    if (!$context->inside_isset) {
                        if ($in_assignment) {
                            if (IssueBuffer::accepts(
                                new MixedArrayAssignment(
                                    'Cannot access array value on mixed variable ' . $array_var_id,
                                    new CodeLocation($statements_analyzer->getSource(), $stmt)
                                ),
                                $statements_analyzer->getSuppressedIssues()
                            )) {
                                // fall through
                            }
                        } else {
                            if (IssueBuffer::accepts(
                                new MixedArrayAccess(
                                    'Cannot access array value on mixed variable ' . $array_var_id,
                                    new CodeLocation($statements_analyzer->getSource(), $stmt)
                                ),
                                $statements_analyzer->getSuppressedIssues()
                            )) {
                                // fall through
                            }
                        }
                    }

                    $has_valid_offset = true;
                    $array_access_type = Type::getMixed();
                    break;
                }

                $type = clone array_values($type->as->getAtomicTypes())[0];
            }

            if ($type instanceof TNull) {
                if ($array_type->ignore_nullable_issues) {
                    continue;
                }

                if ($in_assignment) {
                    if ($replacement_type) {
                        if ($array_access_type) {
                            $array_access_type = Type::combineUnionTypes($array_access_type, $replacement_type);
                        } else {
                            $array_access_type = clone $replacement_type;
                        }
                    } else {
                        if (IssueBuffer::accepts(
                            new PossiblyNullArrayAssignment(
                                'Cannot access array value on possibly null variable ' . $array_var_id .
                                    ' of type ' . $array_type,
                                new CodeLocation($statements_analyzer->getSource(), $stmt)
                            ),
                            $statements_analyzer->getSuppressedIssues()
                        )) {
                            // fall through
                        }

                        $array_access_type = new Type\Union([new TEmpty]);
                    }
                } else {
                    if (!$context->inside_isset) {
                        if (IssueBuffer::accepts(
                            new PossiblyNullArrayAccess(
                                'Cannot access array value on possibly null variable ' . $array_var_id .
                                    ' of type ' . $array_type,
                                new CodeLocation($statements_analyzer->getSource(), $stmt)
                            ),
                            $statements_analyzer->getSuppressedIssues()
                        )) {
                            // fall through
                        }
                    }

                    if ($array_access_type) {
                        $array_access_type = Type::combineUnionTypes($array_access_type, Type::getNull());
                    } else {
                        $array_access_type = Type::getNull();
                    }
                }

                continue;
            }

            if ($type instanceof TArray
                || $type instanceof ObjectLike
                || $type instanceof TList
                || $type instanceof TClassStringMap
            ) {
                $has_array_access = true;

                if ($in_assignment
                    && $type instanceof TArray
                    && (($type->type_params[0]->isEmpty() && $type->type_params[1]->isEmpty())
                        || ($type->type_params[1]->isMixed() && \is_string($key_value)))
                ) {
                    $from_empty_array = $type->type_params[0]->isEmpty() && $type->type_params[1]->isEmpty();

                    if ($key_value !== null) {
                        $from_mixed_array = $type->type_params[1]->isMixed();

                        $previous_key_type = $type->type_params[0];
                        $previous_value_type = $type->type_params[1];

                        // ok, type becomes an ObjectLike
                        $array_type->removeType($type_string);
                        $type = new ObjectLike([$key_value => $from_mixed_array ? Type::getMixed() : Type::getEmpty()]);

                        $type->sealed = $from_empty_array;

                        if (!$from_empty_array) {
                            $type->previous_value_type = clone $previous_value_type;
                            $type->previous_key_type = clone $previous_key_type;
                        }

                        $array_type->addType($type);
                    } elseif (!$stmt->dim && $from_empty_array && $replacement_type) {
                        $array_type->removeType($type_string);
                        $array_type->addType(new Type\Atomic\TNonEmptyList($replacement_type));
                        continue;
                    }
                }

                $offset_type = self::replaceOffsetTypeWithInts($offset_type);

                if ($type instanceof TList
                    && (($in_assignment && $stmt->dim)
                        || $original_type instanceof TTemplateParam
                        || !$offset_type->isInt())
                ) {
                    $type = new TArray([Type::getInt(), $type->type_param]);
                }

                if ($type instanceof TArray) {
                    // if we're assigning to an empty array with a key offset, refashion that array
                    if ($in_assignment) {
                        if ($type->type_params[0]->isEmpty()) {
                            $type->type_params[0] = $offset_type;
                        }
                    } elseif (!$type->type_params[0]->isEmpty()) {
                        $expected_offset_type = $type->type_params[0]->hasMixed()
                            ? new Type\Union([ new TArrayKey ])
                            : $type->type_params[0];

                        $templated_offset_type = null;

                        foreach ($offset_type->getAtomicTypes() as $offset_atomic_type) {
                            if ($offset_atomic_type instanceof TTemplateParam) {
                                $templated_offset_type = $offset_atomic_type;
                            }
                        }

                        $union_comparison_results = new \Psalm\Internal\Analyzer\TypeComparisonResult();

                        if ($original_type instanceof TTemplateParam && $templated_offset_type) {
                            foreach ($templated_offset_type->as->getAtomicTypes() as $offset_as) {
                                if ($offset_as instanceof Type\Atomic\TTemplateKeyOf
                                    && $offset_as->param_name === $original_type->param_name
                                    && $offset_as->defining_class === $original_type->defining_class
                                ) {
                                    /** @psalm-suppress PropertyTypeCoercion */
                                    $type->type_params[1] = new Type\Union([
                                        new Type\Atomic\TTemplateIndexedAccess(
                                            $offset_as->param_name,
                                            $templated_offset_type->param_name,
                                            $offset_as->defining_class
                                        )
                                    ]);

                                    $has_valid_offset = true;
                                }
                            }
                        } else {
                            $offset_type_contained_by_expected = TypeAnalyzer::isContainedBy(
                                $codebase,
                                $offset_type,
                                $expected_offset_type,
                                true,
                                $offset_type->ignore_falsable_issues,
                                $union_comparison_results
                            );

                            if ($codebase->config->ensure_array_string_offsets_exist
                                && $offset_type_contained_by_expected
                            ) {
                                self::checkLiteralStringArrayOffset(
                                    $offset_type,
                                    $expected_offset_type,
                                    $array_var_id,
                                    $stmt,
                                    $context,
                                    $statements_analyzer
                                );
                            }

                            if ($codebase->config->ensure_array_int_offsets_exist
                                && $offset_type_contained_by_expected
                            ) {
                                self::checkLiteralIntArrayOffset(
                                    $offset_type,
                                    $expected_offset_type,
                                    $array_var_id,
                                    $stmt,
                                    $context,
                                    $statements_analyzer
                                );
                            }

                            if ((!$offset_type_contained_by_expected
                                    && !$union_comparison_results->type_coerced_from_scalar)
                                || $union_comparison_results->to_string_cast
                            ) {
                                if ($union_comparison_results->type_coerced_from_mixed
                                    && !$offset_type->isMixed()
                                ) {
                                    if (IssueBuffer::accepts(
                                        new MixedArrayTypeCoercion(
                                            'Coercion from array offset type \'' . $offset_type->getId() . '\' '
                                                . 'to the expected type \'' . $expected_offset_type->getId() . '\'',
                                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                                        ),
                                        $statements_analyzer->getSuppressedIssues()
                                    )) {
                                        // fall through
                                    }
                                } else {
                                    $expected_offset_types[] = $expected_offset_type->getId();
                                }

                                if (TypeAnalyzer::canExpressionTypesBeIdentical(
                                    $codebase,
                                    $offset_type,
                                    $expected_offset_type
                                )) {
                                    $has_valid_offset = true;
                                }
                            } else {
                                $has_valid_offset = true;
                            }
                        }
                    }

                    if (!$stmt->dim && $type instanceof TNonEmptyArray && $type->count !== null) {
                        $type->count++;
                    }

                    if ($in_assignment && $replacement_type) {
                        /** @psalm-suppress PropertyTypeCoercion */
                        $type->type_params[1] = Type::combineUnionTypes(
                            $type->type_params[1],
                            $replacement_type,
                            $codebase
                        );
                    }

                    if (!$array_access_type) {
                        $array_access_type = $type->type_params[1];
                    } else {
                        $array_access_type = Type::combineUnionTypes(
                            $array_access_type,
                            $type->type_params[1]
                        );
                    }

                    if ($array_access_type->isEmpty()
                        && !$array_type->hasMixed()
                        && !$in_assignment
                        && !$context->inside_isset
                    ) {
                        if (IssueBuffer::accepts(
                            new EmptyArrayAccess(
                                'Cannot access value on empty array variable ' . $array_var_id,
                                new CodeLocation($statements_analyzer->getSource(), $stmt)
                            ),
                            $statements_analyzer->getSuppressedIssues()
                        )) {
                            return Type::getMixed(true);
                        }

                        if (!IssueBuffer::isRecording()) {
                            $array_access_type = Type::getMixed(true);
                        }
                    }
                } elseif ($type instanceof TList) {
                    // if we're assigning to an empty array with a key offset, refashion that array
                    if (!$in_assignment) {
                        if (!$type instanceof TNonEmptyList
                            || ($key_value > 0 && $key_value > ($type->count - 1))
                        ) {
                            $expected_offset_type = Type::getInt();

                            if ($codebase->config->ensure_array_int_offsets_exist) {
                                self::checkLiteralIntArrayOffset(
                                    $offset_type,
                                    $expected_offset_type,
                                    $array_var_id,
                                    $stmt,
                                    $context,
                                    $statements_analyzer
                                );
                            }
                        }

                        $has_valid_offset = true;
                    }

                    if ($in_assignment && $type instanceof Type\Atomic\TNonEmptyList && $type->count !== null) {
                        $type->count++;
                    }

                    if ($in_assignment && $replacement_type) {
                        $type->type_param = Type::combineUnionTypes(
                            $type->type_param,
                            $replacement_type,
                            $codebase
                        );
                    }

                    if (!$array_access_type) {
                        $array_access_type = $type->type_param;
                    } else {
                        $array_access_type = Type::combineUnionTypes(
                            $array_access_type,
                            $type->type_param
                        );
                    }
                } elseif ($type instanceof TClassStringMap) {
                    $offset_type_parts = array_values($offset_type->getAtomicTypes());

                    foreach ($offset_type_parts as $offset_type_part) {
                        if ($offset_type_part instanceof Type\Atomic\TClassString) {
                            if ($offset_type_part instanceof Type\Atomic\TTemplateParamClass) {
                                $template_result_get = new TemplateResult(
                                    [],
                                    [
                                        $type->param_name => [
                                            'class-string-map' => [
                                                new Type\Union([
                                                    new TTemplateParam(
                                                        $offset_type_part->param_name,
                                                        $offset_type_part->as_type
                                                            ? new Type\Union([$offset_type_part->as_type])
                                                            : Type::getObject(),
                                                        $offset_type_part->defining_class
                                                    )
                                                ])
                                            ]
                                        ]
                                    ]
                                );

                                $template_result_set = new TemplateResult(
                                    [],
                                    [
                                        $offset_type_part->param_name => [
                                            $offset_type_part->defining_class => [
                                                new Type\Union([
                                                    new TTemplateParam(
                                                        $type->param_name,
                                                        $type->as_type
                                                            ? new Type\Union([$type->as_type])
                                                            : Type::getObject(),
                                                        'class-string-map'
                                                    )
                                                ])
                                            ]
                                        ]
                                    ]
                                );
                            } else {
                                $template_result_get = new TemplateResult(
                                    [],
                                    [
                                        $type->param_name => [
                                            'class-string-map' => [
                                                new Type\Union([
                                                    $offset_type_part->as_type
                                                        ?: new Type\Atomic\TObject()
                                                ])
                                            ]
                                        ]
                                    ]
                                );
                                $template_result_set = new TemplateResult(
                                    [],
                                    []
                                );
                            }

                            $expected_value_param_get = clone $type->value_param;

                            $expected_value_param_get->replaceTemplateTypesWithArgTypes(
                                $template_result_get->generic_params,
                                $codebase
                            );

                            if ($replacement_type) {
                                $expected_value_param_set = clone $type->value_param;

                                $replacement_type->replaceTemplateTypesWithArgTypes(
                                    $template_result_set->generic_params,
                                    $codebase
                                );

                                $type->value_param = Type::combineUnionTypes(
                                    $replacement_type,
                                    $expected_value_param_set,
                                    $codebase
                                );
                            }

                            if (!$array_access_type) {
                                $array_access_type = $expected_value_param_get;
                            } else {
                                $array_access_type = Type::combineUnionTypes(
                                    $array_access_type,
                                    $expected_value_param_get,
                                    $codebase
                                );
                            }
                        }
                    }
                } else {
                    $generic_key_type = $type->getGenericKeyType();

                    if (!$stmt->dim && $type->sealed && $type->is_list) {
                        $key_value = count($type->properties);
                    }

                    if ($key_value !== null) {
                        if (isset($type->properties[$key_value]) || $replacement_type) {
                            $has_valid_offset = true;

                            if ($replacement_type) {
                                if (isset($type->properties[$key_value])) {
                                    $type->properties[$key_value] = Type::combineUnionTypes(
                                        $type->properties[$key_value],
                                        $replacement_type
                                    );
                                } else {
                                    $type->properties[$key_value] = $replacement_type;
                                }
                            }

                            if (!$array_access_type) {
                                $array_access_type = clone $type->properties[$key_value];
                            } else {
                                $array_access_type = Type::combineUnionTypes(
                                    $array_access_type,
                                    $type->properties[$key_value]
                                );
                            }
                        } elseif ($in_assignment) {
                            $type->properties[$key_value] = new Type\Union([new TEmpty]);

                            if (!$array_access_type) {
                                $array_access_type = clone $type->properties[$key_value];
                            } else {
                                $array_access_type = Type::combineUnionTypes(
                                    $array_access_type,
                                    $type->properties[$key_value]
                                );
                            }
                        } elseif ($type->previous_value_type) {
                            $has_valid_offset = true;

                            if ($codebase->config->ensure_array_string_offsets_exist) {
                                self::checkLiteralStringArrayOffset(
                                    $offset_type,
                                    $type->getGenericKeyType(),
                                    $array_var_id,
                                    $stmt,
                                    $context,
                                    $statements_analyzer
                                );
                            }

                            if ($codebase->config->ensure_array_int_offsets_exist) {
                                self::checkLiteralIntArrayOffset(
                                    $offset_type,
                                    $type->getGenericKeyType(),
                                    $array_var_id,
                                    $stmt,
                                    $context,
                                    $statements_analyzer
                                );
                            }

                            $type->properties[$key_value] = clone $type->previous_value_type;

                            $array_access_type = clone $type->previous_value_type;
                        } else {
                            if ($type->sealed) {
                                $object_like_keys = array_keys($type->properties);

                                if (count($object_like_keys) === 1) {
                                    $expected_keys_string = '\'' . $object_like_keys[0] . '\'';
                                } else {
                                    $last_key = array_pop($object_like_keys);
                                    $expected_keys_string = '\'' . implode('\', \'', $object_like_keys) .
                                        '\' or \'' . $last_key . '\'';
                                }

                                $expected_offset_types[] = $expected_keys_string;
                            }

                            $array_access_type = Type::getMixed();
                        }
                    } else {
                        $key_type = $generic_key_type->hasMixed()
                                ? Type::getArrayKey()
                                : $generic_key_type;

                        $union_comparison_results = new \Psalm\Internal\Analyzer\TypeComparisonResult();

                        $is_contained = TypeAnalyzer::isContainedBy(
                            $codebase,
                            $offset_type,
                            $key_type,
                            true,
                            $offset_type->ignore_falsable_issues,
                            $union_comparison_results
                        );

                        if ($context->inside_isset && !$is_contained) {
                            $is_contained = TypeAnalyzer::isContainedBy(
                                $codebase,
                                $key_type,
                                $offset_type,
                                true,
                                $offset_type->ignore_falsable_issues
                            )
                            || TypeAnalyzer::canBeContainedBy(
                                $codebase,
                                $offset_type,
                                $key_type,
                                true,
                                $offset_type->ignore_falsable_issues
                            );
                        }

                        if (($is_contained
                            || $union_comparison_results->type_coerced_from_scalar
                            || $union_comparison_results->type_coerced_from_mixed
                            || $in_assignment)
                            && !$union_comparison_results->to_string_cast
                        ) {
                            if ($replacement_type) {
                                $generic_params = Type::combineUnionTypes(
                                    $type->getGenericValueType(),
                                    $replacement_type
                                );

                                $new_key_type = Type::combineUnionTypes(
                                    $generic_key_type,
                                    $offset_type
                                );

                                $property_count = $type->sealed ? count($type->properties) : null;

                                if (!$stmt->dim && $property_count) {
                                    ++$property_count;
                                    $array_type->removeType($type_string);
                                    $type = new TNonEmptyArray([
                                        $new_key_type,
                                        $generic_params,
                                    ]);
                                    $array_type->addType($type);
                                    $type->count = $property_count;
                                } else {
                                    $array_type->removeType($type_string);
                                    $type = new TArray([
                                        $new_key_type,
                                        $generic_params,
                                    ]);
                                    $array_type->addType($type);
                                }

                                if (!$array_access_type) {
                                    $array_access_type = clone $generic_params;
                                } else {
                                    $array_access_type = Type::combineUnionTypes(
                                        $array_access_type,
                                        $generic_params
                                    );
                                }
                            } else {
                                if (!$array_access_type) {
                                    $array_access_type = $type->getGenericValueType();
                                } else {
                                    $array_access_type = Type::combineUnionTypes(
                                        $array_access_type,
                                        $type->getGenericValueType()
                                    );
                                }
                            }

                            $has_valid_offset = true;
                        } else {
                            if (!$context->inside_isset
                                || ($type->sealed && !$union_comparison_results->type_coerced)
                            ) {
                                $expected_offset_types[] = $generic_key_type->getId();
                            }

                            $array_access_type = Type::getMixed();
                        }
                    }
                }
                continue;
            }

            if ($type instanceof TString) {
                if ($in_assignment && $replacement_type) {
                    if ($replacement_type->hasMixed()) {
                        if (!$context->collect_initializations
                            && !$context->collect_mutations
                            && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                            && (!(($parent_source = $statements_analyzer->getSource())
                                    instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                                || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
                        ) {
                            $codebase->analyzer->incrementMixedCount($statements_analyzer->getFilePath());
                        }

                        if (IssueBuffer::accepts(
                            new MixedStringOffsetAssignment(
                                'Right-hand-side of string offset assignment cannot be mixed',
                                new CodeLocation($statements_analyzer->getSource(), $stmt)
                            ),
                            $statements_analyzer->getSuppressedIssues()
                        )) {
                            // fall through
                        }
                    } else {
                        if (!$context->collect_initializations
                            && !$context->collect_mutations
                            && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                            && (!(($parent_source = $statements_analyzer->getSource())
                                    instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                                || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
                        ) {
                            $codebase->analyzer->incrementNonMixedCount($statements_analyzer->getFilePath());
                        }
                    }
                }

                if ($type instanceof TSingleLetter) {
                    $valid_offset_type = Type::getInt(false, 0);
                } elseif ($type instanceof TLiteralString) {
                    if (!strlen($type->value)) {
                        $valid_offset_type = Type::getEmpty();
                    } elseif (strlen($type->value) < 10) {
                        $valid_offsets = [];

                        for ($i = -strlen($type->value), $l = strlen($type->value); $i < $l; $i++) {
                            $valid_offsets[] = new TLiteralInt($i);
                        }

                        if (!$valid_offsets) {
                            throw new \UnexpectedValueException('This is weird');
                        }

                        $valid_offset_type = new Type\Union($valid_offsets);
                    } else {
                        $valid_offset_type = Type::getInt();
                    }
                } else {
                    $valid_offset_type = Type::getInt();
                }

                if (!TypeAnalyzer::isContainedBy(
                    $codebase,
                    $offset_type,
                    $valid_offset_type,
                    true
                )) {
                    $expected_offset_types[] = $valid_offset_type->getId();

                    $array_access_type = Type::getMixed();
                } else {
                    $has_valid_offset = true;

                    if (!$array_access_type) {
                        $array_access_type = Type::getSingleLetter();
                    } else {
                        $array_access_type = Type::combineUnionTypes(
                            $array_access_type,
                            Type::getSingleLetter()
                        );
                    }
                }

                continue;
            }

            if (!$context->collect_initializations
                && !$context->collect_mutations
                && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                && (!(($parent_source = $statements_analyzer->getSource())
                        instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                    || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
            ) {
                $codebase->analyzer->incrementNonMixedCount($statements_analyzer->getFilePath());
            }

            if ($type instanceof Type\Atomic\TFalse && $array_type->ignore_falsable_issues) {
                continue;
            }

            if ($type instanceof TNamedObject) {
                if (strtolower($type->value) === 'simplexmlelement') {
                    $array_access_type = new Type\Union([new TNamedObject('SimpleXMLElement')]);
                } elseif (strtolower($type->value) === 'domnodelist' && $stmt->dim) {
                    $old_data_provider = $statements_analyzer->node_data;

                    $statements_analyzer->node_data = clone $statements_analyzer->node_data;

                    $fake_method_call = new PhpParser\Node\Expr\MethodCall(
                        $stmt->var,
                        new PhpParser\Node\Identifier('item', $stmt->var->getAttributes()),
                        [
                            new PhpParser\Node\Arg($stmt->dim)
                        ]
                    );

                    $suppressed_issues = $statements_analyzer->getSuppressedIssues();

                    if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
                        $statements_analyzer->addSuppressedIssues(['PossiblyInvalidMethodCall']);
                    }

                    \Psalm\Internal\Analyzer\Statements\Expression\Call\MethodCallAnalyzer::analyze(
                        $statements_analyzer,
                        $fake_method_call,
                        $context
                    );

                    if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
                        $statements_analyzer->removeSuppressedIssues(['PossiblyInvalidMethodCall']);
                    }

                    $array_access_type = $statements_analyzer->node_data->getType(
                        $fake_method_call
                    ) ?: Type::getMixed();

                    $statements_analyzer->node_data = $old_data_provider;
                } else {
                    $suppressed_issues = $statements_analyzer->getSuppressedIssues();

                    if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
                        $statements_analyzer->addSuppressedIssues(['PossiblyInvalidMethodCall']);
                    }

                    if ($in_assignment) {
                        $old_node_data = $statements_analyzer->node_data;

                        $statements_analyzer->node_data = clone $statements_analyzer->node_data;

                        $fake_set_method_call = new PhpParser\Node\Expr\MethodCall(
                            $stmt->var,
                            new PhpParser\Node\Identifier('offsetSet', $stmt->var->getAttributes()),
                            [
                                new PhpParser\Node\Arg(
                                    $stmt->dim
                                        ? $stmt->dim
                                        : new PhpParser\Node\Expr\ConstFetch(
                                            new PhpParser\Node\Name('null'),
                                            $stmt->var->getAttributes()
                                        )
                                ),
                                new PhpParser\Node\Arg(
                                    $assign_value
                                        ?: new PhpParser\Node\Expr\ConstFetch(
                                            new PhpParser\Node\Name('null'),
                                            $stmt->var->getAttributes()
                                        )
                                ),
                            ]
                        );

                        \Psalm\Internal\Analyzer\Statements\Expression\Call\MethodCallAnalyzer::analyze(
                            $statements_analyzer,
                            $fake_set_method_call,
                            $context
                        );

                        $statements_analyzer->node_data = $old_node_data;
                    }

                    if ($stmt->dim) {
                        $old_node_data = $statements_analyzer->node_data;

                        $statements_analyzer->node_data = clone $statements_analyzer->node_data;

                        $fake_get_method_call = new PhpParser\Node\Expr\MethodCall(
                            $stmt->var,
                            new PhpParser\Node\Identifier('offsetGet', $stmt->var->getAttributes()),
                            [
                                new PhpParser\Node\Arg(
                                    $stmt->dim
                                )
                            ]
                        );

                        \Psalm\Internal\Analyzer\Statements\Expression\Call\MethodCallAnalyzer::analyze(
                            $statements_analyzer,
                            $fake_get_method_call,
                            $context
                        );

                        $array_access_type = $statements_analyzer->node_data->getType($fake_get_method_call)
                            ?: Type::getMixed();

                        $statements_analyzer->node_data = $old_node_data;
                    } else {
                        $array_access_type = Type::getVoid();
                    }

                    $has_array_access = true;

                    if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
                        $statements_analyzer->removeSuppressedIssues(['PossiblyInvalidMethodCall']);
                    }
                }
            } elseif (!$array_type->hasMixed()) {
                $non_array_types[] = (string)$type;
            }
        }

        if ($non_array_types) {
            if ($has_array_access) {
                if ($in_assignment) {
                    if (IssueBuffer::accepts(
                        new PossiblyInvalidArrayAssignment(
                            'Cannot access array value on non-array variable ' .
                            $array_var_id . ' of type ' . $non_array_types[0],
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )
                    ) {
                        // do nothing
                    }
                } else {
                    if (IssueBuffer::accepts(
                        new PossiblyInvalidArrayAccess(
                            'Cannot access array value on non-array variable ' .
                            $array_var_id . ' of type ' . $non_array_types[0],
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )
                    ) {
                        // do nothing
                    }
                }
            } else {
                if ($in_assignment) {
                    if (IssueBuffer::accepts(
                        new InvalidArrayAssignment(
                            'Cannot access array value on non-array variable ' .
                            $array_var_id . ' of type ' . $non_array_types[0],
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )) {
                        // fall through
                    }
                } else {
                    if (IssueBuffer::accepts(
                        new InvalidArrayAccess(
                            'Cannot access array value on non-array variable ' .
                            $array_var_id . ' of type ' . $non_array_types[0],
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )) {
                        // fall through
                    }
                }

                $array_access_type = Type::getMixed();
            }
        }

        if ($offset_type->hasMixed()) {
            if (!$context->collect_initializations
                && !$context->collect_mutations
                && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                && (!(($parent_source = $statements_analyzer->getSource())
                        instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                    || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
            ) {
                $codebase->analyzer->incrementMixedCount($statements_analyzer->getFilePath());
            }

            if (IssueBuffer::accepts(
                new MixedArrayOffset(
                    'Cannot access value on variable ' . $array_var_id . ' using mixed offset',
                    new CodeLocation($statements_analyzer->getSource(), $stmt)
                ),
                $statements_analyzer->getSuppressedIssues()
            )) {
                // fall through
            }
        } else {
            if (!$context->collect_initializations
                && !$context->collect_mutations
                && $statements_analyzer->getFilePath() === $statements_analyzer->getRootFilePath()
                && (!(($parent_source = $statements_analyzer->getSource())
                        instanceof \Psalm\Internal\Analyzer\FunctionLikeAnalyzer)
                    || !$parent_source->getSource() instanceof \Psalm\Internal\Analyzer\TraitAnalyzer)
            ) {
                $codebase->analyzer->incrementNonMixedCount($statements_analyzer->getFilePath());
            }

            if ($expected_offset_types) {
                $invalid_offset_type = $expected_offset_types[0];

                $used_offset = 'using a ' . $offset_type->getId() . ' offset';

                if ($key_value !== null) {
                    $used_offset = 'using offset value of '
                        . (is_int($key_value) ? $key_value : '\'' . $key_value . '\'');
                }

                if ($has_valid_offset && $context->inside_isset) {
                    // do nothing
                } elseif ($has_valid_offset) {
                    if (!$context->inside_unset) {
                        if (IssueBuffer::accepts(
                            new PossiblyInvalidArrayOffset(
                                'Cannot access value on variable ' . $array_var_id . ' ' . $used_offset
                                    . ', expecting ' . $invalid_offset_type,
                                new CodeLocation($statements_analyzer->getSource(), $stmt)
                            ),
                            $statements_analyzer->getSuppressedIssues()
                        )) {
                            // fall through
                        }
                    }
                } else {
                    if (IssueBuffer::accepts(
                        new InvalidArrayOffset(
                            'Cannot access value on variable ' . $array_var_id . ' ' . $used_offset
                                . ', expecting ' . $invalid_offset_type,
                            new CodeLocation($statements_analyzer->getSource(), $stmt)
                        ),
                        $statements_analyzer->getSuppressedIssues()
                    )) {
                        // fall through
                    }
                }
            }
        }

        if ($array_access_type === null) {
            // shouldn’t happen, but don’t crash
            return Type::getMixed();
        }

        if ($in_assignment) {
            $array_type->bustCache();
        }

        return $array_access_type;
    }

    private static function checkLiteralIntArrayOffset(
        Type\Union $offset_type,
        Type\Union $expected_offset_type,
        ?string $array_var_id,
        PhpParser\Node\Expr\ArrayDimFetch $stmt,
        Context $context,
        StatementsAnalyzer $statements_analyzer
    ) : void {
        if ($context->inside_isset || $context->inside_unset) {
            return;
        }

        if ($offset_type->hasLiteralInt()) {
            $found_match = false;

            foreach ($offset_type->getAtomicTypes() as $offset_type_part) {
                if ($array_var_id
                    && $offset_type_part instanceof TLiteralInt
                    && isset(
                        $context->vars_in_scope[
                            $array_var_id . '[' . $offset_type_part->value . ']'
                        ]
                    )
                    && !$context->vars_in_scope[
                            $array_var_id . '[' . $offset_type_part->value . ']'
                        ]->possibly_undefined
                ) {
                    $found_match = true;
                }
            }

            if (!$found_match) {
                if (IssueBuffer::accepts(
                    new PossiblyUndefinedIntArrayOffset(
                        'Possibly undefined array offset \''
                            . $offset_type->getId() . '\' '
                            . 'is risky given expected type \''
                            . $expected_offset_type->getId() . '\'.'
                            . ' Consider using isset beforehand.',
                        new CodeLocation($statements_analyzer->getSource(), $stmt)
                    ),
                    $statements_analyzer->getSuppressedIssues()
                )) {
                    // fall through
                }
            }
        }
    }

    private static function checkLiteralStringArrayOffset(
        Type\Union $offset_type,
        Type\Union $expected_offset_type,
        ?string $array_var_id,
        PhpParser\Node\Expr\ArrayDimFetch $stmt,
        Context $context,
        StatementsAnalyzer $statements_analyzer
    ) : void {
        if ($context->inside_isset || $context->inside_unset) {
            return;
        }

        if ($offset_type->hasLiteralString() && !$expected_offset_type->hasLiteralClassString()) {
            $found_match = false;

            foreach ($offset_type->getAtomicTypes() as $offset_type_part) {
                if ($array_var_id
                    && $offset_type_part instanceof TLiteralString
                    && isset(
                        $context->vars_in_scope[
                            $array_var_id . '[\'' . $offset_type_part->value . '\']'
                        ]
                    )
                    && !$context->vars_in_scope[
                            $array_var_id . '[\'' . $offset_type_part->value . '\']'
                        ]->possibly_undefined
                ) {
                    $found_match = true;
                }
            }

            if (!$found_match) {
                if (IssueBuffer::accepts(
                    new PossiblyUndefinedStringArrayOffset(
                        'Possibly undefined array offset \''
                            . $offset_type->getId() . '\' '
                            . 'is risky given expected type \''
                            . $expected_offset_type->getId() . '\'.'
                            . ' Consider using isset beforehand.',
                        new CodeLocation($statements_analyzer->getSource(), $stmt)
                    ),
                    $statements_analyzer->getSuppressedIssues()
                )) {
                    // fall through
                }
            }
        }
    }

    /**
     * @return Type\Union
     */
    public static function replaceOffsetTypeWithInts(Type\Union $offset_type)
    {
        $offset_types = $offset_type->getAtomicTypes();

        $cloned = false;

        foreach ($offset_types as $key => $offset_type_part) {
            if ($offset_type_part instanceof Type\Atomic\TLiteralString) {
                if (preg_match('/^(0|[1-9][0-9]*)$/', $offset_type_part->value)) {
                    if (!$cloned) {
                        $offset_type = clone $offset_type;
                        $cloned = true;
                    }
                    $offset_type->addType(new Type\Atomic\TLiteralInt((int) $offset_type_part->value));
                    $offset_type->removeType($key);
                }
            } elseif ($offset_type_part instanceof Type\Atomic\TBool) {
                if (!$cloned) {
                    $offset_type = clone $offset_type;
                    $cloned = true;
                }

                if ($offset_type_part instanceof Type\Atomic\TFalse) {
                    if (!$offset_type->ignore_falsable_issues) {
                        $offset_type->addType(new Type\Atomic\TLiteralInt(0));
                        $offset_type->removeType($key);
                    }
                } elseif ($offset_type_part instanceof Type\Atomic\TTrue) {
                    $offset_type->addType(new Type\Atomic\TLiteralInt(1));
                    $offset_type->removeType($key);
                } else {
                    $offset_type->addType(new Type\Atomic\TLiteralInt(0));
                    $offset_type->addType(new Type\Atomic\TLiteralInt(1));
                    $offset_type->removeType($key);
                }
            }
        }

        return $offset_type;
    }
}
