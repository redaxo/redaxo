<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('demo_base')
    ->exclude('sprog')
    ->exclude('watson')
    ->exclude('yform')
    ->exclude('yrewrite')
    ->exclude('fragments')
    ->exclude('releases')
    ->notPath('src/core/boot.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'concat_without_spaces' => false,
        'blank_line_before_return' => false,
        'simplified_null_return' => false,
        'method_separation' => false,
        'no_unreachable_default_argument_value' => false,
        'no_blank_lines_after_phpdoc' => false,
        'phpdoc_no_package' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => false,
        'short_array_syntax' => true,
        'no_php4_constructor' => true,
    ])
    ->finder($finder)
;
