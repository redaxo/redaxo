<?php
namespace Psalm\Internal\Analyzer\Statements\Expression;

use PhpParser;
use Psalm\Internal\Analyzer\Statements\ExpressionAnalyzer;
use \Psalm\Internal\Analyzer\Statements\Block\IfAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Type;
use Psalm\Type\Algebra;
use Psalm\Type\Reconciler;
use Psalm\Internal\Type\AssertionReconciler;
use function array_merge;
use function array_map;
use function array_diff_key;
use function array_filter;
use const ARRAY_FILTER_USE_KEY;
use function array_values;
use function array_keys;
use function preg_match;
use function preg_quote;
use function array_intersect_key;

/**
 * @internal
 */
class TernaryAnalyzer
{
    /**
     * @param   StatementsAnalyzer           $statements_analyzer
     * @param   PhpParser\Node\Expr\Ternary $stmt
     * @param   Context                     $context
     *
     * @return  false|null
     */
    public static function analyze(
        StatementsAnalyzer $statements_analyzer,
        PhpParser\Node\Expr\Ternary $stmt,
        Context $context
    ) {
        $codebase = $statements_analyzer->getCodebase();

        $if_scope = new \Psalm\Internal\Scope\IfScope();

        try {
            $if_conditional_scope = IfAnalyzer::analyzeIfConditional(
                $statements_analyzer,
                $stmt->cond,
                $context,
                $codebase,
                $if_scope,
                $context->branch_point ?: (int) $stmt->getAttribute('startFilePos')
            );

            $if_context = $if_conditional_scope->if_context;

            $cond_referenced_var_ids = $if_conditional_scope->cond_referenced_var_ids;
        } catch (\Psalm\Exception\ScopeAnalysisException $e) {
            return false;
        }

        $codebase = $statements_analyzer->getCodebase();

        $if_clauses = \Psalm\Type\Algebra::getFormula(
            \spl_object_id($stmt->cond),
            $stmt->cond,
            $context->self,
            $statements_analyzer,
            $codebase
        );

        $mixed_var_ids = [];

        foreach ($context->vars_in_scope as $var_id => $type) {
            if ($type->hasMixed()) {
                $mixed_var_ids[] = $var_id;
            }
        }

        foreach ($context->vars_possibly_in_scope as $var_id => $_) {
            if (!isset($context->vars_in_scope[$var_id])) {
                $mixed_var_ids[] = $var_id;
            }
        }

        $if_clauses = array_values(
            array_map(
                /**
                 * @return \Psalm\Internal\Clause
                 */
                function (\Psalm\Internal\Clause $c) use ($mixed_var_ids) {
                    $keys = array_keys($c->possibilities);

                    $mixed_var_ids = \array_diff($mixed_var_ids, $keys);

                    foreach ($keys as $key) {
                        foreach ($mixed_var_ids as $mixed_var_id) {
                            if (preg_match('/^' . preg_quote($mixed_var_id, '/') . '(\[|-)/', $key)) {
                                return new \Psalm\Internal\Clause([], true);
                            }
                        }
                    }

                    return $c;
                },
                $if_clauses
            )
        );

        $ternary_clauses = array_merge($context->clauses, $if_clauses);

        if ($if_context->reconciled_expression_clauses) {
            $reconciled_expression_clauses = $if_context->reconciled_expression_clauses;

            $ternary_clauses = array_values(
                array_filter(
                    $ternary_clauses,
                    function ($c) use ($reconciled_expression_clauses) {
                        return !\in_array($c->getHash(), $reconciled_expression_clauses);
                    }
                )
            );
        }

        $ternary_clauses = Algebra::simplifyCNF($ternary_clauses);

        $negated_clauses = Algebra::negateFormula($if_clauses);

        $negated_if_types = Algebra::getTruthsFromFormula(
            Algebra::simplifyCNF(
                array_merge($context->clauses, $negated_clauses)
            )
        );

        $active_if_types = [];

        $reconcilable_if_types = Algebra::getTruthsFromFormula(
            $ternary_clauses,
            \spl_object_id($stmt->cond),
            $cond_referenced_var_ids,
            $active_if_types
        );

        $changed_var_ids = [];

        if ($reconcilable_if_types) {
            $if_vars_in_scope_reconciled = Reconciler::reconcileKeyedTypes(
                $reconcilable_if_types,
                $active_if_types,
                $if_context->vars_in_scope,
                $changed_var_ids,
                $cond_referenced_var_ids,
                $statements_analyzer,
                $statements_analyzer->getTemplateTypeMap() ?: [],
                $if_context->inside_loop,
                new CodeLocation($statements_analyzer->getSource(), $stmt->cond)
            );

            $if_context->vars_in_scope = $if_vars_in_scope_reconciled;
        }

        $t_else_context = clone $context;

        if ($stmt->if) {
            if (ExpressionAnalyzer::analyze($statements_analyzer, $stmt->if, $if_context) === false) {
                return false;
            }

            foreach ($if_context->vars_in_scope as $var_id => $type) {
                if (isset($context->vars_in_scope[$var_id])) {
                    $context->vars_in_scope[$var_id] = Type::combineUnionTypes($context->vars_in_scope[$var_id], $type);
                }
            }

            $context->referenced_var_ids = array_merge(
                $context->referenced_var_ids,
                $if_context->referenced_var_ids
            );

            $context->unreferenced_vars = array_intersect_key(
                $context->unreferenced_vars,
                $if_context->unreferenced_vars
            );
        }

        if ($negated_if_types) {
            $t_else_vars_in_scope_reconciled = Reconciler::reconcileKeyedTypes(
                $negated_if_types,
                $negated_if_types,
                $t_else_context->vars_in_scope,
                $changed_var_ids,
                $cond_referenced_var_ids,
                $statements_analyzer,
                $statements_analyzer->getTemplateTypeMap() ?: [],
                $t_else_context->inside_loop,
                new CodeLocation($statements_analyzer->getSource(), $stmt->else)
            );

            $t_else_context->vars_in_scope = $t_else_vars_in_scope_reconciled;
        }

        if (ExpressionAnalyzer::analyze($statements_analyzer, $stmt->else, $t_else_context) === false) {
            return false;
        }

        foreach ($t_else_context->vars_in_scope as $var_id => $type) {
            if (isset($context->vars_in_scope[$var_id])) {
                $context->vars_in_scope[$var_id] = Type::combineUnionTypes(
                    $context->vars_in_scope[$var_id],
                    $type
                );
            } elseif (isset($if_context->vars_in_scope[$var_id])
                && isset($if_context->assigned_var_ids[$var_id])
            ) {
                $context->vars_in_scope[$var_id] = Type::combineUnionTypes(
                    $if_context->vars_in_scope[$var_id],
                    $type
                );
            }
        }

        $context->vars_possibly_in_scope = array_merge(
            $context->vars_possibly_in_scope,
            $if_context->vars_possibly_in_scope,
            $t_else_context->vars_possibly_in_scope
        );

        $context->referenced_var_ids = array_merge(
            $context->referenced_var_ids,
            $t_else_context->referenced_var_ids
        );

        $context->unreferenced_vars = array_intersect_key(
            $context->unreferenced_vars,
            $t_else_context->unreferenced_vars
        );

        foreach ($context->unreferenced_vars as $var_id => $_) {
            if (isset($t_else_context->unreferenced_vars[$var_id])) {
                $context->unreferenced_vars[$var_id] += $t_else_context->unreferenced_vars[$var_id];
            }

            if (isset($if_context->unreferenced_vars[$var_id])) {
                $context->unreferenced_vars[$var_id] += $if_context->unreferenced_vars[$var_id];
            }
        }

        $lhs_type = null;

        if ($stmt->if) {
            if ($stmt_if_type = $statements_analyzer->node_data->getType($stmt->if)) {
                $lhs_type = $stmt_if_type;
            }
        } elseif ($stmt_cond_type = $statements_analyzer->node_data->getType($stmt->cond)) {
            $if_return_type_reconciled = AssertionReconciler::reconcile(
                '!falsy',
                clone $stmt_cond_type,
                '',
                $statements_analyzer,
                $context->inside_loop,
                [],
                new CodeLocation($statements_analyzer->getSource(), $stmt),
                $statements_analyzer->getSuppressedIssues()
            );

            $lhs_type = $if_return_type_reconciled;
        }

        if ($lhs_type && ($stmt_else_type = $statements_analyzer->node_data->getType($stmt->else))) {
            $statements_analyzer->node_data->setType($stmt, Type::combineUnionTypes($lhs_type, $stmt_else_type));
        } else {
            $statements_analyzer->node_data->setType($stmt, Type::getMixed());
        }

        return null;
    }
}
