<?php

declare(strict_types=1);

// total 206 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method rex_debug\\:\\:getTrace\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_extension_debug\\:\\:getExtensionPoints\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/extensions/extension_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_extension_debug\\:\\:getExtensions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/extensions/extension_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Property rex_extension_debug\\:\\:\\$extensionPoints type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/extensions/extension_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Property rex_extension_debug\\:\\:\\$extensions type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/extensions/extension_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Property rex_extension_debug\\:\\:\\$listeners type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/debug/lib/extensions/extension_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_api_install_core_update\\:\\:checkRequirements\\(\\) has parameter \\$addons with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/api/api_core_update.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$conflicts has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/api/api_core_update.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$requirements has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/api/api_core_update.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$conflicts has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/package/package_update.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$requirements has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/package/package_update.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_install_webservice\\:\\:getCache\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_install_webservice\\:\\:getJson\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_install_webservice\\:\\:post\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_install_webservice\\:\\:setCache\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$cache has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^Property rex_install_webservice\\:\\:\\$cache type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/install/lib/webservice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Backend\\\\Controller\\:\\:pageAddProperties\\(\\) has parameter \\$properties with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Backend/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Backend\\\\Controller\\:\\:pageCreate\\(\\) has parameter \\$page with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Backend/Controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleAction\\:\\:getMessages\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Content\\\\ArticleAction\\:\\:\\$messages type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Content\\\\ArticleContentBase\\:\\:\\$template_attributes type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleContentBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleHandler\\:\\:addArticle\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleHandler\\:\\:copyMeta\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleHandler\\:\\:editArticle\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleHandler\\:\\:reqKey\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleSlice\\:\\:getSliceWhere\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleSlice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleSlice\\:\\:getSlicesWhere\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleSlice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleSlice\\:\\:getValueArray\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleSlice.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ArticleSliceHistory\\:\\:getSnapshots\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ArticleSliceHistory.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\CategoryHandler\\:\\:addCategory\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\CategoryHandler\\:\\:editCategory\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\CategoryHandler\\:\\:reqKey\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ContentHandler\\:\\:addSlice\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ContentHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ExtensionPoint\\\\SliceMenu\\:\\:addAdditionalActions\\(\\) has parameter \\$additionalActions with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ExtensionPoint/SliceMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ExtensionPoint\\\\SliceMenu\\:\\:getAdditionalActions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ExtensionPoint/SliceMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ExtensionPoint\\\\SliceMenu\\:\\:setAdditionalActions\\(\\) has parameter \\$additionalActions with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ExtensionPoint/SliceMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Content\\\\ExtensionPoint\\\\SliceMenu\\:\\:\\$additionalActions type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ExtensionPoint/SliceMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\Linkmap\\\\ArticleListRenderer\\:\\:renderList\\(\\) has parameter \\$articles with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/Linkmap/ArticleListRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\ModulePermission\\:\\:getFieldParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/ModulePermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureContext\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureContext\\:\\:getMountpoints\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Content\\\\StructureContext\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureContext.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:_hasValue\\(\\) has parameter \\$prefixes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:_toAttributeString\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:getUrl\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:toLink\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:toLink\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructureElement\\:\\:toLink\\(\\) has parameter \\$sorroundAttributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructureElement.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructurePermission\\:\\:getFieldParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructurePermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\StructurePermission\\:\\:getMountpoints\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/StructurePermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\Template\\:\\:hasModule\\(\\) has parameter \\$templateAttributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Content/Template.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Cronjob\\\\CronjobExecutor\\:\\:tryExecute\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Cronjob/CronjobExecutor.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Cronjob\\\\CronjobManager\\:\\:calculateNextTime\\(\\) has parameter \\$interval with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Cronjob/CronjobManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Cronjob\\\\Form\\\\IntervalField\\:\\:getIntervalElements\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Cronjob/Form/IntervalField.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Cronjob\\\\Form\\\\IntervalField\\:\\:\\$intervalElements type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Cronjob/Form/IntervalField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Database\\\\Configuration\\:\\:__construct\\(\\) has parameter \\$dbConfig with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Database/Configuration.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Database\\\\Sql\\:\\:createConnection\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Database/Sql.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Filesystem\\\\Url\\:\\:article\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Filesystem/Url.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addArticleField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addCheckboxField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addContainerField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addHiddenField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addInputField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addMediaField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addRadioField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addReadOnlyField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addReadOnlyTextField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addSelectField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addTextAreaField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:addTextField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:createElement\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:createInput\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:fieldsetPostValues\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:getInputAttributes\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:getUrl\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:redirect\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\AbstractForm\\:\\:setApplyUrl\\(\\) has parameter \\$url with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/AbstractForm.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\Field\\\\AbstractOptionField\\:\\:getOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/AbstractOptionField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\Field\\\\ContainerField\\:\\:addField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/ContainerField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\Field\\\\ContainerField\\:\\:addGroupedField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/ContainerField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\Form\\:\\:addPrioField\\(\\) has parameter \\$attributes with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Form.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Form\\\\Form\\:\\:\\$languageSupport type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Form.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Language\\\\LanguagePermission\\:\\:getClangs\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Language/LanguagePermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Language\\\\LanguagePermission\\:\\:getFieldParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Language/LanguagePermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Mailer\\\\Mailer\\:\\:\\$xHeader type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Mailer/Mailer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\AbstractEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/AbstractEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\ConvertToImageEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/ConvertToImageEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\CropEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/CropEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterBlurEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterBlurEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterBrightnessEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterBrightnessEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterColorizeEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterColorizeEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterContrastEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterContrastEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterGreyscaleEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterGreyscaleEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterSepiaEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterSepiaEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FilterSharpenEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FilterSharpenEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\FlipEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/FlipEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\HeaderEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/HeaderEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\ImageFormatEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/ImageFormatEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\ImagePropertiesEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/ImagePropertiesEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\InsertImageEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/InsertImageEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\MediaPathEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/MediaPathEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\MirrorEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/MirrorEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\ResizeEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/ResizeEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\RotateEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/RotateEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\RoundedCornersEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/RoundedCornersEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\Effect\\\\WorkspaceEffect\\:\\:getParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/MediaManager/Effect/WorkspaceEffect.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\Media\\:\\:toImage\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/Media.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaCategoryHandler\\:\\:editCategory\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaCategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaHandler\\:\\:addMedia\\(\\) has parameter \\$allowedExtensions with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaHandler\\:\\:addMedia\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaHandler\\:\\:updateMedia\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaPool\\:\\:getAllowedExtensions\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaPool.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaPool\\:\\:getAllowedExtensions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaPool.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaPool\\:\\:getBlockedExtensions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaPool.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaPool\\:\\:isAllowedExtension\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaPool.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaPool\\\\MediaPoolPermission\\:\\:getFieldParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaPool/MediaPoolPermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\MetaInfo\\\\Form\\\\Input\\\\MediaInput\\:\\:\\$args type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Form/Input/MediaInput.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:buildFilterCondition\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:fetchRequestValues\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:handleSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:handleSave\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:renderFormAndSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:renderMetaFields\\(\\) has parameter \\$epParams with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\ArticleHandler\\:\\:buildFilterCondition\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\ArticleHandler\\:\\:getForm\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\ArticleHandler\\:\\:handleSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\ArticleHandler\\:\\:handleSave\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\CategoryHandler\\:\\:buildFilterCondition\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\CategoryHandler\\:\\:handleSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\CategoryHandler\\:\\:handleSave\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:buildFilterCondition\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:handleSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:handleSave\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\MediaHandler\\:\\:buildFilterCondition\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\MediaHandler\\:\\:handleSave\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\MediaHandler\\:\\:handleSave\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\MetaInfo\\:\\:cleanup\\(\\) has parameter \\$epOrParams with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/MetaInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\RexVar\\\\LinkListVar\\:\\:getWidget\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/RexVar/LinkListVar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\RexVar\\\\LinkVar\\:\\:getWidget\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/RexVar/LinkVar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\RexVar\\\\MediaListVar\\:\\:getWidget\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/RexVar/MediaListVar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\RexVar\\\\MediaVar\\:\\:getWidget\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/RexVar/MediaVar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\RexVar\\\\RexVar\\:\\:toArray\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/RexVar/RexVar.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Security\\\\ComplexPermission\\:\\:getFieldParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Security/ComplexPermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\Security\\\\ComplexPermission\\:\\:\\$perms type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Security/ComplexPermission.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Security\\\\Login\\:\\:setSessionVar\\(\\) has parameter \\$value with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Security/Login.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Setup\\\\Setup\\:\\:checkDb\\(\\) has parameter \\$config with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Setup/Setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Formatter\\:\\:custom\\(\\) has parameter \\$format with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Formatter.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:blockFencedCodeComplete\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:blockHeader\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:blockSetextHeader\\(\\) has parameter \\$Block with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:blockSetextHeader\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:handleHeader\\(\\) has parameter \\$block with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Util\\\\Parsedown\\:\\:handleHeader\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for variable \\$Block has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Parsedown.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @phpstan\\-assert for \\$value has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Util/Type.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\Asset\\:\\:getJsFilesWithOptions\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/Asset.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\View\\\\Asset\\:\\:\\$jsFiles type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/Asset.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:addColumn\\(\\) has parameter \\$columnLayout with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:addTableColumnGroup\\(\\) has parameter \\$columns with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:formatValue\\(\\) has parameter \\$format with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:getArrayValue\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:getColumnParams\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:getParsedUrl\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:getTableColumnGroups\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:setColumnFormat\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:setColumnLayout\\(\\) has parameter \\$columnLayout with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\DataList\\:\\:setColumnParams\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\View\\\\DataList\\:\\:\\$columnParams type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Redaxo\\\\Core\\\\View\\\\DataList\\:\\:\\$tableColumnGroups type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/DataList.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Redaxo\\\\Core\\\\View\\\\Fragment has PHPDoc tag @method for method getSubfragment\\(\\) parameter \\#2 \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/Fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Redaxo\\\\Core\\\\View\\\\Fragment has PHPDoc tag @method for method subfragment\\(\\) parameter \\#2 \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/Fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\View\\\\View\\:\\:title\\(\\) has parameter \\$subtitle with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/View/View.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Content\\\\Category@anonymous/tests/Content/CategoryTest\\.php\\:175\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Content/CategoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Content\\\\CategoryTest\\:\\:createCategories\\(\\) has parameter \\$lev1Params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Content/CategoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Content\\\\CategoryTest\\:\\:createCategories\\(\\) has parameter \\$lev2Params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Content/CategoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Content\\\\CategoryTest\\:\\:createCategories\\(\\) has parameter \\$lev3Params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Content/CategoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Content\\\\CategoryTest\\:\\:createCategory\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Content/CategoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\MediaPool\\\\MediaPoolTest\\:\\:testIsAllowedExtension\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/MediaPool/MediaPoolTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\RexVar\\\\RexVarTest\\:\\:varCallback\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/RexVar/RexVarTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Security\\\\PasswordPolicyTest\\:\\:testCheck\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Security/PasswordPolicyTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Util\\\\StrTest\\:\\:buildQueryProvider\\(\\) return type has no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Util/StrTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Tests\\\\Util\\\\StrTest\\:\\:testSplit\\(\\) has parameter \\$expectedArray with no value type specified in iterable type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../tests/Util/StrTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
