<?php

$src = __DIR__.'/redaxo/src';

$finder = PhpCsFixer\Finder::create()
    ->exclude('fragments')
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
        $src.'/addons/tests',
        $src.'/addons/textile',
        $src.'/addons/users',
    ])
    ->filter(function (\SplFileInfo $file) use ($src) {
        return $src.'/core/boot.php' !== $file->getRealPath();
    })
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
        'no_extra_consecutive_blank_lines' => false,
        'no_unreachable_default_argument_value' => false,
        'no_blank_lines_after_phpdoc' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_no_package' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => false,
        'short_array_syntax' => true,
        'no_php4_constructor' => true,
    ])
    ->finder($finder)
;
