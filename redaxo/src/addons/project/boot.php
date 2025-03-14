<?php

use Redaxo\Core\Addon\Addon;

$addon = Addon::require('project');

// register a custom yrewrite scheme
// rex_yrewrite::setScheme(new rex_project_rewrite_scheme());

// register yform template path
// rex_yform::addTemplatePath($addon->getPath('yform-templates'));

// register yorm class
// rex_yform_manager_dataset::setModelClass('rex_my_table', my_classname::class);

// change list of allowed mime types for mediapool
// MediaPool::setAllowedMimeTypes([
//     ...MediaPool::getAllowedMimeTypes(),
//     'json' => ['application/json'],
// ]);
