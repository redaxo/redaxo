<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-psr0',
        '-concat_without_spaces',
        '-return',
        '-empty_return',
        '-no_empty_lines_after_phpdocs',
        '-phpdoc_no_package',
        '-phpdoc_to_comment',
        '-phpdoc_var_without_name',
        'short_array_syntax',
    ])
    ->finder($finder)
;
