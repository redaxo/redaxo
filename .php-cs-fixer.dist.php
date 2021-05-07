<?php

$src = __DIR__.'/redaxo/src';
$bootPath = realpath($src.'/core/boot.php');
$tools = __DIR__.'/.tools';

$finder = PhpCsFixer\Finder::create()
    ->in([
        $src.'/core',
        $src.'/addons/backup',
        $src.'/addons/be_style',
        $src.'/addons/cronjob',
        $src.'/addons/debug',
        $src.'/addons/install',
        $src.'/addons/media_manager',
        $src.'/addons/mediapool',
        $src.'/addons/metainfo',
        $src.'/addons/phpmailer',
        $src.'/addons/project',
        $src.'/addons/structure',
        $src.'/addons/users',
        $tools,
    ])
    ->append([
        __FILE__,
        $tools.'/bin/clone-addon',
        $tools.'/bin/release',
        $tools.'/bin/setup',
        $tools.'/bin/update-root-composer',
    ])
    ->filter(static function (SplFileInfo $file) use ($bootPath) {
        return $bootPath !== $file->getRealPath();
    })
;

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP73Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        'array_indentation' => true,
        'blank_line_before_statement' => false,
        'braces' => ['allow_single_line_closure' => false],
        'comment_to_phpdoc' => true,
        'concat_space' => false,
        'declare_strict_types' => false,
        'echo_tag_syntax' => false,
        'heredoc_to_nowdoc' => true,
        'list_syntax' => ['syntax' => 'short'],
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'native_constant_invocation' => false,
        'no_alternative_syntax' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => ['order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property',
            'construct',
            'phpunit',
            'method',
        ]],
        'php_unit_internal_class' => true,
        'php_unit_test_case_static_method_calls' => true,
        'phpdoc_align' => false,
        'phpdoc_no_package' => false,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => false,
        'phpdoc_var_annotation_correct_order' => true,
        'psr_autoloading' => false,
        'semicolon_after_instruction' => false,
        'static_lambda' => true,
        'void_return' => false,
    ])
    ->setFinder($finder)
;
